<?php

use Carbon\Carbon;
use Flowframe\Trend\TrendForecast;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Collection;

beforeEach(function () {
    // Setup test data - simulates 6 months of increasing data
    $this->historicalData = collect([
        new TrendValue(date: '2023-01', aggregate: 10),
        new TrendValue(date: '2023-02', aggregate: 15),
        new TrendValue(date: '2023-03', aggregate: 20),
        new TrendValue(date: '2023-04', aggregate: 25),
        new TrendValue(date: '2023-05', aggregate: 30),
        new TrendValue(date: '2023-06', aggregate: 35),
    ]);
    
    $this->forecastStart = Carbon::parse('2023-07-01');
    $this->forecastEnd = Carbon::parse('2023-09-01');
});

it('can create a linear forecast', function () {
    $forecast = new TrendForecast(
        $this->historicalData,
        'month',
        $this->forecastStart,
        $this->forecastEnd
    );
    
    $result = $forecast->linear();
    
    expect($result)->toBeInstanceOf(Collection::class);
    expect($result)->toHaveCount(3); // 3 months forecasted
    expect($result[0]->date)->toBe('2023-07');
    expect($result[0]->aggregate)->toBeGreaterThan(35); // Should continue the trend
    
    // The linear forecast should have a consistent increase
    $firstDiff = $result[1]->aggregate - $result[0]->aggregate;
    $secondDiff = $result[2]->aggregate - $result[1]->aggregate;
    expect(round($firstDiff, 2))->toBe(round($secondDiff, 2));
});

it('can create a moving average forecast', function () {
    $forecast = new TrendForecast(
        $this->historicalData,
        'month',
        $this->forecastStart,
        $this->forecastEnd
    );
    
    $result = $forecast->movingAverage(3);
    
    expect($result)->toBeInstanceOf(Collection::class);
    expect($result)->toHaveCount(3);
    
    // The moving average of the last 3 months (25, 30, 35) should be 30
    expect($result[0]->aggregate)->toBe(30);
    expect($result[1]->aggregate)->toBe(30);
    expect($result[2]->aggregate)->toBe(30);
});

it('can create a weighted moving average forecast', function () {
    $forecast = new TrendForecast(
        $this->historicalData,
        'month',
        $this->forecastStart,
        $this->forecastEnd
    );
    
    $result = $forecast->weightedMovingAverage([0.2, 0.3, 0.5]);
    
    expect($result)->toBeInstanceOf(Collection::class);
    expect($result)->toHaveCount(3);
    
    // Calculate expected value: 0.2*25 + 0.3*30 + 0.5*35 = 31.5
    $expectedValue = 0.2 * 25 + 0.3 * 30 + 0.5 * 35;
    expect($result[0]->aggregate)->toBe($expectedValue);
});

it('can create an exponential smoothing forecast', function () {
    $forecast = new TrendForecast(
        $this->historicalData,
        'month',
        $this->forecastStart,
        $this->forecastEnd
    );
    
    $result = $forecast->exponentialSmoothing(0.3);
    
    expect($result)->toBeInstanceOf(Collection::class);
    expect($result)->toHaveCount(3);
    
    // All forecast values should be the same
    expect($result[0]->aggregate)->toBe($result[1]->aggregate);
    expect($result[1]->aggregate)->toBe($result[2]->aggregate);
    expect($result[0]->aggregate)->toBeGreaterThan(25); // Value should be higher than early data
});

it('can use the default forecasting method', function () {
    $forecast = new TrendForecast(
        $this->historicalData,
        'month',
        $this->forecastStart,
        $this->forecastEnd
    );
    
    $result = $forecast->generate();
    
    expect($result)->toBeInstanceOf(Collection::class);
    expect($result)->toHaveCount(3);
});

it('can handle empty historical data', function () {
    $emptyData = collect([]);
    
    $forecast = new TrendForecast(
        $emptyData,
        'month',
        $this->forecastStart,
        $this->forecastEnd
    );
    
    $result = $forecast->generate();
    
    expect($result)->toBeInstanceOf(Collection::class);
    expect($result)->toHaveCount(3);
    expect($result[0]->aggregate)->toBe(0);
});

it('can handle single data point for linear regression', function () {
    $singlePoint = collect([
        new TrendValue(date: '2023-01', aggregate: 10),
    ]);
    
    $forecast = new TrendForecast(
        $singlePoint,
        'month',
        $this->forecastStart,
        $this->forecastEnd
    );
    
    $result = $forecast->linear();
    
    expect($result)->toBeInstanceOf(Collection::class);
    expect($result)->toHaveCount(3);
    expect($result[0]->aggregate)->toBe(0);
}); 