<?php
/**
 * GET /api/current-alert-data.php
 *
 * Reads NDMA, KMD, and indigenous-indicator JSON files,
 * feeds them into RiskEngine::assess(), and returns:
 *   - risk_level, score, sub_scores, reasons
 *   - recommended_actions (array for routing table)
 *   - channel_messages (WhatsApp, Facebook, Radio, USSD previews)
 *   - sources: NDMA, KMD, indigenous indicators
 *
 * Accepts optional GET/POST overrides for what-if simulation.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/DataRepository.php';
require_once __DIR__ . '/../includes/RiskEngine.php';
require_once __DIR__ . '/../includes/Db.php';

try {
    // Load source data
    $ndma       = DataRepository::load('ndma_latest.json');
    $kmd        = DataRepository::load('kmd_summary.json');
    $indigenous = DataRepository::load('indigenous_indicators.json');
    $channels   = DataRepository::load('channels_content.json');

    // Pull live bulletin links from DB; fall back to JSON source_url if not synced yet
    $dbKmdUrl  = null;
    $dbNdmaUrl = null;
    try {
        $kmdRow  = Db::fetch('SELECT pdf_url, page_url FROM kmd_ndma_reports WHERE source_org = ? ORDER BY synced_at DESC LIMIT 1', ['KMD']);
        $ndmaRow = Db::fetch('SELECT pdf_url, page_url FROM kmd_ndma_reports WHERE source_org = ? ORDER BY synced_at DESC LIMIT 1', ['NDMA']);
        if ($kmdRow)  $dbKmdUrl  = $kmdRow['pdf_url']  ?: $kmdRow['page_url'];
        if ($ndmaRow) $dbNdmaUrl = $ndmaRow['pdf_url'] ?: $ndmaRow['page_url'];
    } catch (Throwable) {
        // table not created yet or DB down - harmless, falls back below
    }

    // Build inputs.
    // Scored indicators: Balint et al. (2013) CDI — Rainfall 50%, Vegetation 25%, Temperature 25%
    // Context-only: Livestock, Water distance, FCS — real NDMA/WFP data, displayed not weighted
    $defaults = [
        // ── Scored: Rainfall (NDMA bulletin) ──
        'rainfall_mm'             => (float)($ndma['rainfall']['current_mm']                   ?? 0),
        'rainfall_avg_mm'         => (float)($ndma['rainfall']['long_term_avg_mm']             ?? 95),
        // ── Scored: Vegetation/VCI (NDMA bulletin, threshold=35) ──
        'ndvi'                    => (float)($ndma['vegetation_condition']['ndvi']             ?? 0),
        'ndvi_normal'             => (float)($ndma['vegetation_condition']['ndvi_normal']      ?? 35),
        // ── Scored: Temperature (KMD bulletin) ──
        'temp_max_celsius'        => (float)($kmd['temperature']['max_celsius']               ?? 30),
        'temp_normal_max'         => (float)($kmd['temperature']['normal_max_celsius']        ?? 30),
        'temp_extreme_max'        => (float)($kmd['temperature']['extreme_max_celsius']       ?? 40),
        // ── Cross-validation: Indigenous stress ratio (field research) ──
        'indigenous_stress_ratio' => 0.5,
        // ── Context only: real NDMA/WFP data, not weighted ──
        'livestock_condition'     => (string)($ndma['livestock']['body_condition']             ?? 'Fair'),
        'water_distance_km'       => (float)($ndma['water']['distance_to_water_km']           ?? 0),
        'water_normal_km'         => (float)($ndma['water']['normal_distance_km']             ?? 7.0),
        'food_consumption_score'  => (float)($ndma['food_security']['food_consumption_score'] ?? 0),
    ];

    // Compute indigenous stress ratio from keyword scan of documented field indicators.
    // Specialist-tier indicators excluded — their celestial/spiritual readings cannot
    // be reliably classified by keyword matching without risk of misclassification.
    // The raw ratio (0.0–1.0) is passed directly to RiskEngine, which applies it as
    // a bounded ±5 pt cross-validation adjustment rather than a weighted sub-score.
    $stressKeywords  = ['deteriorating', 'low', 'sparse', 'drying', 'unusual', 'restless',
                        'below', 'poor', 'declining', 'drought', 'stress', 'browning',
                        'early movement', 'dry-season', 'above normal', 'rapidly'];
    $droughtSignals  = 0;
    $totalIndicators = 0;
    if (is_array($indigenous)) {
        foreach ($indigenous as $ind) {
            if (($ind['tier'] ?? 'general') === 'specialist') continue;
            $totalIndicators++;
            $status = strtolower($ind['status'] ?? '');
            foreach ($stressKeywords as $kw) {
                if (strpos($status, $kw) !== false) {
                    $droughtSignals++;
                    break;
                }
            }
        }
    }
    if ($totalIndicators > 0) {
        $defaults['indigenous_stress_ratio'] = round($droughtSignals / $totalIndicators, 4);
    }

    // Apply overrides (what-if mode)
    $params = $_SERVER['REQUEST_METHOD'] === 'POST'
        ? array_merge($_GET, $_POST)
        : $_GET;

    $overridable = [
        'rainfall_mm', 'rainfall_avg_mm',
        'ndvi', 'ndvi_normal',
        'temp_max_celsius', 'temp_normal_max', 'temp_extreme_max',
        'indigenous_stress_ratio',
    ];

    $isOverride = false;
    $inputs = $defaults;
    foreach ($overridable as $key) {
        if (isset($params[$key]) && $params[$key] !== '') {
            $inputs[$key] = is_numeric($params[$key]) ? (float)$params[$key] : (string)$params[$key];
            $isOverride = true;
        }
    }

    // Run risk engine
    $raw   = RiskEngine::assess($inputs);
    $phase = $raw['phase'];   // Normal / Alert / Alarm / Emergency
    $score = $raw['score'];

    // Build recommended_actions array - JS renderRouting() expects [{icon, stakeholder, action}]
    $actionsMap = [
        ['icon' => '🏛️', 'stakeholder' => 'Government',     'action' => $raw['actions']['government']   ?? ''],
        ['icon' => '🤝', 'stakeholder' => 'NGOs',            'action' => $raw['actions']['ngos']         ?? ''],
        ['icon' => '📻', 'stakeholder' => 'Radio Stations',  'action' => $raw['actions']['radio']        ?? ''],
        ['icon' => '🐄', 'stakeholder' => 'Pastoralists',    'action' => $raw['actions']['pastoralists'] ?? ''],
        ['icon' => '🔗', 'stakeholder' => 'Intermediaries',  'action' => 'Relay alert via community networks and barazas'],
    ];

    // Build channel_messages
    $date   = date('j F Y');
    $waTpls = $channels['social_media']['whatsapp']['templates'] ?? [];
    $fbTpls = $channels['social_media']['facebook']['templates'] ?? [];
    $r30s   = $channels['radio']['scripts']['30s'] ?? [];
    $r60s   = $channels['radio']['scripts']['60s'] ?? [];

    $waBody = $waTpls[$phase]['body']    ?? ($waTpls['Alert']['body']    ?? '(No template)');
    $fbBody = $fbTpls[$phase]['post']    ?? ($fbTpls['Alert']['post']    ?? '(No template)');
    $r30    = $r30s[$phase]['script']    ?? ($r30s['Alert']['script']    ?? '(No template)');
    $r60    = $r60s[$phase]['script']    ?? ($r60s['Alert']['script']    ?? '(No template)');

    $fill = fn(string $s) => str_replace(['[SCORE]', '[DATE]'], [$score, $date], $s);

    $ussdStatus  = "=== Samburu EWS *384# ===\n\nRisk Level : {$phase}\nScore      : {$score}/100\nDate       : {$date}\n\nPress BACK or dial again for menu.";
    $ussdActions = "MENU:\n1. Current Alert Level\n2. Advice for Pastoralists\n3. Where to Get Help\n4. Change Language\n\n0. Back  00. Home";

    $channelMessages = [
        'whatsapp'     => $fill($waBody),
        'facebook'     => $fill($fbBody),
        'radio_30s'    => $r30,
        'radio_60s'    => $r60,
        'ussd_status'  => $ussdStatus,
        'ussd_actions' => $ussdActions,
    ];

    // Final response
    $response = [
        'ok'   => true,
        'mode' => $isOverride ? 'what-if' : 'live',
        'assessment' => [
            'risk_level'          => $phase,
            'score'               => $score,
            'sub_scores'          => $raw['sub_scores'],
            'reasons'             => $raw['reasons'],
            'assessed_at'         => date('c'),
            'recommended_actions' => $actionsMap,
            'channel_messages'    => $channelMessages,
        ],
        'inputs'  => $inputs,
        'sources' => [
            'ndma' => [
                'label'        => $ndma['source']         ?? 'NDMA',
                'url'          => $dbNdmaUrl ?? $ndma['source_url'] ?? 'https://ndma.go.ke',
                'bulletin'     => $ndma['bulletin_month'] ?? '',
                'phase_stated' => $ndma['phase']          ?? '',
                'summary'      => $ndma['summary']        ?? '',
                'updated_at'   => $ndma['updated_at']     ?? '',
            ],
            'kmd' => [
                'label'        => $kmd['source']       ?? 'KMD',
                'url'          => $dbKmdUrl ?? $kmd['source_url'] ?? 'https://meteo.go.ke',
                'valid_period' => $kmd['valid_period'] ?? '',
                'outlook'      => $kmd['outlook']      ?? [],
                'advisory'     => $kmd['advisory']     ?? '',
                'updated_at'   => $kmd['updated_at']   ?? '',
            ],
            'indigenous' => [
                'count'        => $totalIndicators,
                'stress_ratio' => $defaults['indigenous_stress_ratio'],
                'adjustment'   => $raw['sub_scores']['indigenous_adjustment'] ?? 0,
                'indicators'   => $indigenous,
            ],
        ],
    ];

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Failed to compute alert data.',
        'debug' => (defined('APP_DEBUG') && APP_DEBUG) ? $e->getMessage() : null,
    ]);
}
