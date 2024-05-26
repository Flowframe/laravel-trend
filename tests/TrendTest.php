<?php

use Flowframe\Trend\Tests\Fixtures\Models\Post;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('correctly maps values to dates', function () {
    $trend = Trend::query(Post::query());

    $startDate = Carbon::parse('2024-01-01');
    $endDate = Carbon::parse('2024-01-10');

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

it('correctly aggregates sum data', function () {
    Post::factory()
        ->count(2)
        ->create(['created_at' => Carbon::parse('2024-01-01'), 'summable_column' => 5]);
    Post::factory()
        ->count(2)
        ->create(['created_at' => Carbon::parse('2024-01-02'), 'summable_column' => 10]);
    Post::factory()
        ->count(2)
        ->create(['created_at' => Carbon::parse('2024-01-03'), 'summable_column' => 15]);

    $trend = Trend::model(Post::class);

    $startDate = Carbon::parse('2024-01-01');
    $endDate = Carbon::parse('2024-01-03');

    $trend->between($startDate, $endDate)->perDay();

    $result = $trend->sum('summable_column');

    $expected = collect([
        new TrendValue('2024-01-01', 10),
        new TrendValue('2024-01-02', 20),
        new TrendValue('2024-01-03', 30),
    ]);

    expect($result->values())->toEqual($expected->values());
});

it('correctly aggregates averages data', function () {
    Post::factory()
        ->count(2)
        ->create(['created_at' => Carbon::parse('2024-01-01'), 'summable_column' => 5]);
    Post::factory()
        ->count(2)
        ->create(['created_at' => Carbon::parse('2024-01-02'), 'summable_column' => 10]);

    $trend = Trend::model(Post::class);

    $trend->between(Carbon::parse('2024-01-01'), Carbon::parse('2024-01-02'))->perDay();

    $result = $trend->average('summable_column');

    $expected = collect([
        new TrendValue('2024-01-01', 5),
        new TrendValue('2024-01-02', 10),
    ]);

    expect($result->values())->toEqual($expected->values());
});

it('correctly calculates the minimum value', function () {
    Post::factory()
        ->count(2)->sequence(['summable_column' => 18], ['summable_column' => 145])
        ->create(['created_at' => '2024-01-01']);

    Post::factory()
        ->count(2)->sequence(['summable_column' => 534], ['summable_column' => 245])
        ->create(['created_at' => '2024-01-02']);

    Post::factory()
        ->count(2)->sequence(['summable_column' => 113], ['summable_column' => 135])
        ->create(['created_at' => '2024-01-03']);

    $trend = Trend::model(Post::class);

    $trend->between(Carbon::parse('2024-01-01'), Carbon::parse('2024-01-03'))->perDay();

    $result = $trend->min('summable_column');

    $expected = collect([
        new TrendValue('2024-01-01', 18),
        new TrendValue('2024-01-02', 245),
        new TrendValue('2024-01-03', 113),
    ]);

    expect($result->values())->toEqual($expected->values());
});

it('correctly calculates the maximum value', function () {
    Post::factory()
        ->count(2)->sequence(['summable_column' => 10], ['summable_column' => 15])
        ->create(['created_at' => '2024-01-01']);

    Post::factory()
        ->count(2)->sequence(['summable_column' => 5], ['summable_column' => 25])
        ->create(['created_at' => '2024-01-02']);

    Post::factory()
        ->count(2)->sequence(['summable_column' => 15], ['summable_column' => 35])
        ->create(['created_at' => '2024-01-03']);

    $trend = Trend::model(Post::class);

    $trend->between(Carbon::parse('2024-01-01'), Carbon::parse('2024-01-03'))->perDay();

    $result = $trend->max('summable_column');

    $expected = collect([
        new TrendValue('2024-01-01', 15),
        new TrendValue('2024-01-02', 25),
        new TrendValue('2024-01-03', 35),
    ]);

    expect($result->values())->toEqual($expected->values());
});

it('correctly calculates the count', function () {
    Post::factory()->count(5)->create(['created_at' => '2024-01-01']);
    Post::factory()->count(4)->create(['created_at' => '2024-01-02']);
    Post::factory()->count(3)->create(['created_at' => '2024-01-03']);

    $trend = Trend::model(Post::class);

    $trend->between(Carbon::parse('2024-01-01'), Carbon::parse('2024-01-03'))->perDay();

    $result = $trend->count();

    $expected = collect([
        new TrendValue('2024-01-01', 5),
        new TrendValue('2024-01-02', 4),
        new TrendValue('2024-01-03', 3),
    ]);

    expect($result->values())->toEqual($expected->values());
});

it('correctly sets the date column', function () {
    $trend = Trend::query(Post::query());
    $trend->dateColumn('custom_date_column');
    expect($trend->dateColumn)->toBe('custom_date_column');
});

it('correctly sets the date alias', function () {
    $trend = Trend::query(Post::query());
    $trend->dateAlias('custom_date_alias');
    expect($trend->dateAlias)->toBe('custom_date_alias');
});

it('correctly sets the interval', function () {
    $trend = Trend::query(Post::query());
    $trend->interval('month');
    expect($trend->interval)->toBe('month');

    $intervals = collect([
        'perMinute' => 'minute',
        'perHour' => 'hour',
        'perDay' => 'day',
        'perMonth' => 'month',
        'perYear' => 'year',
    ]);

    $intervals->each(function (string $interval, string $method) use ($trend) {
        $trend->{$method}();
        expect($trend->interval)->toBe($interval);
    });
});
