<?php

declare(strict_types=1);

namespace YouzanApiDataBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use YouzanApiBundle\Entity\Account;
use YouzanApiBundle\Entity\Shop;
use YouzanApiBundle\Repository\AccountRepository;
use YouzanApiBundle\Service\YouzanClientService;
use YouzanApiDataBundle\Entity\DailyStat;
use YouzanApiDataBundle\Exception\ApiResponseException;
use YouzanApiDataBundle\Repository\DailyStatRepository;

#[AsCommand(
    name: self::NAME,
    description: '同步有赞店铺每日统计数据',
)]
class SyncDailyStatsCommand extends Command
{
    protected const NAME = 'youzan:sync:daily-stats';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly YouzanClientService $clientService,
        private readonly AccountRepository $accountRepository,
        private readonly DailyStatRepository $statsRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('start-day', null, InputOption::VALUE_OPTIONAL, '开始日期（YYYYMMDD）', date('Ymd', strtotime('-1 day')))
            ->addOption('end-day', null, InputOption::VALUE_OPTIONAL, '结束日期（YYYYMMDD）', date('Ymd', strtotime('-1 day')))
            ->addOption('account-id', null, InputOption::VALUE_OPTIONAL, '有赞账号ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startDay = $this->validateDateOption($input->getOption('start-day'));
        $endDay = $this->validateDateOption($input->getOption('end-day'));
        $accountId = $this->validateAccountIdOption($input->getOption('account-id'));

        // 获取需要同步的账号列表
        $accounts = [];
        if (null !== $accountId) {
            $account = $this->accountRepository->find($accountId);
            if (null === $account) {
                $output->writeln(sprintf('<error>账号 %s 不存在</error>', $accountId));

                return Command::FAILURE;
            }
            $accounts[] = $account;
        } else {
            $accounts = $this->accountRepository->findAll();
        }

        foreach ($accounts as $account) {
            $this->syncAccountStats($account, $startDay, $endDay, $output);
        }

        return Command::SUCCESS;
    }

    private function syncAccountStats(Account $account, string $startDay, string $endDay, OutputInterface $output): void
    {
        $output->writeln(sprintf('正在同步账号 %s 的数据...', $account->getName()));

        try {
            $client = $this->clientService->getClient($account);

            // 获取账号关联的所有店铺ID
            /** @var array<int> $kdtIds */
            $kdtIds = [];
            /** @var array<int, Shop> $shopMap 用于快速查找店铺对象 */
            $shopMap = [];
            foreach ($account->getShops() as $shop) {
                $kdtIds[] = $shop->getKdtId();
                $shopMap[$shop->getKdtId()] = $shop;
            }

            if ([] === $kdtIds) {
                $output->writeln(sprintf('<comment>账号 %s 未关联任何店铺</comment>', $account->getName()));

                return;
            }

            // 调用有赞数据中心API
            $response = $client->post('youzan.datacenter.datastandard.team', '1.0.0', [
                'start_day' => $startDay,
                'end_day' => $endDay,
                'date_type' => '1',
                'shop_role' => '1',
                'kdt_list[]' => sprintf('[%s]', implode(',', $kdtIds)),
            ]);

            if (!is_array($response) || !isset($response['data']) || !is_array($response['data'])) {
                throw new ApiResponseException('API返回数据格式错误');
            }

            // 处理返回的数据
            foreach ($response['data'] as $item) {
                if (!is_array($item)) {
                    $output->writeln('<error>API返回数据项格式错误</error>');
                    continue;
                }

                try {
                    /** @var array<string, mixed> $item */
                    $validatedItem = $this->validateApiResponseItem($item);

                    // 获取对应的店铺对象
                    $shop = $shopMap[$validatedItem['kdt_id']] ?? null;
                    if (null === $shop) {
                        $output->writeln(sprintf('<error>找不到店铺 %d</error>', $validatedItem['kdt_id']));
                        continue;
                    }

                    $stats = $this->statsRepository->findByAccountShopAndDay($account, $shop, (int) $validatedItem['current_day'])
                        ?? new DailyStat();

                    $stats->setAccount($account);
                    $stats->setShop($shop);
                    $stats->setCurrentDay((int) $validatedItem['current_day']);
                    $stats->setUv((int) $validatedItem['uv']);
                    $stats->setPv((int) $validatedItem['pv']);
                    $stats->setAddCartUv((int) $validatedItem['add_cart_uv']);
                    $stats->setPaidOrderCnt((int) $validatedItem['paid_order_cnt']);
                    $stats->setPaidOrderAmt((string) $validatedItem['paid_order_amt']);
                    $stats->setExcludeCashbackRefundedAmt((string) $validatedItem['exclude_cashback_refunded_amt']);

                    $this->entityManager->persist($stats);
                } catch (\InvalidArgumentException $e) {
                    $output->writeln(sprintf('<error>数据验证失败: %s</error>', $e->getMessage()));
                    continue;
                }
            }

            $this->entityManager->flush();
            $output->writeln(sprintf('<info>账号 %s 数据同步完成</info>', $account->getName()));
        } catch (\Throwable $e) {
            $output->writeln(sprintf('<error>账号 %s 数据同步失败: %s</error>', $account->getName(), $e->getMessage()));
        }
    }

