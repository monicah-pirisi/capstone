<?php
/**
 * Samburu EWS — Global Configuration
 * ------------------------------------
 * Database, authentication, and path helpers.
 */

/* ── Database (PDO DSN) ─────────────────────────── */
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'samburu_ews');
define('DB_USER', 'root');          // ← change in production
define('DB_PASS', '');              // ← change in production
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

/* ── Admin password (bcrypt hash placeholder) ───── */
define('ADMIN_PASSWORD_HASH', password_hash('admin123', PASSWORD_BCRYPT));
// In production: replace with a pre-computed hash and remove plain text.

/* ── Base URL helper ────────────────────────────── */
function base_url(string $path = ''): string
{
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Works when public/ is the document root
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    return $scheme . '://' . $host . $basePath . '/' . ltrim($path, '/');
}

/* ── Site metadata ─────────────────────────────── */
define('SITE_NAME', 'Samburu EWS');
define('SITE_TAGLINE', 'Early Warning System — Recommender & Educative Platform');
