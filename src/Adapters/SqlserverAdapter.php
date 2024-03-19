<?php

namespace Flowframe\Trend\Adapters;

use Error;

class SqlserverAdapter extends AbstractAdapter
{
    public function format(string $column, string $interval): string
    {
        return match ($interval) {
            'minute' => "LEFT(CONVERT(VARCHAR, [$column], 120), 17) + '00'",
            'hour' => "LEFT(CONVERT(VARCHAR, [$column], 120), 14) + '00:00'",
            'day' => "CONVERT(VARCHAR, [$column], 23)",
            'month' => "LEFT(CONVERT(VARCHAR, [$column], 23), 7)",
            'year' => "CONVERT(VARCHAR(4), YEAR([$column]))",
            default => throw new Error('Invalid interval.'),
        };
    }
}
