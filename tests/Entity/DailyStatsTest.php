<?php

namespace YouzanApiDataBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use YouzanApiBundle\Entity\Account;
use YouzanApiBundle\Entity\Shop;
use YouzanApiDataBundle\Entity\DailyStats;

class DailyStatsTest extends TestCase
{
    private DailyStats $dailyStats;
    private Account $account;
    private Shop $shop;

    protected function setUp(): void
    {
        $this->account = $this->createMock(Account::class);
        $this->shop = $this->createMock(Shop::class);
        $this->dailyStats = new DailyStats();
    }

    public function testInitialValues(): void
    {
        $this->assertSame(0, $this->dailyStats->getId());
        $this->assertSame(0, $this->dailyStats->getUv());
        $this->assertSame(0, $this->dailyStats->getPv());
        $this->assertSame(0, $this->dailyStats->getAddCartUv());
        $this->assertSame(0, $this->dailyStats->getPaidOrderCnt());
        $this->assertSame('0.00', $this->dailyStats->getPaidOrderAmt());
        $this->assertSame('0.00', $this->dailyStats->getExcludeCashbackRefundedAmt());
        $this->assertNull($this->dailyStats->getCreateTime());
        $this->assertNull($this->dailyStats->getUpdateTime());
    }

    public function testSetAndGetAccount(): void
    {
        $this->dailyStats->setAccount($this->account);
        $this->assertSame($this->account, $this->dailyStats->getAccount());
    }

    public function testSetAndGetShop(): void
    {
        $this->dailyStats->setShop($this->shop);
        $this->assertSame($this->shop, $this->dailyStats->getShop());
    }

    public function testSetAndGetCurrentDay(): void
    {
        $currentDay = 20230101;
        $this->dailyStats->setCurrentDay($currentDay);
        $this->assertSame($currentDay, $this->dailyStats->getCurrentDay());
    }

    public function testSetAndGetUv(): void
    {
        $uv = 100;
        $this->dailyStats->setUv($uv);
        $this->assertSame($uv, $this->dailyStats->getUv());
    }

    public function testSetAndGetPv(): void
    {
        $pv = 200;
        $this->dailyStats->setPv($pv);
        $this->assertSame($pv, $this->dailyStats->getPv());
    }

    public function testSetAndGetAddCartUv(): void
    {
        $addCartUv = 50;
        $this->dailyStats->setAddCartUv($addCartUv);
        $this->assertSame($addCartUv, $this->dailyStats->getAddCartUv());
    }

    public function testSetAndGetPaidOrderCnt(): void
    {
        $paidOrderCnt = 30;
        $this->dailyStats->setPaidOrderCnt($paidOrderCnt);
        $this->assertSame($paidOrderCnt, $this->dailyStats->getPaidOrderCnt());
    }

    public function testSetAndGetPaidOrderAmt(): void
    {
        $paidOrderAmt = '1234.56';
        $this->dailyStats->setPaidOrderAmt($paidOrderAmt);
        $this->assertSame($paidOrderAmt, $this->dailyStats->getPaidOrderAmt());
    }

    public function testSetAndGetExcludeCashbackRefundedAmt(): void
    {
        $excludeCashbackRefundedAmt = '987.65';
        $this->dailyStats->setExcludeCashbackRefundedAmt($excludeCashbackRefundedAmt);
        $this->assertSame($excludeCashbackRefundedAmt, $this->dailyStats->getExcludeCashbackRefundedAmt());
    }

    public function testSetAndGetCreateTime(): void
    {
        $createTime = new \DateTime();
        $this->dailyStats->setCreateTime($createTime);
        $this->assertSame($createTime, $this->dailyStats->getCreateTime());
    }

    public function testSetAndGetUpdateTime(): void
    {
        $updateTime = new \DateTime();
        $this->dailyStats->setUpdateTime($updateTime);
        $this->assertSame($updateTime, $this->dailyStats->getUpdateTime());
    }

    public function testFluentInterface(): void
    {
        $result = $this->dailyStats
            ->setAccount($this->account)
            ->setShop($this->shop)
            ->setCurrentDay(20230101)
            ->setUv(100)
            ->setPv(200)
            ->setAddCartUv(50)
            ->setPaidOrderCnt(30)
            ->setPaidOrderAmt('1234.56')
            ->setExcludeCashbackRefundedAmt('987.65');
        
        $this->assertSame($this->dailyStats, $result);
    }
} 