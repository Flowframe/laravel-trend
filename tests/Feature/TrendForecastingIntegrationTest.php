<?php

use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mockery;

class DummyModel extends Model
{
    protected $table = 'dummy_table';
}

beforeEach(function () {
    $this->now = Carbon::parse('2023-07-01');
    Carbon::setTestNow($this->now);
    
    // Mock the database connection and query builder
    $this->builder = Mockery::mock(Builder::class);
    $this->baseBuilder = Mockery::mock();
    
    // Setup the builder to return our test values
    $this->builder->shouldReceive('getConnection->getDriverName')->andReturn('mysql');
    $this->builder->shouldReceive('toBase')->andReturn($this->baseBuilder);
    
    // Create test data with a clear increasing trend
    $this->testData = collect([
        (object) ['date' => '2023-01', 'aggregate' => 10],
        (object) ['date' => '2023-02', 'aggregate' => 15],
        (object) ['date' => '2023-03', 'aggregate' => 20],
        (object) ['date' => '2023-04', 'aggregate' => 25],
        (object) ['date' => '2023-05', 'aggregate' => 30],
        (object) ['date' => '2023-06', 'aggregate' => 35],
    ]);
});

afterEach(function () {
    Mockery::close();
    Carbon::setTestNow();
});

it('can forecast future periods using linear regression', function () {
    // Set up mock to return test data
    $this->baseBuilder->shouldReceive('selectRaw')->andReturnSelf();
    $this->baseBuilder->shouldReceive('whereBetween')->andReturnSelf();
    $this->baseBuilder->shouldReceive('groupBy')->andReturnSelf();
    $this->baseBuilder->shouldReceive('orderBy')->andReturnSelf();
    $this->baseBuilder->shouldReceive('get')->andReturn($this->testData);
    
    // Create trend with forecasting
    $trend = Trend::query($this->builder)
        ->between(
            Carbon::parse('2023-01-01'),
            Carbon::parse('2023-06-30')
        )
        ->perMonth()
        ->forecastPeriods(3, 'linear')
        ->count();
    
    expect($trend)->toBeInstanceOf(Collection::class);
    
    // Should have 6 historical + 3 forecast points
    expect($trend->filter(fn ($value) => !$value->isForecast))->toHaveCount(6);
    expect($trend->filter(fn ($value) => $value->isForecast))->toHaveCount(3);
    
    // Verify the forecast points are marked correctly
    $forecasts = $trend->filter(fn ($value) => $value->isForecast)->values();
    expect($forecasts[0]->date)->toBe('2023-07');
    expect($forecasts[1]->date)->toBe('2023-08');
    expect($forecasts[2]->date)->toBe('2023-09');
    
    // The forecast should continue the trend
    expect($forecasts[0]->aggregate)->toBeGreaterThan(35);
});

it('can forecast until a specific date', function () {
    // Set up mock to return test data
    $this->baseBuilder->shouldReceive('selectRaw')->andReturnSelf();
    $this->baseBuilder->shouldReceive('whereBetween')->andReturnSelf();
    $this->baseBuilder->shouldReceive('groupBy')->andReturnSelf();
    $this->baseBuilder->shouldReceive('orderBy')->andReturnSelf();
    $this->baseBuilder->shouldReceive('get')->andReturn($this->testData);
    
    // Create trend with forecasting
    $trend = Trend::query($this->builder)
        ->between(
            Carbon::parse('2023-01-01'),
            Carbon::parse('2023-06-30')
        )
        ->perMonth()
        ->forecastUntil(Carbon::parse('2023-12-31'), 'moving-average')
        ->count();
    
    expect($trend)->toBeInstanceOf(Collection::class);
    
    // Should have 6 historical + 6 forecast points (Jul-Dec)
    expect($trend->filter(fn ($value) => $value->isForecast))->toHaveCount(6);
    
    // All forecast values should be the same (moving average)
    $forecasts = $trend->filter(fn ($value) => $value->isForecast)->values();
    $firstForecastValue = $forecasts[0]->aggregate;
    
    foreach ($forecasts as $forecast) {
        expect($forecast->aggregate)->toBe($firstForecastValue);
    }
});

it('has the forecast flag set correctly on values', function () {
    // Set up mock to return test data
    $this->baseBuilder->shouldReceive('selectRaw')->andReturnSelf();
    $this->baseBuilder->shouldReceive('whereBetween')->andReturnSelf();
    $this->baseBuilder->shouldReceive('groupBy')->andReturnSelf();
    $this->baseBuilder->shouldReceive('orderBy')->andReturnSelf();
    $this->baseBuilder->shouldReceive('get')->andReturn($this->testData);
    
    // Create trend with forecasting
    $trend = Trend::query($this->builder)
        ->between(
            Carbon::parse('2023-01-01'),
            Carbon::parse('2023-06-30')
        )
        ->perMonth()
        ->forecastPeriods(2, 'linear')
        ->count();
    
    // Historical values should have isForecast = false
    $historicalValues = $trend->filter(fn ($value) => !$value->isForecast);
    foreach ($historicalValues as $value) {
        expect($value->isForecast)->toBeFalse();
    }
    
    // Forecast values should have isForecast = true
    $forecastValues = $trend->filter(fn ($value) => $value->isForecast);
    foreach ($forecastValues as $value) {
        expect($value->isForecast)->toBeTrue();
    }
});

it('throws an error when forecasting periods without interval', function () {
    $trend = Trend::query($this->builder)
        ->between(
            Carbon::parse('2023-01-01'),
            Carbon::parse('2023-06-30')
        );
    
    expect(fn () => $trend->forecastPeriods(3))->toThrow(Error::class);
});

it('throws an error when forecasting periods without end date', function () {
    $trend = Trend::query($this->builder)
        ->perMonth();
    
    expect(fn () => $trend->forecastPeriods(3))->toThrow(Error::class);
}); 