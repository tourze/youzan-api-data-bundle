<?php

namespace YouzanApiDataBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use YouzanApiDataBundle\DependencyInjection\YouzanApiDataExtension;

/**
 * @internal
 */
#[CoversClass(YouzanApiDataExtension::class)]
final class YouzanApiDataExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private YouzanApiDataExtension $extension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension = new YouzanApiDataExtension();
    }

    public function testGetConfigDir(): void
    {
        $reflection = new \ReflectionMethod($this->extension, 'getConfigDir');
        $this->assertTrue($reflection->isProtected());

        $reflection->setAccessible(true);
        $configDir = $reflection->invoke($this->extension);

        $this->assertIsString($configDir);
        $this->assertStringEndsWith('/Resources/config', $configDir);
        $this->assertDirectoryExists($configDir);
    }

    public function testGetAlias(): void
    {
        $this->assertSame('youzan_api_data', $this->extension->getAlias());
    }
}
