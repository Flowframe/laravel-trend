<?php

namespace Flowframe\Trend;

class TrendValue
{
    public function __construct(
        public string $date,
        public mixed $aggregate,
    ) {
    }
}
