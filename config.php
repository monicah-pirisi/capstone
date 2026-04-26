<?php
/**
 * SamEWS Recommender: Global Configuration
 * ------------------------------------------
 * Database, authentication, and path helpers.
 */

// ── Production safety: suppress error output to browser ──────────────────────
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL); // still log errors server-side, never show them

/*
 * DATABASE: update these values for your hosting MySQL database.
 * InfinityFree / Hostinger: get credentials from your control panel.
 */
define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'samburu_ews');   // your Hostinger DB name
define('DB_USER',    'root');          // your Hostinger DB username
define('DB_PASS',    '');              // your Hostinger DB password
define('DB_CHARSET', 'utf8mb4');

define('DB_DSN', 'mysql:host=' . DB_HOST .
                 ';port='      . DB_PORT .
                 ';dbname='    . DB_NAME .
                 ';charset='   . DB_CHARSET);

/**
 * Return a shared PDO instance (singleton).
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

/*
 * ADMIN PASSWORD HASH
 * Pre-computed bcrypt hash of the admin password.
 * To change the password: run php -r "echo password_hash('yourpassword', PASSWORD_BCRYPT);"
 * then paste the resulting hash below.
 * Current password: admin123
 */
define('ADMIN_PASSWORD_HASH', '$2y$10$Q9WsXRysQMuQJNWklXV4QOzPe0Mh2EiGZOBJvKKe2knji9dirf5ru');

/**
 * Return the canonical base URL for the site.
 */
function base_url(string $path = ''): string
{
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    return $scheme . '://' . $host . $basePath . '/' . ltrim($path, '/');
}

define('SITE_NAME',    'SamEWS Recommender');
define('SITE_TAGLINE', 'Drought Early Warning &amp; Action Recommendation Platform &middot; Samburu County, Kenya');

/*
 * SYNC TOKEN
 * Used to trigger scripts/sync_official_reports.php via browser on hosts
 * that do not support cron (e.g. InfinityFree free plan).
 * Visit: /scripts/sync_official_reports.php?token=YOUR_TOKEN_HERE
 * Change this to any long random string before deploying.
 */
define('SYNC_TOKEN', 'change-this-to-a-long-random-string-before-deploy');
