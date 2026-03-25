<?php

namespace Flowframe\Trend\Adapters;

use Error;

class SqliteAdapter extends AbstractAdapter
{
    public function format(string $column, string $interval): string
    {
        // Fix wrong week date format after SQLite v3.46
        // Using coalesce for backward compatibility
        if ($interval === 'week') {
            return "coalesce(strftime('%G-%V', {$column}), strftime('%Y-%W', {$column}))";
        }

        $format = match ($interval) {
            'minute' => '%Y-%m-%d %H:%M:00',
            'hour' => '%Y-%m-%d %H:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%W',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => throw new Error('Invalid interval.'),
        };

        return "strftime('{$format}', {$column})";
    }
}
