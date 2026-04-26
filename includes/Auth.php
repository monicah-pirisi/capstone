<?php
/**
 * Samburu EWS: Authentication Helper
 */

class Auth
{
    /**
     * Check if user is logged in as admin
     */
    public static function isAdmin(): bool
    {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    /**
     * Attempt admin login
     */
    public static function login(string $password): bool
    {
        if (password_verify($password, ADMIN_PASSWORD_HASH)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['login_time'] = time();
            return true;
        }
        return false;
    }

    /**
     * Logout admin
     */
    public static function logout(): void
    {
        session_unset();
        session_destroy();
    }

    /**
     * Alias for isAdmin(), used by admin.php
     */
    public static function check(): bool
    {
        return self::isAdmin();
    }

    /**
     * Return session metadata for the logged-in admin
     */
    public static function meta(): array
    {
        return [
            'login_time' => $_SESSION['login_time'] ?? null,
        ];
    }

    /**
     * Require admin authentication
     */
    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            header('Location: ' . base_url('admin.php'));
            exit;
        }
    }
}
