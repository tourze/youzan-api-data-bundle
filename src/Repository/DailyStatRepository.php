<?php

declare(strict_types=1);

namespace YouzanApiDataBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use YouzanApiBundle\Entity\Account;
use YouzanApiBundle\Entity\Shop;
use YouzanApiDataBundle\Entity\DailyStat;
use YouzanApiDataBundle\Exception\DateGenerationException;

/**
 * @extends ServiceEntityRepository<DailyStat>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: DailyStat::class)]
class DailyStatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyStat::class);
    }

    /**
     * 根据账号、店铺和日期查找统计数据
     */
    public function findByAccountShopAndDay(Account $account, Shop $shop, int $currentDay): ?DailyStat
    {
        return $this->findOneBy([
            'account' => $account,
            'shop' => $shop,
            'currentDay' => $currentDay,
        ]);
    }

    /**
     * 获取指定日期范围的统计数据
     *
     * @return array<int, DailyStat>
     */
    public function findByDateRange(Account $account, Shop $shop, int $startDay, int $endDay): array
    {
        /** @var array<int, DailyStat> */
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
            ->getResult()
        ;
    }

    /**
     * 获取最近N天的统计数据
     *
     * @return array<DailyStat>
     */
    public function findRecentDays(Account $account, Shop $shop, int $days): array
    {
        $endDay = (int) date('Ymd');

        $startTimestamp = strtotime("-{$days} days");
        if (false === $startTimestamp) {
            throw new DateGenerationException('Failed to calculate start timestamp');
        }

        $startDay = (int) date('Ymd', $startTimestamp);

        return $this->findByDateRange($account, $shop, $startDay, $endDay);
    }

    /**
     * 获取账号下所有店铺在指定日期的统计数据
     *
     * @return array<DailyStat>
     */
    public function findByAccountAndDay(Account $account, int $currentDay): array
    {
        return $this->findBy([
            'account' => $account,
            'currentDay' => $currentDay,
        ]);
    }

    public function save(DailyStat $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DailyStat $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
