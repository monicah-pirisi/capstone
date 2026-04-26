<?php
/**
 * Samburu EWS: Risk Assessment Engine
 *
 * Scoring uses the exact three-indicator Combined Drought Index (CDI)
 * published by Balint et al. (2013) "Monitoring Drought with the Combined
 * Drought Index in Kenya" — the only peer-reviewed study providing
 * empirically derived weights for a Kenya ASAL composite drought index:
 *
 *   Precipitation (Rainfall)  50%  — NDMA bulletin data
 *   Vegetation (NDVI/VCI)     25%  — NDMA bulletin data, threshold VCI=35
 *   Temperature               25%  — KMD bulletin data
 *
 * Indicators without published weights (Livestock, Water distance, FCS)
 * are displayed as a Ground Conditions context panel using real NDMA/WFP
 * data and published thresholds, but are not assigned a weight in the score.
 *
 * Indigenous knowledge is included as a cross-validation adjustment (±5 pts)
 * using the raw community stress ratio — no lookup values, no arbitrary weight.
 */

class RiskEngine
{
    const PHASE_NORMAL    = 'Normal';
    const PHASE_ALERT     = 'Alert';
    const PHASE_ALARM     = 'Alarm';
    const PHASE_EMERGENCY = 'Emergency';

    /**
     * Assess drought risk.
     *
     * Scored inputs (Balint et al. CDI):
     *   rainfall_mm            float  NDMA bulletin current rainfall
     *   rainfall_avg_mm        float  NDMA bulletin long-term average
     *   ndvi                   float  NDMA bulletin VCI reading (0–100 scale)
     *   ndvi_normal            float  NDMA published VCI threshold = 35
     *   temp_max_celsius       float  KMD bulletin forecast maximum temperature
     *   temp_normal_max        float  KMD long-term normal max for Samburu = 30°C
     *   temp_extreme_max       float  Recorded northern Kenya ASAL extreme = 40°C
     *
     * Cross-validation input (not scored):
     *   indigenous_stress_ratio float  Proportion of stressed community indicators (0.0–1.0)
     *
     * Context-only inputs (real NDMA/WFP data, displayed not scored):
     *   livestock_condition    string  NDMA field classification
     *   water_distance_km      float   NDMA bulletin
     *   water_normal_km        float   NDMA bulletin
     *   food_consumption_score float   NDMA bulletin (WFP FCS, max 42)
     */
    public static function assess(array $inputs): array
    {
        // ── SCORED INDICATOR 1: Rainfall ─────────────────────────────────────
        // Weight: 50% — Balint et al. (2013) CDI
        // Data:   NDMA Samburu County Drought Bulletin (current_mm, long_term_avg_mm)
        $rainfall    = (float)($inputs['rainfall_mm']     ?? 0);
        $rainfallAvg = (float)($inputs['rainfall_avg_mm'] ?? 95);
        $rainfallScore = self::calculateRainfallScore($rainfall, $rainfallAvg);

        // ── SCORED INDICATOR 2: Vegetation (VCI) ─────────────────────────────
        // Weight: 25% — Balint et al. (2013) CDI
        // Data:   NDMA Samburu County Drought Bulletin (VCI reading)
        // Normal: VCI = 35 — NDMA published operational trigger (VCI3M below 35
        //         activates the National Drought Contingency Fund)
        $ndvi       = (float)($inputs['ndvi']       ?? 0);
        $ndviNormal = (float)($inputs['ndvi_normal'] ?? 35);
        $ndviScore  = self::calculateNdviScore($ndvi, $ndviNormal);

        // ── SCORED INDICATOR 3: Temperature ──────────────────────────────────
        // Weight: 25% — Balint et al. (2013) CDI
        // Data:   KMD monthly forecast bulletin (max_celsius)
        // Normal: 30°C — KMD long-term mean maximum for Samburu ASAL zone
        // Extreme: 40°C — recorded ceiling for northern Kenya ASAL counties
        // Direction: inverted — higher temperature = more evapotranspiration = worse
        $tempMax     = (float)($inputs['temp_max_celsius']  ?? 30);
        $tempNormal  = (float)($inputs['temp_normal_max']   ?? 30);
        $tempExtreme = (float)($inputs['temp_extreme_max']  ?? 40);
        $tempScore   = self::calculateTemperatureScore($tempMax, $tempNormal, $tempExtreme);

        // ── WEIGHTED COMPOSITE ────────────────────────────────────────────────
        // Exact weights from Balint et al. (2013) — sum to 1.0
        $scores = [
            'rainfall'    => $rainfallScore,
            'ndvi'        => $ndviScore,
            'temperature' => $tempScore,
        ];

        $weights = [
            'rainfall'    => 0.50,
            'ndvi'        => 0.25,
            'temperature' => 0.25,
        ];

        $compositeScore = 0.0;
        foreach ($weights as $key => $weight) {
            $compositeScore += $scores[$key] * $weight;
        }

        // ── INDIGENOUS CROSS-VALIDATION ADJUSTMENT ────────────────────────────
        // Not a weighted indicator — no published composite index assigns a numeric
        // weight to indigenous knowledge. Instead, the raw community stress ratio
        // (0.0–1.0) adjusts the composite score by up to ±5 points:
        //
        //   adjustment = (0.5 − ratio) × 10   [capped at ±5]
        //
        //   ratio = 1.0  → −5 pts  (community signals maximum drought stress)
        //   ratio = 0.5  →  0 pts  (community neutral — scientific score unchanged)
        //   ratio = 0.0  → +5 pts  (community signals no stress)
        //
        // Basis: Derbyshire et al. (2024) and Radeny et al. (2019) document that
        // indigenous indicators in northern Kenya detect drought stress earlier than
        // satellite data — justifying their role as a directional cross-check.
        $indigenousRatio      = (float)($inputs['indigenous_stress_ratio'] ?? 0.5);
        $indigenousAdjustment = max(-5.0, min(5.0, (0.5 - $indigenousRatio) * 10));
        $compositeScore       = max(0.0, min(100.0, $compositeScore + $indigenousAdjustment));

        $scores['indigenous_stress_pct'] = (int)round($indigenousRatio * 100);
        $scores['indigenous_adjustment'] = round($indigenousAdjustment, 1);

        // ── GROUND CONDITIONS CONTEXT ─────────────────────────────────────────
        // Real NDMA/WFP data displayed alongside the score.
        // Not weighted because no published study provides weights for these
        // indicators in a Kenya ASAL composite drought index.
        // Status labels use published thresholds:
        //   Livestock: NDMA field body condition classification
        //   Water:     NDMA bulletin normal baseline
        //   FCS:       WFP Kenya-specific thresholds (poor <21, borderline 21–35,
        //              acceptable >35, ceiling 42 for high-staple-consumption zones)
        $livestock  = (string)($inputs['livestock_condition']    ?? '');
        $waterKm    = (float)($inputs['water_distance_km']       ?? 0);
        $waterNorm  = (float)($inputs['water_normal_km']         ?? 7);
        $fcs        = (float)($inputs['food_consumption_score']  ?? 0);

        $scores['ground_conditions'] = [
            'livestock' => [
                'value'   => $livestock,
                'status'  => self::livestockStatus($livestock),
                'source'  => 'NDMA bulletin',
            ],
            'water' => [
                'value'        => $waterKm . ' km',
                'normal'       => $waterNorm . ' km',
                'status'       => $waterKm <= $waterNorm ? 'Within normal' : 'Above normal',
                'status_level' => $waterKm <= $waterNorm ? 'good' : ($waterKm <= $waterNorm * 1.3 ? 'moderate' : 'stressed'),
                'source'       => 'NDMA bulletin',
            ],
            'food_security' => [
                'value'   => $fcs . ' / 42',
                'status'  => self::fcsStatus($fcs),
                'source'  => 'NDMA bulletin, WFP FCS methodology',
            ],
        ];

        $phase   = self::determinePhase($compositeScore);
        $reasons = self::generateReasons($scores);
        $actions = self::getStakeholderActions($phase);

        return [
            'phase'      => $phase,
            'score'      => round($compositeScore, 1),
            'sub_scores' => $scores,
            'reasons'    => $reasons,
            'actions'    => $actions,
        ];
    }

