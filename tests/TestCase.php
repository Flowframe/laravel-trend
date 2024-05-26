<?php

namespace Flowframe\Trend\Tests;

use Flowframe\Trend\TrendServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/Fixtures/Database/migrations');

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Flowframe\\Trend\\Tests\\Fixtures\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    /**
     * @return array<int, class-string<PackageServiceProvider>>
     */
    protected function getPackageProviders($app): array
    {
        return [
            TrendServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
