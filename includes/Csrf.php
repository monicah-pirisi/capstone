<?php
/**
 * Samburu EWS: CSRF Protection
 */

class Csrf
{
    /**
     * Generate CSRF token
     */
    public static function token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    public static function validate(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Alias for validate(), used by admin.php and contact-submit.php
     */
    public static function verify(string $token): bool
    {
        return self::validate($token);
    }

    /**
     * Regenerate the CSRF token (call after a successful form action)
     */
    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    /**
     * Get CSRF token field HTML
     */
    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::token() . '">';
    }
}
