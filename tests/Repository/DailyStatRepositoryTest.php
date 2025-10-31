<?php

namespace YouzanApiDataBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use YouzanApiBundle\Entity\Account;
use YouzanApiBundle\Entity\Shop;
use YouzanApiDataBundle\Entity\DailyStat;
use YouzanApiDataBundle\Repository\DailyStatRepository;

/**
 * @internal
 */
#[CoversClass(DailyStatRepository::class)]
#[RunTestsInSeparateProcesses]
final class DailyStatRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 检查当前测试是否需要特殊处理
        $currentTest = $this->name();
        if ('testCountWithDataFixtureShouldReturnGreaterThanZero' === $currentTest) {
            // 为计数测试创建测试数据
            $this->createTestDataForCountTest();
        }
    }

    protected function createNewEntity(): object
    {
        // 创建必需的关联对象，但不持久化它们
        $account = new Account();
        $account->setName('Test Account ' . uniqid());
        $account->setClientId('test-client-id-' . uniqid());
        $account->setClientSecret('test-client-secret-' . uniqid());

        $shop = new Shop();
        $shop->setKdtId(rand(100000, 999999));
        $shop->setName('Test Shop ' . uniqid());

        $entity = new DailyStat();
        $entity->setAccount($account);
        $entity->setShop($shop);
        $entity->setCurrentDay((int) date('Ymd'));
        $entity->setUv(100);
        $entity->setPv(200);
        $entity->setAddCartUv(50);
        $entity->setPaidOrderCnt(10);
        $entity->setPaidOrderAmt('1000.00');
        $entity->setExcludeCashbackRefundedAmt('800.00');

        return $entity;
    }

    protected function getRepository(): DailyStatRepository
    {
        return self::getService(DailyStatRepository::class);
    }

    private function createTestDataForCountTest(): void
    {
        // 创建 Account 和 Shop
        $account = new Account();
        $account->setName('Test Account For Count');
        $account->setClientId('test-client-id-count');
        $account->setClientSecret('test-client-secret-count');

        $shop = new Shop();
        $shop->setKdtId(999999);
        $shop->setName('Test Shop For Count');

        // 创建 DailyStats 记录
        $stats = new DailyStat();
        $stats->setAccount($account);
        $stats->setShop($shop);
        $stats->setCurrentDay((int) date('Ymd'));
        $stats->setUv(100);
        $stats->setPv(200);
        $stats->setAddCartUv(50);
        $stats->setPaidOrderCnt(10);
        $stats->setPaidOrderAmt('1000.00');
        $stats->setExcludeCashbackRefundedAmt('800.00');

        // 保存所有数据
        $em = self::getEntityManager();
        $em->persist($account);
        $em->persist($shop);
        $em->persist($stats);
        $em->flush();
    }

    public function testRepositoryIsCorrectType(): void
    {
        $repository = $this->getRepository();

        $this->assertInstanceOf(DailyStatRepository::class, $repository);
    }

    public function testFindByAccountAndDay(): void
    {
        $repository = $this->getRepository();

        $account = new Account();
        $account->setName('Test Account');
        $account->setClientId('test-client-id');
        $account->setClientSecret('test-client-secret');
        $shop1 = new Shop();
        $shop1->setKdtId(123);
        $shop1->setName('Test Shop 1');
        $shop2 = new Shop();
        $shop2->setKdtId(456);
        $shop2->setName('Test Shop 2');
        $currentDay = 20230101;

        $stats1 = new DailyStat();
        $stats1->setAccount($account);
        $stats1->setShop($shop1);
        $stats1->setCurrentDay($currentDay);
        $stats1->setUv(100);
        $stats1->setPv(200);

        $stats2 = new DailyStat();
        $stats2->setAccount($account);
        $stats2->setShop($shop2);
        $stats2->setCurrentDay($currentDay);
        $stats2->setUv(150);
        $stats2->setPv(300);

        $otherStats = new DailyStat();
        $otherStats->setAccount($account);
        $otherStats->setShop($shop1);
        $otherStats->setCurrentDay(20230102);
        $otherStats->setUv(80);
        $otherStats->setPv(160);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($shop1);
        self::getEntityManager()->persist($shop2);
        self::getEntityManager()->persist($stats1);
        self::getEntityManager()->persist($stats2);
        self::getEntityManager()->persist($otherStats);
        self::getEntityManager()->flush();

        $result = $repository->findByAccountAndDay($account, $currentDay);

        $this->assertCount(2, $result);
        $this->assertEquals($currentDay, $result[0]->getCurrentDay());
        $this->assertEquals($currentDay, $result[1]->getCurrentDay());
        $this->assertEquals($account, $result[0]->getAccount());
        $this->assertEquals($account, $result[1]->getAccount());
    }

    public function testFindByAccountShopAndDay(): void
    {
        $repository = $this->getRepository();

        $account = new Account();
        $account->setName('Test Account');
        $account->setClientId('test-client-id');
        $account->setClientSecret('test-client-secret');
        $shop = new Shop();
        $shop->setKdtId(789);
        $shop->setName('Test Shop');
        $currentDay = 20230101;

        $stats = new DailyStat();
        $stats->setAccount($account);
        $stats->setShop($shop);
        $stats->setCurrentDay($currentDay);
        $stats->setUv(100);
        $stats->setPv(200);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($shop);
        self::getEntityManager()->persist($stats);
        self::getEntityManager()->flush();

        $result = $repository->findByAccountShopAndDay($account, $shop, $currentDay);

        $this->assertNotNull($result);
        $this->assertEquals($account, $result->getAccount());
        $this->assertEquals($shop, $result->getShop());
        $this->assertEquals($currentDay, $result->getCurrentDay());
        $this->assertEquals(100, $result->getUv());
        $this->assertEquals(200, $result->getPv());
    }

    public function testFindByAccountShopAndDayReturnsNullWhenNotFound(): void
    {
        $repository = $this->getRepository();

        $account = new Account();
        $account->setName('Test Account');
        $account->setClientId('test-client-id');
        $account->setClientSecret('test-client-secret');
        $shop = new Shop();
        $shop->setKdtId(789);
        $shop->setName('Test Shop');
        $currentDay = 20230101;

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($shop);
        self::getEntityManager()->flush();

        $result = $repository->findByAccountShopAndDay($account, $shop, $currentDay);

        $this->assertNull($result);
    }

    public function testFindByDateRange(): void
    {
        $repository = $this->getRepository();

        $account = new Account();
        $account->setName('Test Account');
        $account->setClientId('test-client-id');
        $account->setClientSecret('test-client-secret');
        $shop = new Shop();
        $shop->setKdtId(789);
        $shop->setName('Test Shop');

        $stats1 = new DailyStat();
        $stats1->setAccount($account);
        $stats1->setShop($shop);
        $stats1->setCurrentDay(20230101);
        $stats1->setUv(100);
        $stats1->setPv(200);

        $stats2 = new DailyStat();
        $stats2->setAccount($account);
        $stats2->setShop($shop);
        $stats2->setCurrentDay(20230102);
        $stats2->setUv(120);
        $stats2->setPv(240);

        $stats3 = new DailyStat();
        $stats3->setAccount($account);
        $stats3->setShop($shop);
        $stats3->setCurrentDay(20230103);
        $stats3->setUv(110);
        $stats3->setPv(220);

        $statsOutOfRange = new DailyStat();
        $statsOutOfRange->setAccount($account);
        $statsOutOfRange->setShop($shop);
        $statsOutOfRange->setCurrentDay(20230105);
        $statsOutOfRange->setUv(90);
        $statsOutOfRange->setPv(180);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($shop);
        self::getEntityManager()->persist($stats1);
        self::getEntityManager()->persist($stats2);
        self::getEntityManager()->persist($stats3);
        self::getEntityManager()->persist($statsOutOfRange);
        self::getEntityManager()->flush();

        $result = $repository->findByDateRange($account, $shop, 20230101, 20230103);

        $this->assertCount(3, $result);
        $this->assertEquals(20230101, $result[0]->getCurrentDay());
        $this->assertEquals(20230102, $result[1]->getCurrentDay());
        $this->assertEquals(20230103, $result[2]->getCurrentDay());
    }

    public function testFindRecentDays(): void
    {
        $repository = $this->getRepository();

        $account = new Account();
        $account->setName('Test Account');
        $account->setClientId('test-client-id');
        $account->setClientSecret('test-client-secret');
        $shop = new Shop();
        $shop->setKdtId(789);
        $shop->setName('Test Shop');

        $today = (int) date('Ymd');
        $yesterday = (int) date('Ymd', strtotime('-1 day'));
        $twoDaysAgo = (int) date('Ymd', strtotime('-2 days'));
        $weekAgo = (int) date('Ymd', strtotime('-7 days'));

        $statsToday = new DailyStat();
        $statsToday->setAccount($account);
        $statsToday->setShop($shop);
        $statsToday->setCurrentDay($today);
        $statsToday->setUv(100);
        $statsToday->setPv(200);

        $statsYesterday = new DailyStat();
        $statsYesterday->setAccount($account);
        $statsYesterday->setShop($shop);
        $statsYesterday->setCurrentDay($yesterday);
        $statsYesterday->setUv(90);
        $statsYesterday->setPv(180);

        $statsTwoDaysAgo = new DailyStat();
        $statsTwoDaysAgo->setAccount($account);
        $statsTwoDaysAgo->setShop($shop);
        $statsTwoDaysAgo->setCurrentDay($twoDaysAgo);
        $statsTwoDaysAgo->setUv(110);
        $statsTwoDaysAgo->setPv(220);

        $statsWeekAgo = new DailyStat();
        $statsWeekAgo->setAccount($account);
        $statsWeekAgo->setShop($shop);
        $statsWeekAgo->setCurrentDay($weekAgo);
        $statsWeekAgo->setUv(80);
        $statsWeekAgo->setPv(160);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($shop);
        self::getEntityManager()->persist($statsToday);
        self::getEntityManager()->persist($statsYesterday);
        self::getEntityManager()->persist($statsTwoDaysAgo);
        self::getEntityManager()->persist($statsWeekAgo);
        self::getEntityManager()->flush();

        $result = $repository->findRecentDays($account, $shop, 3);

        $this->assertGreaterThanOrEqual(3, count($result));

        foreach ($result as $stats) {
            $this->assertEquals($account, $stats->getAccount());
            $this->assertEquals($shop, $stats->getShop());
            $daysDiff = ($today - $stats->getCurrentDay()) / 10000;
            $this->assertLessThanOrEqual(3, $daysDiff);
        }
    }

    public function testSave(): void
    {
        $repository = $this->getRepository();

        $account = new Account();
        $account->setName('Test Account');
        $account->setClientId('test-client-id');
        $account->setClientSecret('test-client-secret');
        $shop = new Shop();
        $shop->setKdtId(123);
        $shop->setName('Test Shop');

        $stats = new DailyStat();
        $stats->setAccount($account);
        $stats->setShop($shop);
        $stats->setCurrentDay(20230101);
        $stats->setUv(100);
        $stats->setPv(200);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($shop);

        $repository->save($stats, true);

        $this->assertNotNull($stats->getId());

        $savedStats = $repository->find($stats->getId());
        $this->assertNotNull($savedStats);
        $this->assertEquals(20230101, $savedStats->getCurrentDay());
        $this->assertEquals(100, $savedStats->getUv());
        $this->assertEquals(200, $savedStats->getPv());
    }

    public function testRemove(): void
    {
        $repository = $this->getRepository();

        $account = new Account();
        $account->setName('Test Account');
        $account->setClientId('test-client-id');
        $account->setClientSecret('test-client-secret');
        $shop = new Shop();
        $shop->setKdtId(123);
        $shop->setName('Test Shop');

        $stats = new DailyStat();
        $stats->setAccount($account);
        $stats->setShop($shop);
        $stats->setCurrentDay(20230101);
        $stats->setUv(100);
        $stats->setPv(200);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($shop);
        self::getEntityManager()->persist($stats);
        self::getEntityManager()->flush();

        $statsId = $stats->getId();

        $repository->remove($stats, true);

        $removedStats = $repository->find($statsId);
        $this->assertNull($removedStats);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $repository = $this->getRepository();

        $account = new Account();
        $account->setName('Test Account');
        $account->setClientId('test-client-id');
        $account->setClientSecret('test-client-secret');
        $shop = new Shop();
        $shop->setKdtId(123);
        $shop->setName('Test Shop');

        $stats1 = new DailyStat();
        $stats1->setAccount($account);
        $stats1->setShop($shop);
        $stats1->setCurrentDay(20230102);
        $stats1->setUv(100);
        $stats1->setPv(200);

        $stats2 = new DailyStat();
        $stats2->setAccount($account);
        $stats2->setShop($shop);
        $stats2->setCurrentDay(20230101);
        $stats2->setUv(150);
        $stats2->setPv(300);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($shop);
        self::getEntityManager()->persist($stats1);
        self::getEntityManager()->persist($stats2);
        self::getEntityManager()->flush();

        $result = $repository->findOneBy(['account' => $account], ['currentDay' => 'ASC']);

        $this->assertNotNull($result);
        $this->assertEquals(20230101, $result->getCurrentDay());
    }

    public function testCountWithAssociationQuery(): void
    {
        $repository = $this->getRepository();

        $account = new Account();
        $account->setName('Test Account');
        $account->setClientId('test-client-id');
        $account->setClientSecret('test-client-secret');
        $shop = new Shop();
        $shop->setKdtId(123);
        $shop->setName('Test Shop');

        $stats1 = new DailyStat();
        $stats1->setAccount($account);
        $stats1->setShop($shop);
        $stats1->setCurrentDay(20230101);
        $stats1->setUv(100);
        $stats1->setPv(200);

        $stats2 = new DailyStat();
        $stats2->setAccount($account);
        $stats2->setShop($shop);
        $stats2->setCurrentDay(20230102);
        $stats2->setUv(150);
        $stats2->setPv(300);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($shop);
        self::getEntityManager()->persist($stats1);
        self::getEntityManager()->persist($stats2);
        self::getEntityManager()->flush();

        $result = $repository->count(['account' => $account]);

        $this->assertEquals(2, $result);
    }

    public function testFindByAssociationQuery(): void
    {
        $repository = $this->getRepository();

        $account1 = new Account();
        $account1->setName('Test Account 1');
        $account1->setClientId('unique-client-id-' . uniqid());
        $account1->setClientSecret('test-client-secret-1');

        $account2 = new Account();
        $account2->setName('Test Account 2');
        $account2->setClientId('unique-client-id-' . uniqid());
        $account2->setClientSecret('test-client-secret-2');

        $shop = new Shop();
        $shop->setKdtId(999999);
        $shop->setName('Test Shop');

        $stats1 = new DailyStat();
        $stats1->setAccount($account1);
        $stats1->setShop($shop);
        $stats1->setCurrentDay(20230101);
        $stats1->setUv(100);
        $stats1->setPv(200);

        $stats2 = new DailyStat();
        $stats2->setAccount($account2);
        $stats2->setShop($shop);
        $stats2->setCurrentDay(20230101);
        $stats2->setUv(150);
        $stats2->setPv(300);

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->persist($shop);
        self::getEntityManager()->persist($stats1);
        self::getEntityManager()->persist($stats2);
        self::getEntityManager()->flush();

        $result = $repository->findBy(['account' => $account1]);

        $this->assertCount(1, $result);
        $this->assertEquals($account1, $result[0]->getAccount());
    }

    public function testFindByShopAssociationQuery(): void
    {
        $repository = $this->getRepository();

        $account = new Account();
        $account->setName('Test Account');
        $account->setClientId('test-client-id');
        $account->setClientSecret('test-client-secret');

        $shop1 = new Shop();
        $shop1->setKdtId(123);
        $shop1->setName('Test Shop 1');

        $shop2 = new Shop();
        $shop2->setKdtId(456);
        $shop2->setName('Test Shop 2');

        $stats1 = new DailyStat();
        $stats1->setAccount($account);
        $stats1->setShop($shop1);
        $stats1->setCurrentDay(20230101);
        $stats1->setUv(100);
        $stats1->setPv(200);

        $stats2 = new DailyStat();
        $stats2->setAccount($account);
        $stats2->setShop($shop2);
        $stats2->setCurrentDay(20230101);
        $stats2->setUv(150);
        $stats2->setPv(300);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($shop1);
        self::getEntityManager()->persist($shop2);
        self::getEntityManager()->persist($stats1);
        self::getEntityManager()->persist($stats2);
        self::getEntityManager()->flush();

        $result = $repository->findBy(['shop' => $shop1]);

        $this->assertCount(1, $result);
        $this->assertEquals($shop1, $result[0]->getShop());
    }
}
