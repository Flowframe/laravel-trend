<?php

use Flowframe\Trend\Adapters\PgsqlAdapter;

it('formats column for minute interval', function () {
    $adapter = resolve(PgsqlAdapter::class);
    $column = 'created_at';
    $interval = 'minute';
    $expectedFormat = "to_char(created_at, 'YYYY-MM-DD HH24:MI:00')";

    $result = $adapter->format($column, $interval);

    expect($result)->toBe($expectedFormat);
});

it('formats column for hour interval', function () {
    $adapter = resolve(PgsqlAdapter::class);
    $column = 'created_at';
    $interval = 'hour';
    $expectedFormat = "to_char(created_at, 'YYYY-MM-DD HH24:00:00')";

    $result = $adapter->format($column, $interval);

    expect($result)->toBe($expectedFormat);
});

it('formats column for day interval', function () {
    $adapter = resolve(PgsqlAdapter::class);
    $column = 'created_at';
    $interval = 'day';
    $expectedFormat = "to_char(created_at, 'YYYY-MM-DD')";

    $result = $adapter->format($column, $interval);

    expect($result)->toBe($expectedFormat);
});

it('formats column for month interval', function () {
    $adapter = resolve(PgsqlAdapter::class);
    $column = 'created_at';
    $interval = 'month';
    $expectedFormat = "to_char(created_at, 'YYYY-MM')";

    $result = $adapter->format($column, $interval);

    expect($result)->toBe($expectedFormat);
});

it('formats column for year interval', function () {
    $adapter = resolve(PgsqlAdapter::class);
    $column = 'created_at';
    $interval = 'year';
    $expectedFormat = "to_char(created_at, 'YYYY')";

    $result = $adapter->format($column, $interval);

    expect($result)->toBe($expectedFormat);
});

it('throws error for invalid interval', function () {
    $adapter = resolve(PgsqlAdapter::class);
    $column = 'created_at';
    $interval = 'invalid_interval';

    expect(fn () => $adapter->format($column, $interval))->toThrow(Error::class);
});
