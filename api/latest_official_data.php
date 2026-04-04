<?php
/*
 * GET /api/latest_official_data.php
 *
 * Returns the latest KMD and NDMA bulletin links (from kmd_ndma_reports)
 * combined with the manually maintained summaries (from official_summaries).
 *
 * KMD  = Scientific Forecast  (future rainfall outlook)
 * NDMA = Drought Situation     (current phase on the ground)
 */

declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/config.php';
require_once $root . '/includes/Db.php';

function send(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: public, max-age=900');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send(['ok' => false, 'error' => 'Method not allowed'], 405);
}

try {
    $pdo = db();

    $reportStmt = $pdo->prepare(
        'SELECT source_org, report_type, title, page_url, pdf_url, synced_at
         FROM kmd_ndma_reports WHERE source_org = ?
         ORDER BY synced_at DESC LIMIT 1'
    );

    $reportStmt->execute(['KMD']);
    $kmdReport = $reportStmt->fetch() ?: null;

    $reportStmt->execute(['NDMA']);
    $ndmaReport = $reportStmt->fetch() ?: null;

    $summaryStmt = $pdo->prepare(
        'SELECT source_org, outlook_category, drought_phase, summary_text, valid_period, updated_at
         FROM official_summaries WHERE source_org = ?'
    );

    $summaryStmt->execute(['KMD']);
    $kmdSummary = $summaryStmt->fetch() ?: null;

    $summaryStmt->execute(['NDMA']);
    $ndmaSummary = $summaryStmt->fetch() ?: null;

} catch (PDOException $e) {
    error_log('latest_official_data.php: ' . $e->getMessage());
    send(['ok' => false, 'error' => 'Database error, try again later']);
}

// Prefer a direct PDF link over the listing page
function best_link(?array $row): ?string
{
    if ($row === null) return null;
    return ($row['pdf_url'] !== null && $row['pdf_url'] !== '') ? $row['pdf_url'] : $row['page_url'];
}

// Most recent sync timestamp across both orgs
$syncedAt = null;
if ($kmdReport)  $syncedAt = $kmdReport['synced_at'];
if ($ndmaReport && ($syncedAt === null || $ndmaReport['synced_at'] > $syncedAt)) {
    $syncedAt = $ndmaReport['synced_at'];
}

send([
    'ok'        => true,
    'synced_at' => $syncedAt,

    // KMD: future rainfall forecast
    'kmd' => [
        'source'           => 'Kenya Meteorological Department (KMD)',
        'role'             => 'Scientific Forecast - future rainfall outlook',
        'title'            => $kmdReport['title']            ?? null,
        'page_url'         => $kmdReport['page_url']         ?? null,
        'pdf_url'          => $kmdReport['pdf_url']          ?? null,
        'link'             => best_link($kmdReport),
        'report_type'      => $kmdReport['report_type']      ?? null,
        'synced_at'        => $kmdReport['synced_at']        ?? null,
        'outlook_category' => $kmdSummary['outlook_category'] ?? null,
        'valid_period'     => $kmdSummary['valid_period']     ?? null,
        'summary'          => $kmdSummary['summary_text']     ?? null,
        'summary_updated'  => $kmdSummary['updated_at']       ?? null,
    ],

    // NDMA: current drought situation on the ground
    'ndma' => [
        'source'          => 'National Drought Management Authority (NDMA)',
        'role'            => 'Drought Situation - current drought phase',
        'title'           => $ndmaReport['title']           ?? null,
        'page_url'        => $ndmaReport['page_url']        ?? null,
        'pdf_url'         => $ndmaReport['pdf_url']         ?? null,
        'link'            => best_link($ndmaReport),
        'report_type'     => $ndmaReport['report_type']     ?? null,
        'synced_at'       => $ndmaReport['synced_at']       ?? null,
        'drought_phase'   => $ndmaSummary['drought_phase']  ?? null,
        'valid_period'    => $ndmaSummary['valid_period']   ?? null,
        'summary'         => $ndmaSummary['summary_text']   ?? null,
        'summary_updated' => $ndmaSummary['updated_at']     ?? null,
    ],
]);