    // ── SCORING FUNCTIONS ─────────────────────────────────────────────────────

    private static function calculateRainfallScore(float $rainfall, float $avg): float
    {
        if ($rainfall >= $avg) return 100;
        if ($rainfall <= 0)   return 0;
        return min(100, ($rainfall / $avg) * 100);
    }

    private static function calculateNdviScore(float $ndvi, float $normal): float
    {
        if ($ndvi >= $normal) return 100;
        if ($ndvi <= 0.1)    return 0;
        return min(100, ($ndvi / $normal) * 100);
    }

    /**
     * Inverted linear decay — higher temperature = more evapotranspiration = lower score.
     * Normal (30°C) scores 100. At or above extreme (40°C) scores 0.
     */
    private static function calculateTemperatureScore(float $temp, float $normal, float $extreme): float
    {
        if ($temp <= $normal)  return 100;
        if ($temp >= $extreme) return 0;
        return max(0, (1 - (($temp - $normal) / ($extreme - $normal))) * 100);
    }

    // ── STATUS HELPERS FOR CONTEXT PANEL ─────────────────────────────────────

    private static function livestockStatus(string $condition): string
    {
        return match($condition) {
            'Good'      => 'Normal',
            'Fair'      => 'Marginal',
            'Poor'      => 'Stressed',
            'Very Poor' => 'Critical',
            default     => 'Unknown',
        };
    }

