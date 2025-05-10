<?php

namespace YouzanApiDataBundle\Tests\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use YouzanApiBundle\Entity\Account;
use YouzanApiBundle\Entity\Shop;
use YouzanApiDataBundle\Entity\DailyStats;
use YouzanApiDataBundle\Repository\DailyStatsRepository;

class DailyStatsRepositoryTest extends TestCase
{
    private $managerRegistry;
    private $repository;
    private $entityRepositoryMock;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        
        // 模拟实际仓库
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects($this->any())
            ->method('getName')
            ->willReturn(DailyStats::class);
            
        $this->entityRepositoryMock = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $this->repository = $this->getMockBuilder(DailyStatsRepository::class)
            ->setConstructorArgs([$this->managerRegistry])
            ->onlyMethods(['findOneBy', 'findBy', 'createQueryBuilder'])
            ->getMock();
    }

    public function testFindByAccountShopAndDay_ReturnsCorrectResult(): void
    {
        $account = $this->createMock(Account::class);
        $shop = $this->createMock(Shop::class);
        $currentDay = 20230101;
        $expectedResult = new DailyStats();
        
        // 设置 findOneBy 预期
        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'account' => $account,
                'shop' => $shop,
                'currentDay' => $currentDay
            ])
            ->willReturn($expectedResult);
            
        $result = $this->repository->findByAccountShopAndDay($account, $shop, $currentDay);
        
        $this->assertSame($expectedResult, $result);
    }

    public function testFindByDateRange_BuildsCorrectQuery(): void
    {
        $account = $this->createMock(Account::class);
        $shop = $this->createMock(Shop::class);
        $startDay = 20230101;
        $endDay = 20230131;
        $expectedResult = [new DailyStats(), new DailyStats()];
        
        // 创建查询构建器模拟
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        // 使用 Query 类的模拟而不是 AbstractQuery
        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        // 设置查询构建器行为
        $queryBuilder->expects($this->exactly(4))
            ->method('andWhere')
            ->willReturnSelf();
            
        $queryBuilder->expects($this->exactly(4))
            ->method('setParameter')
            ->willReturnSelf();
            
        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('s.currentDay', 'ASC')
            ->willReturnSelf();
            
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
            
        $query->expects($this->once())
            ->method('getResult')
            ->willReturn($expectedResult);
            
        // 设置仓库行为
        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('s')
            ->willReturn($queryBuilder);
            
        $result = $this->repository->findByDateRange($account, $shop, $startDay, $endDay);
        
        $this->assertSame($expectedResult, $result);
    }

    public function testFindRecentDays_CallsFindByDateRangeWithCorrectParameters(): void
    {
        $account = $this->createMock(Account::class);
        $shop = $this->createMock(Shop::class);
        $days = 7;
        $expectedResult = [new DailyStats()];
        
        // 创建一个部分模拟，只模拟 findByDateRange 方法
        $repositoryMock = $this->getMockBuilder(DailyStatsRepository::class)
            ->setConstructorArgs([$this->managerRegistry])
            ->onlyMethods(['findByDateRange'])
            ->getMock();
            
        // 计算预期的开始和结束日期
        $endDay = (int)date('Ymd');
        $startDay = (int)date('Ymd', strtotime("-{$days} days"));
        
        // 设置预期的调用和返回值
        $repositoryMock->expects($this->once())
            ->method('findByDateRange')
            ->with($account, $shop, $startDay, $endDay)
            ->willReturn($expectedResult);
            
        $result = $repositoryMock->findRecentDays($account, $shop, $days);
        
        $this->assertSame($expectedResult, $result);
    }

    public function testFindByAccountAndDay_ReturnsCorrectResult(): void
    {
        $account = $this->createMock(Account::class);
        $currentDay = 20230101;
        $expectedResult = [new DailyStats(), new DailyStats()];
        
        // 设置 findBy 预期
        $this->repository->expects($this->once())
            ->method('findBy')
            ->with([
                'account' => $account,
                'currentDay' => $currentDay
            ])
            ->willReturn($expectedResult);
            
        $result = $this->repository->findByAccountAndDay($account, $currentDay);
        
        $this->assertSame($expectedResult, $result);
    }
} 