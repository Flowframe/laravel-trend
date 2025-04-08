<?php

namespace Flowframe\Trend\Adapters;

use Error;

class MsSqlServerAdapter extends AbstractAdapter
{
    public function format(string $column, string $interval): string
    {
        $format = match ($interval) {
            'minute' => 'yyyy-MM-dd HH:mm:00',
            'hour' => 'yyyy-MM-dd HH:00',
            'day' => 'yyyy-MM-dd',
            'week' => 'yyyy-ww',
            'month' => 'yyyy-MM',
            'year' => 'yyyy',
            default => throw new Error('Invalid interval.'),
        };

        return "FORMAT({$column}, '{$format}')";
    }
}
