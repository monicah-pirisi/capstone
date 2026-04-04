<?php
/**
 * Samburu EWS — Database Connection (PDO)
 * Note: Uses config.php db() function for singleton connection
 */

class Db
{
    /**
     * Get database connection
     */
    public static function connection(): PDO
    {
        return db();
    }

    /**
     * Run a query and return statement
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch single row
     */
    public static function fetch(string $sql, array $params = []): ?array
    {
        $stmt = self::query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Fetch all rows
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }
}
