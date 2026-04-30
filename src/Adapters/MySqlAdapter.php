<?php

namespace Flowframe\Trend\Adapters;

use Error;

class MySqlAdapter extends AbstractAdapter
{
    public function format(string $column, string $interval, bool $isUnixTimestamp = false): string
    {
        $format = match ($interval) {
            'minute' => '%Y-%m-%d %H:%i:00',
            'hour' => '%Y-%m-%d %H:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => throw new Error('Invalid interval.'),
        };

        $columnExpression = $isUnixTimestamp ? "FROM_UNIXTIME({$column})" : $column;

        return "date_format({$columnExpression}, '{$format}')";
    }
}
