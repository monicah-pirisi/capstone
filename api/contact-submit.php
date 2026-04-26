<?php
/**
 * POST /api/contact-submit.php
 *
 * Validates contact-form data:
 *   - CSRF token verification
 *   - Honeypot field check (bot trap)
 *   - Required fields, email, and length limits
 *
 * Inserts into MySQL `contact_messages` table using PDO prepared statements.
 *
 * Returns JSON: { ok: true } or { ok: false, errors: [...] }
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Csrf.php';
require_once __DIR__ . '/../includes/Validator.php';
require_once __DIR__ . '/../includes/Db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Honeypot: humans leave the "website" field blank; bots fill it in.
if (!empty($_POST['website'])) {
    echo json_encode(['ok' => true, 'message' => 'Thank you for your message.']);
    exit;
}

// CSRF verification
$csrfToken = $_POST['csrf_token'] ?? '';
if (!Csrf::verify($csrfToken)) {
    http_response_code(403);
    echo json_encode([
        'ok'     => false,
        'errors' => ['Session expired or invalid token. Please reload the page and try again.'],
    ]);
    exit;
}

// Validation
$v = new Validator($_POST);
$v->required('name',    'Name')
  ->maxLen('name', 100, 'Name')
  ->minLen('name', 2,   'Name');

$v->required('email',   'Email')
  ->email('email',      'Email')
  ->maxLen('email', 255, 'Email');

$v->required('subject', 'Subject')
  ->maxLen('subject', 200, 'Subject');

$v->required('message', 'Message')
  ->minLen('message', 10, 'Message')
  ->maxLen('message', 5000, 'Message');

$v->in('stakeholder_group', [
    '', 'government', 'ngo', 'radio', 'pastoralist', 'intermediary', 'other'
], 'Stakeholder group');

if ($v->fails()) {
    http_response_code(422);
    echo json_encode([
        'ok'     => false,
        'errors' => $v->messages(),
    ]);
    exit;
}

// Sanitise inputs
$name    = htmlspecialchars($v->get('name'),    ENT_QUOTES, 'UTF-8');
$email   = $v->get('email');
$subject = htmlspecialchars($v->get('subject'), ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($v->get('message'), ENT_QUOTES, 'UTF-8');
$group   = $v->get('stakeholder_group');
$ip      = $_SERVER['REMOTE_ADDR'] ?? '';

// Insert into database
try {
    $sql = "INSERT INTO contact_messages
                (name, email, subject, message, stakeholder_group, ip_address, created_at)
            VALUES
                (:name, :email, :subject, :message, :sgroup, :ip, NOW())";

    Db::query($sql, [
        ':name'    => $name,
        ':email'   => $email,
        ':subject' => $subject,
        ':message' => $message,
        ':sgroup'  => $group ?: null,
        ':ip'      => $ip,
    ]);

    Csrf::regenerate();

    echo json_encode([
        'ok'      => true,
        'message' => 'Thank you for your message. We will respond within 48 hours.',
    ]);

} catch (PDOException $e) {
    if (str_contains($e->getMessage(), "doesn't exist") ||
        str_contains($e->getMessage(), 'no such table')) {
        http_response_code(500);
        echo json_encode([
            'ok'     => false,
            'errors' => ['The database has not been set up yet. Please import samburu_ews.sql first.'],
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'ok'     => false,
            'errors' => ['An error occurred while saving your message. Please try again later.'],
        ]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'     => false,
        'errors' => ['An unexpected error occurred. Please try again later.'],
    ]);
}
