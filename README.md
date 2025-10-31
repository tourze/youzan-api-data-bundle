# youzan-api-data-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/youzan-api-data-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/youzan-api-data-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/youzan-api-data-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/youzan-api-data-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/youzan-api-data-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/youzan-api-data-bundle)
[![License](https://img.shields.io/packagist/l/tourze/youzan-api-data-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/youzan-api-data-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/youzan-api-data-bundle/tests.yml?branch=master&style=flat-square)](https://github.com/tourze/youzan-api-data-bundle/actions)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/youzan-api-data-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/youzan-api-data-bundle)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/youzan-api-data-bundle/master.svg?style=flat-square)](https://codecov.io/gh/tourze/youzan-api-data-bundle)

A Symfony bundle for integrating with Youzan API data synchronization, providing entities and 
commands for managing Youzan platform daily statistics.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Requirements](#requirements)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Entities](#entities)
  - [Repository Usage](#repository-usage)
  - [Console Commands](#console-commands)
- [Advanced Usage](#advanced-usage)
- [API Integration](#api-integration)
- [Exception Handling](#exception-handling)
- [Performance Optimization](#performance-optimization)
- [Testing](#testing)
- [Contributing](#contributing)
- [Security](#security)
- [License](#license)

## Features

- Daily statistics data synchronization from Youzan API
- Doctrine entities for data persistence
- Console commands for data management
- Repository methods for data querying
- Exception handling for API responses
- Automatic data update on duplicate entries

## Installation

```bash
composer require tourze/youzan-api-data-bundle
```

### Bundle Registration

The bundle should be automatically registered by Symfony Flex. If not, register it manually in 
`config/bundles.php`:

```php
<?php

return [
    // ...
    YouzanApiDataBundle\YouzanApiDataBundle::class => ['all' => true],
];
```

## Requirements

- PHP >= 8.1
- Symfony >= 6.4
- Doctrine ORM
- tourze/youzan-api-bundle

## Configuration

This bundle depends on `tourze/youzan-api-bundle` and requires proper configuration of Youzan 
API credentials.

No additional configuration is required for this bundle. All necessary services are automatically 
configured.

## Usage

### Entities

The bundle provides a `DailyStats` entity that stores daily statistics data from Youzan platform:

```php
<?php

use YouzanApiDataBundle\Entity\DailyStat;
use YouzanApiBundle\Entity\Account;
use YouzanApiBundle\Entity\Shop;

// Create daily stats record
$stats = new DailyStat();
$stats->setAccount($account)
    ->setShop($shop)
    ->setCurrentDay(20240101)
    ->setUv(1000)
    ->setPv(5000)
    ->setAddCartUv(100)
    ->setPaidOrderCnt(50)
    ->setPaidOrderAmt('2500.00')
    ->setExcludeCashbackRefundedAmt('2400.00');
```

### Repository Usage

```php
<?php

use YouzanApiDataBundle\Repository\DailyStatRepository;

// Find stats by account, shop and day
$stats = $repository->findByAccountShopAndDay($account, $shop, 20240101);

// Find stats by date range
$stats = $repository->findByDateRange($account, $shop, 20240101, 20240131);

// Find recent days data
$stats = $repository->findRecentDays($account, $shop, 7);
```

### Console Commands

#### youzan:sync:daily-stats

Synchronize daily statistics data from Youzan API.

**Usage:**
```bash
# Sync yesterday's data for all accounts
bin/console youzan:sync:daily-stats

# Sync specific date range
bin/console youzan:sync:daily-stats --start-day=20240101 --end-day=20240131

# Sync specific account
bin/console youzan:sync:daily-stats --account-id=1

# Combine options
bin/console youzan:sync:daily-stats --start-day=20240101 --end-day=20240131 --account-id=1
```

**Options:**
- `--start-day`: Start date in YYYYMMDD format (default: yesterday)
- `--end-day`: End date in YYYYMMDD format (default: yesterday)
- `--account-id`: Specific Youzan account ID to sync

## Advanced Usage

### Batch Data Processing

For large datasets, consider using repository methods with pagination:

```php
<?php

use YouzanApiDataBundle\Repository\DailyStatRepository;

// Process data in batches
$batchSize = 1000;
$offset = 0;

do {
    $batch = $repository->findBy([], null, $batchSize, $offset);
    foreach ($batch as $stats) {
        // Process each stats record
    }
    $offset += $batchSize;
} while (count($batch) === $batchSize);
```

### Custom Data Analysis

You can extend the repository to add custom analysis methods:

```php
<?php

namespace App\Repository;

use YouzanApiDataBundle\Repository\DailyStatRepository;

class CustomDailyStatsRepository extends DailyStatRepository
{
    public function calculateMonthlyTrends(Account $account, Shop $shop, int $month): array
    {
        return $this->createQueryBuilder('ds')
            ->select('ds.currentDay, ds.paidOrderAmt')
            ->andWhere('ds.account = :account')
            ->andWhere('ds.shop = :shop')
            ->andWhere('ds.currentDay LIKE :month')
            ->setParameter('account', $account)
            ->setParameter('shop', $shop)
            ->setParameter('month', $month . '%')
            ->orderBy('ds.currentDay', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
```

### Event-Driven Data Processing

Use Symfony events to trigger additional processing after data sync:

```php
<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use YouzanApiDataBundle\Event\DailyStatsUpdatedEvent;

class DailyStatsEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            DailyStatsUpdatedEvent::class => 'onDailyStatsUpdated',
        ];
    }

    public function onDailyStatsUpdated(DailyStatsUpdatedEvent $event): void
    {
        $stats = $event->getDailyStats();
        // Trigger additional analysis, notifications, etc.
    }
}
```

## API Integration

The bundle integrates with Youzan Data Center API to fetch daily statistics including:

- UV (Unique Visitors)
- PV (Page Views)
- Add to Cart Users
- Paid Order Count
- Paid Order Amount
- Exclude Cashback Refunded Amount

### API Response Format

The bundle expects the following response format from Youzan API:

```json
{
    "data": [
        {
            "kdt_id": 456,
            "current_day": 20240101,
            "uv": 1000,
            "pv": 5000,
            "add_cart_uv": 100,
            "paid_order_cnt": 50,
            "paid_order_amt": "2500.00",
            "exclude_cashback_refunded_amt": "2400.00"
        }
    ]
}
```

## Exception Handling

The bundle provides `ApiResponseException` for handling API errors:

```php
<?php

use YouzanApiDataBundle\Exception\ApiResponseException;

try {
    // API call
} catch (ApiResponseException $e) {
    // Handle API error
    $errorMessage = $e->getMessage();
    $errorCode = $e->getCode();
}
```

## Performance Optimization

- The sync command processes data in batches to optimize database operations
- Duplicate entries are automatically updated instead of creating new records
- Use date range parameters to limit the amount of data synchronized
- Consider running the sync command during off-peak hours

## Testing

Run tests with PHPUnit:

```bash
./vendor/bin/phpunit packages/youzan-api-data-bundle/tests
```

Run static analysis with PHPStan:

```bash
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/youzan-api-data-bundle
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

Before submitting a pull request:
- Run tests: `./vendor/bin/phpunit packages/youzan-api-data-bundle/tests`
- Run PHPStan: `php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/youzan-api-data-bundle`
- Follow PSR-12 coding standards
- Write or update tests for new features
- Update documentation as needed

## Security

If you discover any security related issues, please email security@tourze.com instead of using 
the issue tracker.

## Credits

- [Tourze Team](https://github.com/tourze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.