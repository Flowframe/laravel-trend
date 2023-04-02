<?php

namespace Flowframe\Trend\Adapters;

use Error;
use Flowframe\Trend\Trend;

class SqlServerAdapter extends AbstractAdapter
{
    public function format(string $column, string $interval): string
    {
        $format = match ($interval) {
            'minute' => 'yyyy-MM-dd HH:mm:00',
            'hour' => 'yyyy-MM-dd HH:00',
            'day' => 'yyyy-MM-dd',
            'month' => 'yyyy-MM',
            'year' => 'yyyy',
            default => throw new Error('Invalid interval.'),
        };

        return "FORMAT({$column}, '{$format}')";
    }

    public function groupColumn(Trend $trend): ?string
    {
        return $trend->sqlDate;
    }
}
