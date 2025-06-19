<?php

namespace YouzanApiDataBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

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
    }

    /**
     * @group integration
     */
    public function testEntityManagerConfiguration(): void
    {
        $this->markTestSkipped('集成测试环境待完善，暂时跳过');
    }
} 