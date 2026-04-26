<?php
/**
 * Samburu EWS: Data Repository
 * Loads JSON data files from the data/ directory
 */

class DataRepository
{
    /**
     * Base data directory
     */
    private static function getDataDir(): string
    {
        return __DIR__ . '/../data';
    }

    /**
     * Load a JSON data file
     */
    public static function load(string $filename): ?array
    {
        $filepath = self::getDataDir() . '/' . $filename;
        
        if (!file_exists($filepath)) {
            error_log("Data file not found: {$filepath}");
            return null;
        }

        $content = file_get_contents($filepath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON parse error in {$filename}: " . json_last_error_msg());
            return null;
        }

        return $data;
    }

    /**
     * Save data to a JSON file
     */
    public static function save(string $filename, array $data): bool
    {
        $filepath = self::getDataDir() . '/' . $filename;
        
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if ($json === false) {
            error_log("JSON encode error for {$filename}");
            return false;
        }

        return file_put_contents($filepath, $json) !== false;
    }

    /**
     * Get all available data files
     */
    public static function getAvailableFiles(): array
    {
        $dir = self::getDataDir();
        
        if (!is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '/*.json');
        return array_map(function($file) {
            return basename($file);
        }, $files);
    }

    /**
     * Load interviews data
     */
    public static function getInterviews(): ?array
    {
        return self::load('interviews.json');
    }

    /**
     * Load NDMA latest data
     */
    public static function getNdmaLatest(): ?array
    {
        return self::load('ndma_latest.json');
    }

    /**
     * Load KMD summary
     */
    public static function getKmdSummary(): ?array
    {
        return self::load('kmd_summary.json');
    }

    /**
     * Load indigenous indicators
     */
    public static function getIndigenousIndicators(): ?array
    {
        return self::load('indigenous_indicators.json');
    }

    /**
     * Load barriers
     */
    public static function getBarriers(): ?array
    {
        return self::load('barriers.json');
    }

    /**
     * Load recommendations
     */
    public static function getRecommendations(): ?array
    {
        return self::load('recommendations.json');
    }

    /**
     * Load stakeholders
     */
    public static function getStakeholders(): ?array
    {
        return self::load('stakeholders.json');
    }

    /**
     * Load channels content
     */
    public static function getChannelsContent(): ?array
    {
        return self::load('channels_content.json');
    }
}
