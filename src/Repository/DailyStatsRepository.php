<?php

namespace YouzanApiDataBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use YouzanApiBundle\Entity\Account;
use YouzanApiBundle\Entity\Shop;
use YouzanApiDataBundle\Entity\DailyStats;

/**
 * @method DailyStats|null find($id, $lockMode = null, $lockVersion = null)
 * @method DailyStats|null findOneBy(array $criteria, array $orderBy = null)
 * @method DailyStats[] findAll()
 * @method DailyStats[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DailyStatsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyStats::class);
    }

    /**
     * 根据账号、店铺和日期查找统计数据
     */
    public function findByAccountShopAndDay(Account $account, Shop $shop, int $currentDay): ?DailyStats
    {
        return $this->findOneBy([
            'account' => $account,
            'shop' => $shop,
            'currentDay' => $currentDay
        ]);
    }

    /**
     * 获取指定日期范围的统计数据
     */
    public function findByDateRange(Account $account, Shop $shop, int $startDay, int $endDay): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.account = :account')
            ->andWhere('s.shop = :shop')
            ->andWhere('s.currentDay >= :startDay')
            ->andWhere('s.currentDay <= :endDay')
            ->setParameter('account', $account)
            ->setParameter('shop', $shop)
            ->setParameter('startDay', $startDay)
            ->setParameter('endDay', $endDay)
            ->orderBy('s.currentDay', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取最近N天的统计数据
     */
    public function findRecentDays(Account $account, Shop $shop, int $days): array
    {
        $endDay = (int)date('Ymd');
        $startDay = (int)date('Ymd', strtotime("-{$days} days"));
        
        return $this->findByDateRange($account, $shop, $startDay, $endDay);
    }

    /**
     * 获取账号下所有店铺在指定日期的统计数据
     */
    public function findByAccountAndDay(Account $account, int $currentDay): array
    {
        return $this->findBy([
            'account' => $account,
            'currentDay' => $currentDay
        ]);
    }
} 