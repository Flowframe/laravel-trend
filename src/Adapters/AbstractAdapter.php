<?php

namespace Flowframe\Trend\Adapters;

abstract class AbstractAdapter
{
    abstract public function format(string $column, string $interval): string;
}
