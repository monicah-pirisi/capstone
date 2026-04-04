<?php
/**
 * Samburu EWS — Risk Assessment Engine
 */

class RiskEngine
{
    // Risk phase thresholds
    const PHASE_NORMAL = 'Normal';
    const PHASE_ALERT = 'Alert';
    const PHASE_ALARM = 'Alarm';
    const PHASE_EMERGENCY = 'Emergency';

    /**
     * Assess risk based on input indicators
     */
    public static function assess(array $inputs): array
    {
        $scores = [];
        
        // NDVI Score (0-100)
        $ndvi = (float)($inputs['ndvi'] ?? 0);
        $ndviNormal = (float)($inputs['ndvi_normal'] ?? 0.32);
        $ndviScore = self::calculateNdviScore($ndvi, $ndviNormal);
        $scores['ndvi'] = $ndviScore;

        // Rainfall Score (0-100)
        $rainfall = (float)($inputs['rainfall_mm'] ?? 0);
        $rainfallAvg = (float)($inputs['rainfall_avg_mm'] ?? 95);
        $rainfallScore = self::calculateRainfallScore($rainfall, $rainfallAvg);
        $scores['rainfall'] = $rainfallScore;

        // Water Distance Score (0-100, inverted)
        $waterDist = (float)($inputs['water_distance_km'] ?? 0);
        $waterNormal = (float)($inputs['water_normal_km'] ?? 5);
        $waterScore = self::calculateWaterScore($waterDist, $waterNormal);
        $scores['water'] = $waterScore;

        // Livestock Condition Score (0-100)
        $livestock = (string)($inputs['livestock_condition'] ?? 'Fair');
        $livestockScore = self::calculateLivestockScore($livestock);
        $scores['livestock'] = $livestockScore;

        // Food Consumption Score (0-100)
        $foodScore = (float)($inputs['food_consumption_score'] ?? 0);
        $scores['food'] = min(100, ($foodScore / 42) * 100);

        // Indigenous Outlook Score
        $outlook = (string)($inputs['indigenous_outlook'] ?? 'Favourable');
        $indigenousScore = self::calculateIndigenousScore($outlook);
        $scores['indigenous'] = $indigenousScore;

        // Calculate composite score (weighted average)
        $weights = [
            'ndvi' => 0.20,
            'rainfall' => 0.20,
            'water' => 0.15,
            'livestock' => 0.20,
            'food' => 0.10,
            'indigenous' => 0.15,
        ];

        $compositeScore = 0;
        foreach ($weights as $key => $weight) {
            $compositeScore += ($scores[$key] ?? 0) * $weight;
        }

        // Determine phase
        $phase = self::determinePhase($compositeScore);

        // Generate reasons and recommendations
        $reasons = self::generateReasons($inputs, $scores);
        $actions = self::getStakeholderActions($phase);

        return [
            'phase' => $phase,
            'score' => round($compositeScore, 1),
            'sub_scores' => $scores,
            'reasons' => $reasons,
            'actions' => $actions,
        ];
    }

    /**
     * Calculate NDVI score (lower is worse)
     */
    private static function calculateNdviScore(float $ndvi, float $normal): float
    {
        if ($ndvi >= $normal) return 100;
        if ($ndvi <= 0.1) return 0;
        
        $ratio = $ndvi / $normal;
        return min(100, $ratio * 100);
    }

    /**
     * Calculate rainfall score
     */
    private static function calculateRainfallScore(float $rainfall, float $avg): float
    {
        if ($rainfall >= $avg) return 100;
        if ($rainfall <= 0) return 0;
        
        $ratio = $rainfall / $avg;
        return min(100, $ratio * 100);
    }

    /**
     * Calculate water distance score (inverted)
     */
    private static function calculateWaterScore(float $distance, float $normal): float
    {
        if ($distance <= $normal) return 100;
        if ($distance >= 30) return 0;
        
        $ratio = 1 - (($distance - $normal) / (30 - $normal));
        return max(0, $ratio * 100);
    }

    /**
     * Calculate livestock condition score
     */
    private static function calculateLivestockScore(string $condition): float
    {
        $scores = [
            'Good' => 100,
            'Fair' => 70,
            'Poor' => 40,
            'Very Poor' => 15,
        ];
        
        return $scores[$condition] ?? 50;
    }

    /**
     * Calculate indigenous outlook score
     */
    private static function calculateIndigenousScore(string $outlook): float
    {
        $scores = [
            'Favourable' => 100,
            'Watch' => 75,
            'Concerning' => 50,
            'Severe' => 25,
        ];
        
        return $scores[$outlook] ?? 50;
    }

    /**
     * Determine risk phase from composite score
     */
    private static function determinePhase(float $score): string
    {
        if ($score >= 80) return self::PHASE_NORMAL;
        if ($score >= 60) return self::PHASE_ALERT;
        if ($score >= 40) return self::PHASE_ALARM;
        return self::PHASE_EMERGENCY;
    }

    /**
     * Generate explanation reasons
     */
    private static function generateReasons(array $inputs, array $scores): array
    {
        $reasons = [];
        
        if ($scores['ndvi'] < 50) {
            $reasons[] = 'Vegetation condition significantly below normal';
        }
        if ($scores['rainfall'] < 50) {
            $reasons[] = 'Current rainfall well below long-term average';
        }
        if ($scores['water'] < 50) {
            $reasons[] = 'Water sources are further than normal distances';
        }
        if ($scores['livestock'] < 50) {
            $reasons[] = 'Livestock body condition is deteriorating';
        }
        
        return $reasons;
    }

    /**
     * Get stakeholder-specific actions for a phase
     */
    private static function getStakeholderActions(string $phase): array
    {
        $actions = [
            'government' => '',
            'ngos' => '',
            'radio' => '',
            'pastoralists' => '',
        ];

        switch ($phase) {
            case self::PHASE_NORMAL:
                $actions['government'] = 'Continue monitoring';
                $actions['ngos'] = 'Maintain preparedness';
                $actions['radio'] = 'Regular weather updates';
                $actions['pastoralists'] = 'Normal activities';
                break;
            case self::PHASE_ALERT:
                $actions['government'] = 'Activate monitoring protocols';
                $actions['ngos'] = 'Prepare contingency plans';
                $actions['radio'] = 'Broadcast drought watch messages';
                $actions['pastoralists'] = 'Consider early livestock movement';
                break;
            case self::PHASE_ALARM:
                $actions['government'] = 'Coordinate emergency response';
                $actions['ngos'] = 'Pre-position relief supplies';
                $actions['radio'] = 'Air emergency alerts';
                $actions['pastoralists'] = 'Implement destocking decisions';
                break;
            case self::PHASE_EMERGENCY:
                $actions['government'] = 'Declare drought emergency';
                $actions['ngos'] = 'Deploy humanitarian aid';
                $actions['radio'] = 'Continuous emergency broadcasts';
                $actions['pastoralists'] = 'Evacuate to emergency grazing areas';
                break;
        }

        return $actions;
    }
}