    private static function fcsStatus(float $fcs): string
    {
        // WFP Kenya thresholds: poor <21, borderline 21–35, acceptable >35 (ceiling 42)
        if ($fcs > 35)  return 'Acceptable';
        if ($fcs >= 21) return 'Borderline';
        return 'Poor';
    }

    // ── PHASE CLASSIFICATION ──────────────────────────────────────────────────

    private static function determinePhase(float $score): string
    {
        if ($score >= 80) return self::PHASE_NORMAL;
        if ($score >= 60) return self::PHASE_ALERT;
        if ($score >= 40) return self::PHASE_ALARM;
        return self::PHASE_EMERGENCY;
    }

    // ── REASON FLAGS ──────────────────────────────────────────────────────────

    private static function generateReasons(array $scores): array
    {
        $reasons = [];

        if ($scores['rainfall'] < 40) {
            $reasons[] = ['severity' => 'high',   'text' => 'Rainfall critically below long-term average, causing severe seasonal moisture deficit'];
        } elseif ($scores['rainfall'] < 75) {
            $reasons[] = ['severity' => 'medium', 'text' => 'Rainfall below long-term average, with seasonal moisture stress present'];
        }

        if ($scores['ndvi'] < 40) {
            $reasons[] = ['severity' => 'high',   'text' => 'Vegetation critically degraded, with VCI indicating severe pasture deficit across sub-counties'];
        } elseif ($scores['ndvi'] < 75) {
            $reasons[] = ['severity' => 'medium', 'text' => 'Vegetation below normal, with moderate pasture deficit present, particularly in Samburu East'];
        }

        if ($scores['temperature'] < 40) {
            $reasons[] = ['severity' => 'high',   'text' => 'Temperatures critically above normal, accelerating soil moisture loss and pasture deterioration'];
        } elseif ($scores['temperature'] < 75) {
            $reasons[] = ['severity' => 'medium', 'text' => 'Temperatures above normal, increasing evapotranspiration and moisture deficit'];
        }

        $stressPct = $scores['indigenous_stress_pct'] ?? 50;
        $adj       = $scores['indigenous_adjustment']  ?? 0;
        $adjLabel  = $adj >= 0 ? "+{$adj}" : "{$adj}";

        if ($stressPct >= 80) {
            $reasons[] = ['severity' => 'high',   'text' => "Community indicators: {$stressPct}% stressed (score adjusted {$adjLabel} pts) — community signals conditions worse than scientific data alone suggests"];
        } elseif ($stressPct >= 50) {
            $reasons[] = ['severity' => 'medium', 'text' => "Community indicators: {$stressPct}% stressed (score adjusted {$adjLabel} pts) — consistent with emerging drought conditions"];
        } elseif ($stressPct < 30) {
            $reasons[] = ['severity' => 'low',    'text' => "Community indicators: only {$stressPct}% stressed (score adjusted {$adjLabel} pts) — community observations suggest less severe conditions than satellite data indicates"];
        }

        return $reasons;
    }

    // ── STAKEHOLDER ACTIONS ───────────────────────────────────────────────────

    private static function getStakeholderActions(string $phase): array
    {
        $actions = ['government' => '', 'ngos' => '', 'radio' => '', 'pastoralists' => ''];

        switch ($phase) {
            case self::PHASE_NORMAL:
                $actions['government']   = 'Continue monitoring';
                $actions['ngos']         = 'Maintain preparedness';
                $actions['radio']        = 'Regular weather updates';
                $actions['pastoralists'] = 'Normal activities';
                break;
            case self::PHASE_ALERT:
                $actions['government']   = 'Activate monitoring protocols';
                $actions['ngos']         = 'Prepare contingency plans';
                $actions['radio']        = 'Broadcast drought watch messages';
                $actions['pastoralists'] = 'Consider early livestock movement';
                break;
            case self::PHASE_ALARM:
                $actions['government']   = 'Coordinate emergency response';
                $actions['ngos']         = 'Pre-position relief supplies';
                $actions['radio']        = 'Air emergency alerts';
                $actions['pastoralists'] = 'Implement destocking decisions';
                break;
            case self::PHASE_EMERGENCY:
                $actions['government']   = 'Declare drought emergency';
                $actions['ngos']         = 'Deploy humanitarian aid';
                $actions['radio']        = 'Continuous emergency broadcasts';
                $actions['pastoralists'] = 'Evacuate to emergency grazing areas';
                break;
        }

        return $actions;
    }
}
