<?php

namespace YouzanApiDataBundle\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\BundleDependency\BundleDependencyInterface;
use YouzanApiBundle\YouzanApiBundle;
use YouzanApiDataBundle\YouzanApiDataBundle;

class YouzanApiDataBundleTest extends TestCase
{
    public function testBundleImplementsCorrectInterface(): void
    {
        $bundle = new YouzanApiDataBundle();
        $this->assertInstanceOf(BundleDependencyInterface::class, $bundle);
    }

    public function testGetBundleDependencies(): void
    {
        $dependencies = YouzanApiDataBundle::getBundleDependencies();
        
        $this->assertIsArray($dependencies);
        $this->assertArrayHasKey(YouzanApiBundle::class, $dependencies);
        $this->assertEquals(['all' => true], $dependencies[YouzanApiBundle::class]);
    }
} 