    /**
     * 验证日期选项参数
     */
    private function validateDateOption(mixed $option): string
    {
        if (!is_string($option) || '' === $option) {
            throw new \InvalidArgumentException('日期参数必须是非空字符串');
        }

        if (1 !== preg_match('/^\d{8}$/', $option)) {
            throw new \InvalidArgumentException('日期格式必须为YYYYMMDD');
        }

        return $option;
    }

    /**
     * 验证账号ID选项参数
     */
    private function validateAccountIdOption(mixed $option): ?int
    {
        if (null === $option) {
            return null;
        }

        if (is_string($option) && ctype_digit($option)) {
            return (int) $option;
        }

        if (is_int($option)) {
            return $option;
        }

        throw new \InvalidArgumentException('账号ID必须是数字');
    }

    /**
     * 验证API响应数据项
     *
     * @param array<string, mixed> $item
     * @return array<string, int|string>
     */
    private function validateApiResponseItem(array $item): array
    {
        $requiredFields = [
            'kdt_id' => 'int',
            'current_day' => 'int',
            'uv' => 'int',
            'pv' => 'int',
            'add_cart_uv' => 'int',
            'paid_order_cnt' => 'int',
            'paid_order_amt' => 'string',
            'exclude_cashback_refunded_amt' => 'string',
        ];

        $validatedItem = [];

        foreach ($requiredFields as $field => $expectedType) {
            $validatedItem[$field] = $this->validateFieldValue($item, $field, $expectedType);
        }

        // 验证金额格式
        return $this->validateAmountFields($validatedItem);
    }

    /**
     * 验证单个字段的值
     *
     * @param array<string, mixed> $item
     */
    private function validateFieldValue(array $item, string $field, string $expectedType): int|string
    {
        if (!array_key_exists($field, $item)) {
            throw new \InvalidArgumentException(sprintf('缺少必需字段: %s', $field));
        }

        $value = $item[$field];

        if ('int' === $expectedType) {
            if (!is_int($value) && !is_numeric($value)) {
                throw new \InvalidArgumentException(sprintf('字段 %s 必须是数字', $field));
            }

            return (int) $value;
        }

        if ('string' === $expectedType) {
            if (!is_string($value) && !is_numeric($value)) {
                throw new \InvalidArgumentException(sprintf('字段 %s 必须是字符串或数字', $field));
            }

            return (string) $value;
        }

        throw new \InvalidArgumentException(sprintf('不支持的字段类型: %s', $expectedType));
    }

    /**
     * 验证并格式化金额字段
     *
     * @param array<string, int|string> $validatedItem
     * @return array<string, int|string>
     */
    private function validateAmountFields(array $validatedItem): array
    {
        foreach (['paid_order_amt', 'exclude_cashback_refunded_amt'] as $amountField) {
            $amount = (string) $validatedItem[$amountField];
            if (1 !== preg_match('/^\d+(\.\d{1,2})?$/', $amount)) {
                // 如果不是标准格式，尝试格式化为两位小数
                $validatedItem[$amountField] = number_format((float) $amount, 2, '.', '');
            }
        }

        return $validatedItem;
    }
}
