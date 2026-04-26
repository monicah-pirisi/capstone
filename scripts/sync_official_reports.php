<?php
/*
 * Sync script: pulls the latest KMD and NDMA bulletin links and saves them
 * to kmd_ndma_reports.
 *
 * --- HOW TO RUN ---
 * Local (XAMPP):   php c:/xampp/htdocs/capstone_web/scripts/sync_official_reports.php
 * Cron (VPS/cPanel with cron): 0 6 * * * /usr/local/bin/php /home/<user>/public_html/scripts/sync_official_reports.php
 * Browser (InfinityFree / no-cron hosts): visit
 *   https://yourdomain.com/scripts/sync_official_reports.php?token=YOUR_SYNC_TOKEN
 *   (set SYNC_TOKEN in config.php first)
 *
 * --- NOTE FOR INFINITYFREE ---
 * InfinityFree free plan blocks outbound HTTP/cURL requests.
 * If cURL fails the script logs the error and exits cleanly — the site itself
 * is unaffected because current-alert.php falls back to local JSON data files.
 * Use the admin panel (admin.php) to manually update KMD/NDMA summaries instead.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/config.php';
require_once $root . '/includes/Db.php';

// ── Web-browser trigger: require SYNC_TOKEN when called via HTTP ──────────────
$isCli = (PHP_SAPI === 'cli');
if (!$isCli) {
    $provided = $_GET['token'] ?? '';
    if (!defined('SYNC_TOKEN') || $provided === '' || !hash_equals(SYNC_TOKEN, $provided)) {
        http_response_code(403);
        exit('Forbidden. Provide ?token=YOUR_SYNC_TOKEN');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

// ── Check cURL is available before doing anything ────────────────────────────
if (!function_exists('curl_init')) {
    $msg = 'cURL is not available on this server. '
         . 'On InfinityFree free plan, outbound HTTP is blocked. '
         . 'Update KMD/NDMA data manually via admin.php instead.';
    echo $msg . PHP_EOL;
    exit(0);   // exit cleanly — not a fatal error
}

const KMD_URL      = 'https://meteo.go.ke/our-products/monthly-forecast/';
const NDMA_URL     = 'https://knowledgeweb.ndma.go.ke/Public/Resources/Default.aspx?ID=7';
const NDMA_BACKUP  = 'https://ndma.go.ke/drought-information/';
const FETCH_TIMEOUT = 30;

function log_line(string $level, string $msg): void
{
    echo '[' . date('Y-m-d H:i:s') . "] [{$level}] {$msg}" . PHP_EOL;
}

function fetch_page(string $url): string|false
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => FETCH_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => [
            'Accept: text/html,application/xhtml+xml,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
        ],
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; SamburuEWS/1.0)',
        CURLOPT_ENCODING  => '',
    ]);

    $body  = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno !== 0) {
        log_line('ERROR', "curl failed for {$url}: {$error}");
        return false;
    }
    if ($code < 200 || $code >= 400) {
        log_line('ERROR', "HTTP {$code} for {$url}");
        return false;
    }

    log_line('INFO', "fetched {$url} ({$code}, " . strlen((string)$body) . ' bytes)');
    return (string)$body;
}

function make_xpath(string $html): ?DOMXPath
{
    if (trim($html) === '') return null;

    $dom  = new DOMDocument();
    $prev = libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();
    libxml_use_internal_errors($prev);

    return new DOMXPath($dom);
}

function first_match(DOMXPath $xpath, array $queries, string $label): ?DOMElement
{
    foreach ($queries as $q) {
        $nodes = $xpath->query($q);
        if ($nodes && $nodes->length > 0) {
            $node = $nodes->item(0);
            if ($node instanceof DOMElement) {
                log_line('DEBUG', "{$label} matched: {$q}");
                return $node;
            }
        }
    }
    return null;
}

function to_absolute(string $href, string $base): string
{
    $href = trim($href);
    if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) return $href;

    $p      = parse_url($base);
    $scheme = $p['scheme'] ?? 'https';
    $host   = $p['host']   ?? '';

    if (str_starts_with($href, '//')) return $scheme . ':' . $href;
    if (str_starts_with($href, '/')) return "{$scheme}://{$host}{$href}";

    $dir = isset($p['path']) ? rtrim(dirname($p['path']), '/') : '';
    return "{$scheme}://{$host}{$dir}/{$href}";
}

function already_saved(PDO $pdo, string $org, string $type, string $url): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM kmd_ndma_reports
         WHERE source_org = ? AND report_type = ?
           AND (pdf_url = ? OR page_url = ?)
           AND synced_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)'
    );
    $stmt->execute([$org, $type, $url, $url]);
    return (int)$stmt->fetchColumn() > 0;
}

function save_report(PDO $pdo, string $org, string $type, string $title, string $pageUrl, ?string $pdfUrl): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO kmd_ndma_reports (source_org, report_type, title, page_url, pdf_url, synced_at)
         VALUES (?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([$org, $type, $title, $pageUrl, $pdfUrl]);
    return (int)$pdo->lastInsertId();
}

function log_run(PDO $pdo, string $job, string $status, string $msg): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO ingestion_runs (job_name, status, message, ran_at) VALUES (?, ?, ?, NOW())'
    );
    $stmt->execute([$job, $status, $msg]);
}

function scrape_kmd(): ?array
{
    log_line('INFO', 'scraping KMD: ' . KMD_URL);

    $html = fetch_page(KMD_URL);
    if ($html === false) return null;

    $xpath = make_xpath($html);
    if ($xpath === null) {
        log_line('ERROR', 'KMD: could not parse page');
        return null;
    }

    $lc = '"ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"';
    $queries = [
        "//article//a[contains(translate(normalize-space(.), {$lc}), \"forecast\")]",
        "//div[contains(@class,\"entry\")]//a[contains(translate(normalize-space(.), {$lc}), \"forecast\")]",
        "//div[contains(@class,\"post\")]//a[contains(translate(normalize-space(.), {$lc}), \"forecast\")]",
        "//a[contains(translate(@href, {$lc}), \"monthly-forecast\")]",
        "//a[contains(translate(@href, {$lc}), \"forecast\")]",
        "//a[contains(translate(normalize-space(.), {$lc}), \"forecast\")]",
        "//a[contains(translate(@href, {$lc}), \".pdf\")]",
    ];

    $anchor = first_match($xpath, $queries, 'KMD');
    if ($anchor === null) {
        log_line('WARN', 'KMD: no matching link found');
        return null;
    }

    $href    = $anchor->getAttribute('href');
    $text    = trim($anchor->textContent);
    $title   = $text !== '' ? $text : 'KMD Monthly Forecast';
    $url     = to_absolute($href, KMD_URL);
    $isPdf   = str_ends_with(strtolower($url), '.pdf');
    $pageUrl = $isPdf ? KMD_URL : $url;
    $pdfUrl  = $isPdf ? $url : null;

    // If we landed on a post page, dig inside it for the actual PDF
    if (!$isPdf) {
        $inner = fetch_page($url);
        if ($inner !== false) {
            $ix = make_xpath($inner);
            if ($ix !== null) {
                $pdfs = $ix->query("//a[contains(translate(@href,{$lc},\".pdf\")]");
                if ($pdfs && $pdfs->length > 0) {
                    $pa = $pdfs->item(0);
                    if ($pa instanceof DOMElement) {
                        $pdfUrl = to_absolute($pa->getAttribute('href'), $url);
                        log_line('INFO', "KMD: found PDF inside post: {$pdfUrl}");
                    }
                }
            }
        }
    }

    log_line('INFO', "KMD: \"{$title}\" page={$pageUrl} pdf=" . ($pdfUrl ?? 'none'));
    return ['title' => $title, 'page_url' => $pageUrl, 'pdf_url' => $pdfUrl];
}

function scrape_ndma(): ?array
{
    log_line('INFO', 'scraping NDMA: ' . NDMA_URL);

    $html      = fetch_page(NDMA_URL);
    $sourcePage = NDMA_URL;

    if ($html === false) {
        log_line('WARN', 'NDMA: primary URL failed, trying backup');
        $html       = fetch_page(NDMA_BACKUP);
        $sourcePage = NDMA_BACKUP;
    }

    if ($html === false) {
        log_line('ERROR', 'NDMA: both URLs failed');
        return null;
    }

    $xpath = make_xpath($html);
    if ($xpath === null) {
        log_line('ERROR', 'NDMA: could not parse page');
        return null;
    }

    $lc = '"ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"';
    $queries = [
        "//a[contains(translate(@href,{$lc},\".pdf\") and contains(translate(normalize-space(.),{$lc},\"bulletin\")]",
        "//a[contains(translate(@href,{$lc},\".pdf\") and contains(translate(normalize-space(.),{$lc},\"drought\")]",
        "//a[contains(translate(@href,{$lc},\"bulletin\")]",
        "//a[contains(translate(normalize-space(.),{$lc},\"bulletin\")]",
        "//a[contains(translate(normalize-space(.),{$lc},\"drought\") and contains(translate(@href,{$lc},\".pdf\")]",
        "//a[contains(translate(@href,{$lc},\".pdf\")]",
    ];

    $anchor = first_match($xpath, $queries, 'NDMA');
    if ($anchor === null) {
        log_line('WARN', 'NDMA: no matching link found');
        return null;
    }

    $href    = $anchor->getAttribute('href');
    $text    = trim($anchor->textContent);
    $title   = $text !== '' ? $text : 'NDMA National Drought Bulletin';
    $url     = to_absolute($href, $sourcePage);
    $isPdf   = str_ends_with(strtolower($url), '.pdf');
    $pageUrl = $isPdf ? $sourcePage : $url;
    $pdfUrl  = $isPdf ? $url : null;

    log_line('INFO', "NDMA: \"{$title}\" page={$pageUrl} pdf=" . ($pdfUrl ?? 'none'));
    return ['title' => $title, 'page_url' => $pageUrl, 'pdf_url' => $pdfUrl];
}


// Run

log_line('INFO', 'sync started');

try {
    $pdo = db();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    log_line('ERROR', 'DB connection failed: ' . $e->getMessage());
    exit(1);
}

// KMD
$kmdStatus = 'fail';
$kmdMsg    = '';
try {
    $kmd = scrape_kmd();
    if ($kmd === null) {
        $kmdMsg = 'scraper returned nothing - page structure may have changed';
        log_line('WARN', "KMD: {$kmdMsg}");
    } else {
        $check = $kmd['pdf_url'] ?? $kmd['page_url'];
        if (already_saved($pdo, 'KMD', 'monthly_forecast', $check)) {
            $kmdMsg    = 'already synced in last 24h, skipped';
            $kmdStatus = 'success';
            log_line('INFO', "KMD: {$kmdMsg}");
        } else {
            $id        = save_report($pdo, 'KMD', 'monthly_forecast', $kmd['title'], $kmd['page_url'], $kmd['pdf_url']);
            $kmdMsg    = "saved row {$id}: {$kmd['title']}";
            $kmdStatus = 'success';
            log_line('INFO', "KMD: {$kmdMsg}");
        }
    }
} catch (Throwable $e) {
    $kmdMsg = $e->getMessage();
    log_line('ERROR', "KMD: {$kmdMsg}");
}
log_run($pdo, 'sync_kmd_monthly', $kmdStatus, $kmdMsg);

// NDMA
$ndmaStatus = 'fail';
$ndmaMsg    = '';
try {
    $ndma = scrape_ndma();
    if ($ndma === null) {
        $ndmaMsg = 'scraper returned nothing - page structure may have changed';
        log_line('WARN', "NDMA: {$ndmaMsg}");
    } else {
        $check = $ndma['pdf_url'] ?? $ndma['page_url'];
        if (already_saved($pdo, 'NDMA', 'national_drought_bulletin', $check)) {
            $ndmaMsg    = 'already synced in last 24h, skipped';
            $ndmaStatus = 'success';
            log_line('INFO', "NDMA: {$ndmaMsg}");
        } else {
            $id         = save_report($pdo, 'NDMA', 'national_drought_bulletin', $ndma['title'], $ndma['page_url'], $ndma['pdf_url']);
            $ndmaMsg    = "saved row {$id}: {$ndma['title']}";
            $ndmaStatus = 'success';
            log_line('INFO', "NDMA: {$ndmaMsg}");
        }
    }
} catch (Throwable $e) {
    $ndmaMsg = $e->getMessage();
    log_line('ERROR', "NDMA: {$ndmaMsg}");
}
log_run($pdo, 'sync_ndma_bulletin', $ndmaStatus, $ndmaMsg);

log_line('INFO', 'sync done');
exit(0);
