<?php

namespace Flowframe\Trend\Adapters;

use Error;

class SqliteAdapter extends AbstractAdapter
{
    public function convertTimezone(string $column, string $from, string $to): string
    {
        throw new Error('Timezone conversion not supported for SQLite.');
    }

    public function format(string $column, string $interval): string
    {
        $format = match ($interval) {
            'minute' => '%Y-%m-%d %H:%M:00',
            'hour' => '%Y-%m-%d %H:00',
            'day' => '%Y-%m-%d',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => throw new Error('Invalid interval.'),
        };

        return "strftime('{$format}', {$column})";
    }
}
