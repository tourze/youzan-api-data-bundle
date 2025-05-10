<?php

namespace YouzanApiDataBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use YouzanApiDataBundle\Command\SyncDailyStatsCommand;
use YouzanApiDataBundle\Repository\DailyStatsRepository;

/**
 * 集成测试类，受制于测试环境，目前跳过这些测试
 */
class YouzanApiDataIntegrationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }

    /**
     * @group integration
     */
    public function testServicesAreRegistered(): void
    {
        $this->markTestSkipped('集成测试环境待完善，暂时跳过');
        
        self::bootKernel(['debug' => false]);
        $container = self::getContainer();
        
        // 检查服务是否已注册
        $this->assertTrue($container->has('YouzanApiDataBundle\Repository\DailyStatsRepository'));
        $this->assertTrue($container->has('YouzanApiDataBundle\Command\SyncDailyStatsCommand'));
        
        $repository = $container->get('YouzanApiDataBundle\Repository\DailyStatsRepository');
        $this->assertInstanceOf(DailyStatsRepository::class, $repository);
        
        $command = $container->get('YouzanApiDataBundle\Command\SyncDailyStatsCommand');
        $this->assertInstanceOf(SyncDailyStatsCommand::class, $command);
    }

    /**
     * @group integration
     */
    public function testEntityManagerConfiguration(): void
    {
        $this->markTestSkipped('集成测试环境待完善，暂时跳过');
        
        self::bootKernel(['debug' => false]);
        $container = self::getContainer();
        
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->assertNotNull($entityManager);
        
        $classMetadata = $entityManager->getClassMetadata('YouzanApiDataBundle\Entity\DailyStats');
        $this->assertNotNull($classMetadata);
        
        // 验证实体映射
        $this->assertEquals('ims_youzan_daily_stats', $classMetadata->getTableName());
        $this->assertTrue($classMetadata->hasField('currentDay'));
        $this->assertTrue($classMetadata->hasField('uv'));
        $this->assertTrue($classMetadata->hasField('pv'));
        $this->assertTrue($classMetadata->hasField('addCartUv'));
        $this->assertTrue($classMetadata->hasField('paidOrderCnt'));
        $this->assertTrue($classMetadata->hasField('paidOrderAmt'));
        $this->assertTrue($classMetadata->hasField('excludeCashbackRefundedAmt'));
        
        // 验证关联映射
        $this->assertTrue($classMetadata->hasAssociation('account'));
        $this->assertTrue($classMetadata->hasAssociation('shop'));
        $this->assertEquals('YouzanApiBundle\Entity\Account', $classMetadata->getAssociationTargetClass('account'));
        $this->assertEquals('YouzanApiBundle\Entity\Shop', $classMetadata->getAssociationTargetClass('shop'));
    }
} 