<?php

namespace YouzanApiDataBundle\Tests\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Youzan\Open\Client;
use YouzanApiBundle\Entity\Account;
use YouzanApiBundle\Entity\Shop;
use YouzanApiBundle\Repository\AccountRepository;
use YouzanApiBundle\Service\YouzanClientService;
use YouzanApiDataBundle\Command\SyncDailyStatsCommand;
use YouzanApiDataBundle\Entity\DailyStats;
use YouzanApiDataBundle\Repository\DailyStatsRepository;

class SyncDailyStatsCommandTest extends TestCase
{
    private $entityManager;
    private $clientService;
    private $accountRepository;
    private $statsRepository;
    private $command;
    private $commandTester;
    private $client;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->clientService = $this->createMock(YouzanClientService::class);
        $this->accountRepository = $this->createMock(AccountRepository::class);
        $this->statsRepository = $this->createMock(DailyStatsRepository::class);
        $this->client = $this->createMock(Client::class);

        $this->command = new SyncDailyStatsCommand(
            $this->entityManager,
            $this->clientService,
            $this->accountRepository,
            $this->statsRepository
        );

        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecute_WithNoAccounts_ReturnsSuccess(): void
    {
        // 模拟没有账号的情况
        $this->accountRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        // 不应该调用客户端服务
        $this->clientService->expects($this->never())
            ->method('getClient');

        // 不应该持久化任何实体
        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->never())
            ->method('flush');

        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecute_WithSpecificAccountId_CallsCorrectMethods(): void
    {
        $accountId = 123;
        $account = $this->createMock(Account::class);
        $shop = $this->createMock(Shop::class);
        $shops = new ArrayCollection([$shop]);
        $startDay = date('Ymd', strtotime('-1 day'));
        $endDay = date('Ymd', strtotime('-1 day'));

        // 模拟账号设置
        $this->accountRepository->expects($this->once())
            ->method('find')
            ->with($accountId)
            ->willReturn($account);

        $account->expects($this->any())
            ->method('getName')
            ->willReturn('测试账号');

        $account->expects($this->once())
            ->method('getShops')
            ->willReturn($shops);

        $shop->expects($this->any())
            ->method('getKdtId')
            ->willReturn(456);

        // 模拟客户端服务
        $this->clientService->expects($this->once())
            ->method('getClient')
            ->with($account)
            ->willReturn($this->client);

        // 模拟API响应
        $apiResponse = [
            'data' => [
                [
                    'kdt_id' => 456,
                    'current_day' => 20230101,
                    'uv' => 100,
                    'pv' => 200,
                    'add_cart_uv' => 50,
                    'paid_order_cnt' => 30,
                    'paid_order_amt' => '1234.56',
                    'exclude_cashback_refunded_amt' => '987.65'
                ]
            ]
        ];

        $this->client->expects($this->once())
            ->method('post')
            ->with(
                'youzan.datacenter.datastandard.team',
                '1.0.0',
                $this->callback(function ($params) use ($startDay, $endDay) {
                    return $params['start_day'] === $startDay
                        && $params['end_day'] === $endDay
                        && $params['date_type'] === '1'
                        && $params['shop_role'] === '1'
                        && strpos($params['kdt_list[]'], '[') !== false;
                })
            )
            ->willReturn($apiResponse);

        // 模拟仓库
        $stats = new DailyStats();
        $this->statsRepository->expects($this->once())
            ->method('findByAccountShopAndDay')
            ->with($account, $shop, 20230101)
            ->willReturn($stats);

        // 验证持久化逻辑
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($entity) use ($account, $shop) {
                return $entity instanceof DailyStats
                    && $entity->getAccount() === $account
                    && $entity->getShop() === $shop
                    && $entity->getCurrentDay() === 20230101
                    && $entity->getUv() === 100
                    && $entity->getPv() === 200
                    && $entity->getAddCartUv() === 50
                    && $entity->getPaidOrderCnt() === 30
                    && $entity->getPaidOrderAmt() === '1234.56'
                    && $entity->getExcludeCashbackRefundedAmt() === '987.65';
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $exitCode = $this->commandTester->execute([
            '--account-id' => $accountId,
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecute_WithInvalidAccountId_ReturnsFailure(): void
    {
        $accountId = 999;

        // 模拟找不到账号
        $this->accountRepository->expects($this->once())
            ->method('find')
            ->with($accountId)
            ->willReturn(null);

        $exitCode = $this->commandTester->execute([
            '--account-id' => $accountId,
        ]);

        $this->assertEquals(1, $exitCode);
        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('不存在', $display);
    }

    public function testExecute_WithApiException_HandlesErrorGracefully(): void
    {
        $account = $this->createMock(Account::class);

        // 模拟账号设置
        $this->accountRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$account]);

        $account->expects($this->any())
            ->method('getName')
            ->willReturn('测试账号');

        // 不需要调用 getShops，因为异常发生在此之前

        // 模拟客户端服务抛出异常
        $this->clientService->expects($this->once())
            ->method('getClient')
            ->with($account)
            ->will($this->throwException(new \RuntimeException('API 调用失败')));

        // 确保不会调用持久化方法
        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->never())
            ->method('flush');

        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(0, $exitCode);
        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('数据同步失败', $display);
        $this->assertStringContainsString('API 调用失败', $display);
    }

    public function testExecute_WithNoShops_HandlesGracefully(): void
    {
        $account = $this->createMock(Account::class);
        $emptyCollection = new ArrayCollection([]);

        // 模拟账号设置但没有店铺
        $this->accountRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$account]);

        $account->expects($this->any())
            ->method('getName')
            ->willReturn('测试账号');

        $account->expects($this->once())
            ->method('getShops')
            ->willReturn($emptyCollection);

        // 模拟客户端服务，但不应调用 post 方法
        $this->clientService->expects($this->once())
            ->method('getClient')
            ->with($account)
            ->willReturn($this->client);

        $this->client->expects($this->never())
            ->method('post');

        // 确保不会调用持久化方法
        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->never())
            ->method('flush');

        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(0, $exitCode);
        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('未关联任何店铺', $display);
    }

    public function testExecute_WithInvalidApiResponse_HandlesErrorGracefully(): void
    {
        $account = $this->createMock(Account::class);
        $shop = $this->createMock(Shop::class);
        $shops = new ArrayCollection([$shop]);

        // 模拟账号设置
        $this->accountRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$account]);

        $account->expects($this->any())
            ->method('getName')
            ->willReturn('测试账号');

        $account->expects($this->once())
            ->method('getShops')
            ->willReturn($shops);

        $shop->expects($this->any())
            ->method('getKdtId')
            ->willReturn(456);

        // 模拟客户端服务返回无效响应
        $this->clientService->expects($this->once())
            ->method('getClient')
            ->with($account)
            ->willReturn($this->client);

        $this->client->expects($this->once())
            ->method('post')
            ->willReturn(['error' => 'Invalid response']);

        // 应该抛出异常但被捕获
        $this->entityManager->expects($this->never())
            ->method('persist');

        $this->entityManager->expects($this->never())
            ->method('flush');

        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(0, $exitCode);
        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('数据同步失败', $display);
    }
}
