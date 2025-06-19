<?php

namespace YouzanApiDataBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use YouzanApiBundle\Entity\Account;
use YouzanApiBundle\Repository\AccountRepository;
use YouzanApiBundle\Service\YouzanClientService;
use YouzanApiDataBundle\Entity\DailyStats;
use YouzanApiDataBundle\Repository\DailyStatsRepository;

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
        private readonly DailyStatsRepository $statsRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('start-day', null, InputOption::VALUE_OPTIONAL, '开始日期（YYYYMMDD）', date('Ymd', strtotime('-1 day')))
            ->addOption('end-day', null, InputOption::VALUE_OPTIONAL, '结束日期（YYYYMMDD）', date('Ymd', strtotime('-1 day')))
            ->addOption('account-id', null, InputOption::VALUE_OPTIONAL, '有赞账号ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startDay = $input->getOption('start-day');
        $endDay = $input->getOption('end-day');
        $accountId = $input->getOption('account-id');

        // 获取需要同步的账号列表
        $accounts = [];
        if ($accountId !== null) {
            $account = $this->accountRepository->find($accountId);
            if (!$account) {
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
            $kdtIds = [];
            $shopMap = [];  // 用于快速查找店铺对象
            foreach ($account->getShops() as $shop) {
                $kdtIds[] = $shop->getKdtId();
                $shopMap[$shop->getKdtId()] = $shop;
            }

            if (empty($kdtIds)) {
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

            if (!isset($response['data']) || !is_array($response['data'])) {
                throw new \RuntimeException('API返回数据格式错误');
            }

            // 处理返回的数据
            foreach ($response['data'] as $item) {
                // 获取对应的店铺对象
                $shop = $shopMap[$item['kdt_id']] ?? null;
                if (!$shop) {
                    $output->writeln(sprintf('<error>找不到店铺 %d</error>', $item['kdt_id']));
                    continue;
                }

                $stats = $this->statsRepository->findByAccountShopAndDay($account, $shop, $item['current_day'])
                    ?? new DailyStats();

                $stats->setAccount($account)
                    ->setShop($shop)
                    ->setCurrentDay($item['current_day'])
                    ->setUv($item['uv'])
                    ->setPv($item['pv'])
                    ->setAddCartUv($item['add_cart_uv'])
                    ->setPaidOrderCnt($item['paid_order_cnt'])
                    ->setPaidOrderAmt($item['paid_order_amt'])
                    ->setExcludeCashbackRefundedAmt($item['exclude_cashback_refunded_amt']);

                $this->entityManager->persist($stats);
            }

            $this->entityManager->flush();
            $output->writeln(sprintf('<info>账号 %s 数据同步完成</info>', $account->getName()));

        } catch (\Throwable $e) {
            $output->writeln(sprintf('<error>账号 %s 数据同步失败: %s</error>', $account->getName(), $e->getMessage()));
        }
    }
}
