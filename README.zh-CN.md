# youzan-api-data-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/youzan-api-data-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/youzan-api-data-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/youzan-api-data-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/youzan-api-data-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/youzan-api-data-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/youzan-api-data-bundle)
[![License](https://img.shields.io/packagist/l/tourze/youzan-api-data-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/youzan-api-data-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/youzan-api-data-bundle/tests.yml?branch=master&style=flat-square)](https://github.com/tourze/youzan-api-data-bundle/actions)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/youzan-api-data-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/youzan-api-data-bundle)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/youzan-api-data-bundle/master.svg?style=flat-square)](https://codecov.io/gh/tourze/youzan-api-data-bundle)

用于集成有赞 API 数据同步的 Symfony Bundle，提供实体和命令来管理有赞平台的每日统计数据。

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [系统要求](#系统要求)
- [配置](#配置)
- [使用方法](#使用方法)
  - [实体](#实体)
  - [仓储使用](#仓储使用)
  - [控制台命令](#控制台命令)
- [高级用法](#高级用法)
- [API 集成](#api-集成)
- [异常处理](#异常处理)
- [性能优化](#性能优化)
- [测试](#测试)
- [贡献](#贡献)
- [安全](#安全)
- [License](#license)

## 功能特性

- 有赞 API 每日统计数据同步
- Doctrine 实体用于数据持久化
- 控制台命令用于数据管理
- 仓储方法用于数据查询
- API 响应异常处理
- 自动更新重复数据

## 安装

```bash
composer require tourze/youzan-api-data-bundle
```

### Bundle 注册

Bundle 应该通过 Symfony Flex 自动注册。如果没有，请在 `config/bundles.php` 中手动注册：

```php
<?php

return [
    // ...
    YouzanApiDataBundle\YouzanApiDataBundle::class => ['all' => true],
];
```

## 系统要求

- PHP >= 8.1
- Symfony >= 6.4
- Doctrine ORM
- tourze/youzan-api-bundle

## 配置

此 Bundle 依赖于 `tourze/youzan-api-bundle`，需要正确配置有赞 API 凭据。

此 Bundle 无需额外配置。所有必要的服务都会自动配置。

## 使用方法

### 实体

Bundle 提供了 `DailyStats` 实体，用于存储来自有赞平台的每日统计数据：

```php
<?php

use YouzanApiDataBundle\Entity\DailyStat;
use YouzanApiBundle\Entity\Account;
use YouzanApiBundle\Entity\Shop;

// 创建每日统计记录
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

### 仓储使用

```php
<?php

use YouzanApiDataBundle\Repository\DailyStatRepository;

// 根据账号、店铺和日期查找统计数据
$stats = $repository->findByAccountShopAndDay($account, $shop, 20240101);

// 根据日期范围查找统计数据
$stats = $repository->findByDateRange($account, $shop, 20240101, 20240131);

// 查找最近几天的数据
$stats = $repository->findRecentDays($account, $shop, 7);
```

### 控制台命令

#### youzan:sync:daily-stats

从有赞 API 同步每日统计数据。

**用法：**
```bash
# 同步所有账号的昨日数据
bin/console youzan:sync:daily-stats

# 同步指定日期范围
bin/console youzan:sync:daily-stats --start-day=20240101 --end-day=20240131

# 同步指定账号
bin/console youzan:sync:daily-stats --account-id=1

# 组合选项
bin/console youzan:sync:daily-stats --start-day=20240101 --end-day=20240131 --account-id=1
```

**选项：**
- `--start-day`: 开始日期，格式为 YYYYMMDD（默认：昨天）
- `--end-day`: 结束日期，格式为 YYYYMMDD（默认：昨天）
- `--account-id`: 指定要同步的有赞账号 ID

## 高级用法

### 批量数据处理

对于大型数据集，考虑使用分页的仓储方法：

```php
<?php

use YouzanApiDataBundle\Repository\DailyStatRepository;

// 分批处理数据
$batchSize = 1000;
$offset = 0;

do {
    $batch = $repository->findBy([], null, $batchSize, $offset);
    foreach ($batch as $stats) {
        // 处理每个统计记录
    }
    $offset += $batchSize;
} while (count($batch) === $batchSize);
```

### 自定义数据分析

您可以扩展仓储以添加自定义分析方法：

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

### 事件驱动的数据处理

使用 Symfony 事件在数据同步后触发额外处理：

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
        // 触发额外的分析、通知等
    }
}
```

## API 集成

Bundle 与有赞数据中心 API 集成，获取每日统计数据，包括：

- UV（独立访客数）
- PV（页面浏览量）
- 加购用户数
- 支付订单数
- 支付金额
- 不含退款的支付金额

### API 响应格式

Bundle 期望有赞 API 返回以下格式的响应：

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

## 异常处理

Bundle 提供 `ApiResponseException` 用于处理 API 错误：

```php
<?php

use YouzanApiDataBundle\Exception\ApiResponseException;

try {
    // API 调用
} catch (ApiResponseException $e) {
    // 处理 API 错误
    $errorMessage = $e->getMessage();
    $errorCode = $e->getCode();
}
```

## 性能优化

- 同步命令以批次处理数据以优化数据库操作
- 重复条目会自动更新而不是创建新记录
- 使用日期范围参数限制同步的数据量
- 考虑在非高峰时段运行同步命令

## 测试

使用 PHPUnit 运行测试：

```bash
./vendor/bin/phpunit packages/youzan-api-data-bundle/tests
```

使用 PHPStan 运行静态分析：

```bash
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/youzan-api-data-bundle
```

## 贡献

请查看 [CONTRIBUTING.md](CONTRIBUTING.md) 了解详情。

提交 Pull Request 前：
- 运行测试：`./vendor/bin/phpunit packages/youzan-api-data-bundle/tests`
- 运行 PHPStan：`php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/youzan-api-data-bundle`
- 遵循 PSR-12 编码标准
- 为新功能编写或更新测试
- 根据需要更新文档

## 安全

如果您发现任何安全相关问题，请发送邮件到 security@tourze.com，而不是使用问题跟踪器。

## 贡献者

- [Tourze Team](https://github.com/tourze)
- [所有贡献者](../../contributors)

## License

MIT 协议。请查看 [License File](LICENSE) 了解更多信息。