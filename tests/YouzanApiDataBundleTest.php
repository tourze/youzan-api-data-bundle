<?php

declare(strict_types=1);

namespace YouzanApiDataBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use YouzanApiDataBundle\YouzanApiDataBundle;

/**
 * @internal
 */
#[CoversClass(YouzanApiDataBundle::class)]
#[RunTestsInSeparateProcesses]
final class YouzanApiDataBundleTest extends AbstractBundleTestCase
{
}
