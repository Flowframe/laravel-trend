<?php

use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

it('correctly maps values to dates', function () {
    $trend = resolve(Trend::class);

    $startDate = Carbon::parse('2024-01-01');
    $endDate   = Carbon::parse('2024-01-10');

    $trend->between($startDate, $endDate)->perDay();

    $values = collect([
        (object) ['date' => '2024-01-01', 'aggregate' => 10],
        (object) ['date' => '2024-01-03', 'aggregate' => 20],
        (object) ['date' => '2024-01-05', 'aggregate' => 30],
    ]);

    $result = $trend->mapValuesToDates($values);

    $expected = collect([
        new TrendValue('2024-01-01', 10),
        new TrendValue('2024-01-02', 0),
        new TrendValue('2024-01-03', 20),
        new TrendValue('2024-01-04', 0),
        new TrendValue('2024-01-05', 30),
        new TrendValue('2024-01-06', 0),
        new TrendValue('2024-01-07', 0),
        new TrendValue('2024-01-08', 0),
        new TrendValue('2024-01-09', 0),
        new TrendValue('2024-01-10', 0),
    ]);

    expect($result->values())->toEqual($expected->values());
});
