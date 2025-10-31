<?php

declare(strict_types=1);

namespace YouzanApiDataBundle\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use YouzanApiBundle\Entity\Account;
use YouzanApiBundle\Entity\Shop;
use YouzanApiDataBundle\Entity\DailyStat;

#[When(env: 'test')]
class DailyStatFixtures extends AppFixtures
{
    public const DAILY_STATS_REFERENCE_PREFIX = 'daily_stats_';
    public const DAILY_STATS_COUNT = 30;

    public function load(ObjectManager $manager): void
    {
        // 创建多个 Account 和 Shop 以避免唯一约束冲突
        $accounts = [];
        $shops = [];

        // 创建3个 Account
        for ($i = 1; $i <= 3; ++$i) {
            $account = new Account();
            $account->setName("Test Account {$i}");
            $account->setClientId("test-client-id-{$i}");
            $account->setClientSecret("test-client-secret-{$i}");
            $manager->persist($account);
            $accounts[] = $account;
        }

        // 创建3个 Shop
        for ($i = 1; $i <= 3; ++$i) {
            $shop = new Shop();
            $shop->setKdtId(123000 + $i);
            $shop->setName("Test Shop {$i}");
            $manager->persist($shop);
            $shops[] = $shop;
        }

        // 确保 Account 和 Shop 被持久化并生成 ID
        $manager->flush();

        // 生成最近30天的统计数据，确保每个组合唯一
        $usedCombinations = [];
        for ($i = 0; $i < self::DAILY_STATS_COUNT; ++$i) {
            $accountIndex = $i % count($accounts);
            $shopIndex = ($i + 1) % count($shops);

            // 生成日期（从今天开始递减，确保唯一）
            $date = new \DateTime("-{$i} days");
            $currentDay = (int) $date->format('Ymd');

            // 确保组合唯一
            $key = "{$accountIndex}_{$shopIndex}_{$currentDay}";
            if (isset($usedCombinations[$key])) {
                // 如果已经存在，调整日期
                --$currentDay;
            }
            $usedCombinations[$key] = true;

            $stats = new DailyStat();
            $stats->setAccount($accounts[$accountIndex]);
            $stats->setShop($shops[$shopIndex]);
            $stats->setCurrentDay($currentDay);
            $stats->setUv($this->faker->numberBetween(50, 500));
            $stats->setPv($this->faker->numberBetween(200, 2000));
            $stats->setAddCartUv($this->faker->numberBetween(10, 100));
            $stats->setPaidOrderCnt($this->faker->numberBetween(5, 50));
            $stats->setPaidOrderAmt(number_format($this->faker->randomFloat(2, 100, 5000), 2, '.', ''));
            $stats->setExcludeCashbackRefundedAmt(number_format($this->faker->randomFloat(2, 80, 4000), 2, '.', ''));

            $manager->persist($stats);
            $this->addReference(self::DAILY_STATS_REFERENCE_PREFIX . $i, $stats);
        }

        $manager->flush();
    }
}
