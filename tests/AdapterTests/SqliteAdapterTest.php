<?php

use Flowframe\Trend\Adapters\SqliteAdapter;

it('formats column for minute interval', function () {
    $adapter = resolve(SqliteAdapter::class);
    $column = 'created_at';
    $interval = 'minute';
    $expectedFormat = "strftime('%Y-%m-%d %H:%M:00', {$column})";

    $result = $adapter->format($column, $interval);

    expect($result)->toBe($expectedFormat);
});

it('formats column for hour interval', function () {
    $adapter = resolve(SqliteAdapter::class);
    $column = 'created_at';
    $interval = 'hour';
    $expectedFormat = "strftime('%Y-%m-%d %H:00', {$column})";

    $result = $adapter->format($column, $interval);

    expect($result)->toBe($expectedFormat);
});

it('formats column for day interval', function () {
    $adapter = resolve(SqliteAdapter::class);
    $column = 'created_at';
    $interval = 'day';
    $expectedFormat = "strftime('%Y-%m-%d', {$column})";

    $result = $adapter->format($column, $interval);

    expect($result)->toBe($expectedFormat);
});

it('formats column for month interval', function () {
    $adapter = resolve(SqliteAdapter::class);
    $column = 'created_at';
    $interval = 'month';
    $expectedFormat = "strftime('%Y-%m', {$column})";

    $result = $adapter->format($column, $interval);

    expect($result)->toBe($expectedFormat);
});

it('formats column for year interval', function () {
    $adapter = resolve(SqliteAdapter::class);
    $column = 'created_at';
    $interval = 'year';
    $expectedFormat = "strftime('%Y', {$column})";

    $result = $adapter->format($column, $interval);

    expect($result)->toBe($expectedFormat);
});

it('throws error for invalid interval', function () {
    $adapter = resolve(SqliteAdapter::class);
    $column = 'created_at';
    $interval = 'invalid_interval';

    expect(fn () => $adapter->format($column, $interval))->toThrow(Error::class);
});
