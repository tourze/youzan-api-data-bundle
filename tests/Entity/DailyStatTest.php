<?php

namespace YouzanApiDataBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use YouzanApiBundle\Entity\Account;
use YouzanApiBundle\Entity\Shop;
use YouzanApiDataBundle\Entity\DailyStat;

/**
 * @internal
 */
#[CoversClass(DailyStat::class)]
final class DailyStatTest extends AbstractEntityTestCase
{
    private DailyStat $dailyStats;

    private Account $account;

    private Shop $shop;

    protected function createEntity(): DailyStat
    {
        return new DailyStat();
    }

    /**
     * 提供属性及其样本值的 Data Provider.
     *
     * @return \Generator<string, array{string, mixed}>
     */
    public static function propertiesProvider(): \Generator
    {
        yield 'currentDay' => ['currentDay', 20230101];
        yield 'uv' => ['uv', 100];
        yield 'pv' => ['pv', 200];
        yield 'addCartUv' => ['addCartUv', 50];
        yield 'paidOrderCnt' => ['paidOrderCnt', 30];
        yield 'paidOrderAmt' => ['paidOrderAmt', '1234.56'];
        yield 'excludeCashbackRefundedAmt' => ['excludeCashbackRefundedAmt', '987.65'];
        yield 'createTime' => ['createTime', new \DateTimeImmutable()];
        yield 'updateTime' => ['updateTime', new \DateTimeImmutable()];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // 使用 AbstractEntityTest 提供的实体创建方法
        $this->dailyStats = $this->createEntity();

        // 创建真实的实体对象进行测试
        $this->account = new Account();
        $this->account->setName('Test Account');
        $this->account->setClientId('test-client-id');
        $this->account->setClientSecret('test-client-secret');

        $this->shop = new Shop();
        $this->shop->setKdtId(123);
        $this->shop->setName('Test Shop');
    }

    public function testInitialValues(): void
    {
        $this->assertNull($this->dailyStats->getId());
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
        $createTime = new \DateTimeImmutable();
        $this->dailyStats->setCreateTime($createTime);
        $this->assertSame($createTime, $this->dailyStats->getCreateTime());
    }

    public function testSetAndGetUpdateTime(): void
    {
        $updateTime = new \DateTimeImmutable();
        $this->dailyStats->setUpdateTime($updateTime);
        $this->assertSame($updateTime, $this->dailyStats->getUpdateTime());
    }

    public function testSettersWorkCorrectly(): void
    {
        $this->dailyStats->setAccount($this->account);
        $this->dailyStats->setShop($this->shop);
        $this->dailyStats->setCurrentDay(20230101);
        $this->dailyStats->setUv(100);
        $this->dailyStats->setPv(200);
        $this->dailyStats->setAddCartUv(50);
        $this->dailyStats->setPaidOrderCnt(30);
        $this->dailyStats->setPaidOrderAmt('1234.56');
        $this->dailyStats->setExcludeCashbackRefundedAmt('987.65');

        $this->assertSame($this->account, $this->dailyStats->getAccount());
        $this->assertSame($this->shop, $this->dailyStats->getShop());
        $this->assertSame(20230101, $this->dailyStats->getCurrentDay());
        $this->assertSame(100, $this->dailyStats->getUv());
        $this->assertSame(200, $this->dailyStats->getPv());
        $this->assertSame(50, $this->dailyStats->getAddCartUv());
        $this->assertSame(30, $this->dailyStats->getPaidOrderCnt());
        $this->assertSame('1234.56', $this->dailyStats->getPaidOrderAmt());
        $this->assertSame('987.65', $this->dailyStats->getExcludeCashbackRefundedAmt());
    }
}
