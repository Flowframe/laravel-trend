# Laravel Trend

Generate trends for your models. Easily generate charts or reports.

## Why?

Most applications require charts or reports to be generated. Doing this over again, and again can be a painful process. That's why we've created a fluent Laravel package to solve this problem.

You can aggregate average, min, max, and totals per minute, hour, day, month, and year.

## Installation

You can install the package via composer:

```bash
composer require flowframe/laravel-trend
```

## Usage

To generate a trend for your model, import the `Flowframe\Trend\Trend` class and pass along a model or query.

Example:

```php
// Totals per month
$trend = Trend::model(User::class)
    ->between(
        start: now()->startOfYear(),
        end: now()->endOfYear(),
    )
    ->perMonth()
    ->count();

// Average user weight where name starts with a over a span of 11 years, results are grouped per year
$trend = Trend::query(User::where('name', 'like', 'a%'))
    ->between(
        start: now()->startOfYear()->subYears(10),
        end: now()->endOfYear(),
    )
    ->perYear()
    ->average('weight');
```

## Starting a trend

You must either start a trend using `::model()` or `::query()`. The difference between the two is that using `::query()` allows you to add additional filters, just like you're used to using eloquent. Using `::model()` will just consume it as it is.

```php
// Model
Trend::model(Order::class)
    ->between(...)
    ->perDay()
    ->count();

// More specific order query
Trend::query(
    Order::query()
        ->hasBeenPaid()
        ->hasBeenShipped()
)
    ->between(...)
    ->perDay()
    ->count();
```

## Interval

You can use the following aggregates intervals:

-   `perMinute()`
-   `perHour()`
-   `perDay()`
-   `perMonth()`
-   `perYear()`

## Aggregates

You can use the following aggregates:

-   `sum('column')`
-   `average('column')`
-   `max('column')`
-   `min('column')`
-   `count('*')`

## Date Column

By default, laravel-trend assumes that the model on which the operation is being performed has a `created_at` date column. If your model uses a different column name for the date or you want to use a different one, you should specify it using the `dateColumn(string $column)` method.

Example:

```php
Trend::model(Order::class)
    ->dateColumn('custom_date_column')
    ->between(...)
    ->perDay()
    ->count();
```

This allows you to work with models that have custom date column names or when you want to analyze data based on a different date column.

## Date Column Alias

By default, laravel-trend creates a grouping select field in its database query named `date`. This can cause problems if you have a column on that table already named `date`. To avoid this conflict in the query specify a different name with `dateAlias(string $alias)` method.

Example:

```php
Trend::model(Order::class)
    ->dateColumn('date')
    ->dateAlias('date_alias')
    ->between(...)
    ->perDay()
    ->count();
```

This allows you to work with models that have a column named `date`.

## Drivers

We currently support four drivers:

-   MySQL
-   MariaDB
-   SQLite
-   PostgreSQL

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Lars Klopstra](https://github.com/flowframe)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
