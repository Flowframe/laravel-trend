<?php

use Flowframe\Trend\Adapters\MySqlAdapter;

it('formats column for minute interval', function () {
    $adapter = resolve(MySqlAdapter::class);
    $column = 'created_at';
    $interval = 'minute';
    $expectedFormat = "date_format(created_at, '%Y-%m-%d %H:%i:00')";

    $result = $adapter->format($column, $interval);

    expect($result)->toBe($expectedFormat);
});

it('formats column for hour interval', function () {
    $adapter = resolve(MySqlAdapter::class);
    $column = 'created_at';
    $interval = 'hour';
    $expectedFormat = "date_format(created_at, '%Y-%m-%d %H:00')";

    $result = $adapter->format($column, $interval);

    expect($result)->toBe($expectedFormat);
});

it('formats column for day interval', function () {
    $adapter = resolve(MySqlAdapter::class);
    $column = 'created_at';
    $interval = 'day';
    $expectedFormat = "date_format(created_at, '%Y-%m-%d')";

    $result = $adapter->format($column, $interval);

    expect($result)->toBe($expectedFormat);
});

it('formats column for month interval', function () {
    $adapter = resolve(MySqlAdapter::class);
    $column = 'created_at';
    $interval = 'month';
    $expectedFormat = "date_format(created_at, '%Y-%m')";

    $result = $adapter->format($column, $interval);

    expect($result)->toBe($expectedFormat);
});

it('formats column for year interval', function () {
    $adapter = resolve(MySqlAdapter::class);
    $column = 'created_at';
    $interval = 'year';
    $expectedFormat = "date_format(created_at, '%Y')";

    $result = $adapter->format($column, $interval);

    expect($result)->toBe($expectedFormat);
});

it('throws error for invalid interval', function () {
    $adapter = resolve(MySqlAdapter::class);
    $column = 'created_at';
    $interval = 'invalid_interval';

    expect(fn () => $adapter->format($column, $interval))->toThrow(Error::class);
});
