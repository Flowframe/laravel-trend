<?php

use Carbon\Carbon;
use Flowframe\Trend\Adapters\SqliteAdapter;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mockery;

beforeEach(function () {
    $this->now = Carbon::parse('2023-01-15'); // middle of January 2023
    Carbon::setTestNow($this->now);
    
    // Mock the database connection and query builder
    $this->builder = Mockery::mock(Builder::class);
    $this->baseBuilder = Mockery::mock();
    
    // Setup the builder to return our test values and SQLite driver
    $this->builder->shouldReceive('getConnection->getDriverName')->andReturn('sqlite');
    $this->builder->shouldReceive('toBase')->andReturn($this->baseBuilder);
    
    // Create test data for two weeks (formatted as they would be from SQLite)
    $week1 = '2023-2'; // Week 2 of 2023
    $week2 = '2023-3'; // Week 3 of 2023
    
    $this->testData = collect([
        (object) ['date' => $week1, 'aggregate' => 10],
        (object) ['date' => $week2, 'aggregate' => 20],
    ]);
});

afterEach(function () {
    Mockery::close();
    Carbon::setTestNow();
});

it('correctly handles week format for SQLite', function () {
    // Set up mock to return test data
    $this->baseBuilder->shouldReceive('selectRaw')->andReturnSelf();
    $this->baseBuilder->shouldReceive('whereBetween')->andReturnSelf();
    $this->baseBuilder->shouldReceive('groupBy')->andReturnSelf();
    $this->baseBuilder->shouldReceive('orderBy')->andReturnSelf();
    $this->baseBuilder->shouldReceive('get')->andReturn($this->testData);
    
    // Create trend for weeks
    $trend = Trend::query($this->builder)
        ->between(
            Carbon::parse('2023-01-01'), // Start of week 1
            Carbon::parse('2023-01-21')  // End of week 3
        )
        ->perWeek()
        ->count();
    
    expect($trend)->toBeInstanceOf(Collection::class);
    
    // Should have 3 weeks (week 1, 2, and 3)
    expect($trend)->toHaveCount(3);
    
    // Check that the weeks are correctly represented
    $weeks = $trend->pluck('date')->toArray();
    expect($weeks)->toContain('2023-1');
    expect($weeks)->toContain('2023-2');
    expect($weeks)->toContain('2023-3');
    
    // Week 1 should have 0 since we didn't provide data for it (falls back to placeholder)
    $week1Value = $trend->firstWhere('date', '2023-1');
    expect($week1Value->aggregate)->toBe(0);
    
    // Week 2 should have 10
    $week2Value = $trend->firstWhere('date', '2023-2');
    expect($week2Value->aggregate)->toBe(10);
    
    // Week 3 should have 20
    $week3Value = $trend->firstWhere('date', '2023-3');
    expect($week3Value->aggregate)->toBe(20);
});

it('creates the right sqlite adapter format string for weeks', function () {
    $adapter = new SqliteAdapter();
    
    // Call the format method for the 'week' interval
    $formatString = $adapter->format('created_at', 'week');
    
    // Verify the format contains some expected text
    expect($formatString)->toContain("strftime('%Y', created_at)");
    expect($formatString)->toContain("strftime('%W', created_at) + 1");
}); 