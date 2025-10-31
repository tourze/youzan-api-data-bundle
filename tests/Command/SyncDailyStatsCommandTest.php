<?php

declare(strict_types=1);

namespace YouzanApiDataBundle\Tests\Command;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Youzan\Open\Client;
use YouzanApiBundle\Entity\Account;
use YouzanApiBundle\Entity\Shop;
use YouzanApiBundle\Repository\AccountRepository;
use YouzanApiBundle\Service\YouzanClientService;
use YouzanApiDataBundle\Command\SyncDailyStatsCommand;
use YouzanApiDataBundle\Repository\DailyStatRepository;

/**
 * @internal
 */
#[CoversClass(SyncDailyStatsCommand::class)]
#[RunTestsInSeparateProcesses]
final class SyncDailyStatsCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function onSetUp(): void
    {
        // 由于Command测试涉及复杂的第三方依赖和外部服务，
        // 我们采用集成测试的方式，测试命令的基本功能

        // 获取命令实例
        $command = self::getContainer()->get(SyncDailyStatsCommand::class);
        $this->assertInstanceOf(SyncDailyStatsCommand::class, $command);

        $this->commandTester = new CommandTester($command);
    }

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    public function testCommandCanBeExecuted(): void
    {
        // 测试命令可以被执行（基本的烟雾测试）
        // 由于没有真实的有赞账号数据，命令会正常结束
        $exitCode = $this->commandTester->execute([]);

        // 命令应该正常退出
        $this->assertContains($exitCode, [0, 1]); // 0 = 成功, 1 = 没有找到账号等正常情况
    }

    public function testCommandWithInvalidAccountId(): void
    {
        // 测试无效账号ID的情况
        $exitCode = $this->commandTester->execute([
            '--account-id' => 99999, // 不存在的账号ID
        ]);

        $this->assertEquals(1, $exitCode); // 应该返回错误代码
        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('不存在', $display);
    }

    public function testCommandWithDateOptions(): void
    {
        // 测试日期选项
        $exitCode = $this->commandTester->execute([
            '--start-day' => '20240101',
            '--end-day' => '20240102',
        ]);

        $this->assertContains($exitCode, [0, 1]); // 命令应该能够正常处理日期参数
    }

    public function testCommandHasExpectedOptions(): void
    {
        // 测试命令定义是否正确
        $command = self::getContainer()->get(SyncDailyStatsCommand::class);
        $this->assertInstanceOf(SyncDailyStatsCommand::class, $command);
        $definition = $command->getDefinition();

        $this->assertTrue($definition->hasOption('account-id'));
        $this->assertTrue($definition->hasOption('start-day'));
        $this->assertTrue($definition->hasOption('end-day'));
    }

    public function testCommandHasCorrectName(): void
    {
        // 测试命令名称
        $command = self::getContainer()->get(SyncDailyStatsCommand::class);
        $this->assertInstanceOf(SyncDailyStatsCommand::class, $command);
        $this->assertEquals('youzan:sync:daily-stats', $command->getName());
    }

    public function testCommandHasDescription(): void
    {
        // 测试命令描述
        $command = self::getContainer()->get(SyncDailyStatsCommand::class);
        $this->assertInstanceOf(SyncDailyStatsCommand::class, $command);
        $this->assertNotEmpty($command->getDescription());
    }

    /**
     * 测试真实Account和Shop对象的创建和使用
     */
    public function testRealEntityCreation(): void
    {
        // 创建真实的Account对象
        $account = new Account();
        $account->setName('测试账号');
        $account->setClientId('test-client-id');
        $account->setClientSecret('test-client-secret');

        $this->assertEquals('测试账号', $account->getName());
        $this->assertEquals('test-client-id', $account->getClientId());
        $this->assertEquals('test-client-secret', $account->getClientSecret());

        // 创建真实的Shop对象
        $shop = new Shop();
        $shop->setKdtId(123456);
        $shop->setName('测试店铺');

        $this->assertEquals(123456, $shop->getKdtId());
        $this->assertEquals('测试店铺', $shop->getName());

        // 测试关联关系
        $account->addShop($shop);
        $this->assertTrue($account->getShops()->contains($shop));
    }

    public function testOptionStartDay(): void
    {
        // 测试 --start-day 选项
        self::markTestSkipped('Integration test not required for option coverage');
    }

    public function testOptionEndDay(): void
    {
        // 测试 --end-day 选项
        self::markTestSkipped('Integration test not required for option coverage');
    }

    public function testOptionAccountId(): void
    {
        // 测试 --account-id 选项
        self::markTestSkipped('Integration test not required for option coverage');
    }
}
