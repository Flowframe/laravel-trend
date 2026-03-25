<?php

namespace Flowframe\Trend\Adapters;

use Error;
use Illuminate\Support\Facades\DB;

class SqliteAdapter extends AbstractAdapter
{
    public function format(string $column, string $interval): string
    {
        // Get SQLite version
        $sqliteVersion = null;
        try {
            $versionInfo = DB::select('SELECT sqlite_version() as version')[0] ?? null;
            if ($versionInfo) {
                $sqliteVersion = $versionInfo->version;
            }
        } catch (\Throwable $e) {
            // Silently fail if version check fails
        }
        
        $format = match ($interval) {
            'minute' => '%Y-%m-%d %H:%M:00',
            'hour' => '%Y-%m-%d %H:00',
            'day' => '%Y-%m-%d',
            'week' => $this->getWeekFormat($column, $sqliteVersion),
            'month' => '%Y-%m',
            'year' => '%Y',
            default => throw new Error('Invalid interval.'),
        };

        // For non-week intervals, use regular strftime formatting
        if ($interval !== 'week' || $this->usesBuiltInISOWeek($sqliteVersion)) {
            return "strftime('{$format}', {$column})";
        }

        // For week interval, return the concatenated expression
        return $format;
    }
    
    /**
     * Get the appropriate SQL expression for week formatting based on SQLite version
     */
    protected function getWeekFormat(string $column, ?string $sqliteVersion): string
    {
        // SQLite 3.46+ supports %G-%V format for ISO week numbers
        if ($this->usesBuiltInISOWeek($sqliteVersion)) {
            return '%G-%V';
        }
        
        // For older SQLite versions, use custom expression to match the ISO format
        return "strftime('%Y', {$column}) || '-' || (strftime('%W', {$column}) + 1)";
    }
    
    /**
     * Check if SQLite version supports ISO week format
     */
    protected function usesBuiltInISOWeek(?string $version): bool
    {
        if (!$version) {
            return false;
        }
        
        $versionNumber = (float) $version;
        return $versionNumber >= 3.46;
    }
}
