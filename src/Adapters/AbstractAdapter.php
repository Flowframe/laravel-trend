<?php

namespace Flowframe\Trend\Adapters;

use Flowframe\Trend\Trend;

abstract class AbstractAdapter
{
    abstract public function format(string $column, string $interval): string;

    public function groupColumn(Trend $trend): ?string
    {
        return null;
    }
}
