---
category: Getting started
title: Installation and setup
order: 3
---

You can install the package via composer:

```
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

## Settings

You can use the following settings:

- `dataColumn('column')`
- `datapointAlias('alias')`

### dateColumn()  
The trend will per default be based on the 'created_at' attribute of your model.
You can change this to use another field by using ```->dateColumn()```:

- `datecolumn('column')`

### datapointAlias()
If your model already contains a field names ```date``` you will have to set the ```datapointAlias``` to something else.
The value should not an existing field in your table or a reserved word.

- `datapointAlias('datapointAlias')`
