<?php
/**
 * GET /api/findings-data.php
 *
 * Returns qualitative interview findings data for the findings page.
 * Study: semi-structured interviews, n=12, Samburu County 2025.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/DataRepository.php';

try {
    $interviews = DataRepository::load('interviews.json');
    $barriers   = DataRepository::load('barriers.json');
    $recs       = DataRepository::load('recommendations.json');

    $response = [
        'ok'              => true,
        'meta'            => $interviews['meta']   ?? [],
        'themes'          => $interviews['themes'] ?? [],
        'barriers_detail' => $barriers  ?? [],
        'recommendations' => $recs      ?? [],
    ];

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Failed to load findings data.',
        'debug' => (defined('APP_DEBUG') && APP_DEBUG) ? $e->getMessage() : null,
    ]);
}
