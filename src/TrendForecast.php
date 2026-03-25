<?php

namespace Flowframe\Trend;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class TrendForecast
{
    protected Collection $historicalData;
    protected string $interval;
    protected CarbonInterface $forecastStart;
    protected CarbonInterface $forecastEnd;
    protected string $dateFormat;
    protected string $method = 'linear';
    
    public function __construct(Collection $historicalData, string $interval, CarbonInterface $forecastStart, CarbonInterface $forecastEnd)
    {
        $this->historicalData = $historicalData;
        $this->interval = $interval;
        $this->forecastStart = $forecastStart;
        $this->forecastEnd = $forecastEnd;
        $this->dateFormat = $this->getCarbonDateFormat();
    }
    
    public function method(string $method): self
    {
        $this->method = $method;
        
        return $this;
    }
    
    public function linear(): Collection
    {
        // Simple linear regression
        $dataPoints = $this->historicalData->count();
        if ($dataPoints <= 1) {
            return $this->generateEmptyForecast();
        }
        
        // Prepare X and Y values for regression
        $xValues = range(1, $dataPoints);
        $yValues = $this->historicalData->pluck('aggregate')->toArray();
        
        // Calculate means
        $xMean = array_sum($xValues) / $dataPoints;
        $yMean = array_sum($yValues) / $dataPoints;
        
        // Calculate slope and intercept
        $numerator = 0;
        $denominator = 0;
        
        for ($i = 0; $i < $dataPoints; $i++) {
            $numerator += ($xValues[$i] - $xMean) * ($yValues[$i] - $yMean);
            $denominator += pow($xValues[$i] - $xMean, 2);
        }
        
        // Avoid division by zero
        if ($denominator == 0) {
            $slope = 0;
        } else {
            $slope = $numerator / $denominator;
        }
        
        $intercept = $yMean - ($slope * $xMean);
        
        return $this->generateForecast(function($index) use ($slope, $intercept, $dataPoints) {
            // Forecast value using y = mx + b
            return max(0, $intercept + $slope * ($dataPoints + $index));
        });
    }
    
    public function movingAverage(int $periods = 3): Collection
    {
        if ($this->historicalData->count() < $periods) {
            return $this->generateEmptyForecast();
        }
        
        // Get the last n periods to calculate moving average
        $lastValues = $this->historicalData->pluck('aggregate')->take(-$periods)->toArray();
        $average = array_sum($lastValues) / count($lastValues);
        
        return $this->generateForecast(function() use ($average) {
            return $average;
        });
    }
    
    public function weightedMovingAverage(array $weights = null): Collection
    {
        $dataPoints = $this->historicalData->count();
        
        // Default weights based on data points, more recent = more weight
        if ($weights === null) {
            $weights = [];
            $totalPoints = min(5, $dataPoints);
            
            for ($i = 1; $i <= $totalPoints; $i++) {
                $weights[] = $i;
            }
            
            // Normalize weights to sum to 1
            $sum = array_sum($weights);
            $weights = array_map(function($w) use ($sum) {
                return $w / $sum;
            }, $weights);
        }
        
        if ($dataPoints < count($weights)) {
            return $this->generateEmptyForecast();
        }
        
        // Get the most recent values for the calculation
        $recentValues = $this->historicalData->pluck('aggregate')->take(-count($weights))->toArray();
        
        // Calculate weighted average
        $weightedSum = 0;
        for ($i = 0; $i < count($weights); $i++) {
            $weightedSum += $recentValues[$i] * $weights[$i];
        }
        
        return $this->generateForecast(function() use ($weightedSum) {
            return $weightedSum;
        });
    }
    
    public function exponentialSmoothing(float $alpha = 0.3): Collection
    {
        if ($this->historicalData->isEmpty()) {
            return $this->generateEmptyForecast();
        }
        
        // Get last observed value
        $lastValue = $this->historicalData->last()->aggregate;
        
        // Calculate the EMA based on the historical data
        $smoothedValue = $lastValue;
        
        foreach ($this->historicalData as $dataPoint) {
            $smoothedValue = $alpha * $dataPoint->aggregate + (1 - $alpha) * $smoothedValue;
        }
        
        return $this->generateForecast(function() use ($smoothedValue) {
            return $smoothedValue;
        });
    }
    
    public function generate(): Collection
    {
        return match ($this->method) {
            'linear' => $this->linear(),
            'moving-average' => $this->movingAverage(),
            'weighted-moving-average' => $this->weightedMovingAverage(),
            'exponential-smoothing' => $this->exponentialSmoothing(),
            default => $this->linear(),
        };
    }
    
    protected function generateEmptyForecast(): Collection
    {
        return $this->generateForecast(function() {
            return 0;
        });
    }
    
    protected function generateForecast(callable $valueGenerator): Collection
    {
        $period = CarbonPeriod::between(
            $this->forecastStart,
            $this->forecastEnd,
        )->interval("1 {$this->interval}");
        
        $forecasts = collect();
        $index = 0;
        
        foreach ($period as $date) {
            $forecasts->push(new TrendValue(
                date: $date->format($this->dateFormat),
                aggregate: $valueGenerator($index),
            ));
            $index++;
        }
        
        return $forecasts;
    }
    
    protected function getCarbonDateFormat(): string
    {
        return match ($this->interval) {
            'minute' => 'Y-m-d H:i:00',
            'hour' => 'Y-m-d H:00',
            'day' => 'Y-m-d',
            'week' => 'Y-W',
            'month' => 'Y-m',
            'year' => 'Y',
            default => 'Y-m-d',
        };
    }
} 