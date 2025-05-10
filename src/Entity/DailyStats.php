<?php

namespace YouzanApiDataBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;
use YouzanApiBundle\Entity\Account;
use YouzanApiBundle\Entity\Shop;
use YouzanApiDataBundle\Repository\DailyStatsRepository;

/**
 * 有赞每日统计数据实体
 */
#[ORM\Entity(repositoryClass: DailyStatsRepository::class)]
#[ORM\Table(name: 'ims_youzan_daily_stats', options: ['comment' => '有赞每日统计数据表'])]
#[ORM\UniqueConstraint(name: 'uk_account_shop_day', columns: ['account_id', 'shop_id', 'current_day'])]
class DailyStats
{
    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Account $account;

    #[ORM\ManyToOne(targetEntity: Shop::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Shop $shop;

    #[ORM\Column(type: 'integer', options: ['comment' => '统计日期，格式：YYYYMMDD'])]
    private int $currentDay;

    #[ORM\Column(type: 'integer', options: ['comment' => '访客数'])]
    private int $uv = 0;

    #[ORM\Column(type: 'integer', options: ['comment' => '浏览量'])]
    private int $pv = 0;

    #[ORM\Column(type: 'integer', options: ['comment' => '加购人数'])]
    private int $addCartUv = 0;

    #[ORM\Column(type: 'integer', options: ['comment' => '支付订单数'])]
    private int $paidOrderCnt = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, options: ['comment' => '支付金额，单位：元'])]
    private string $paidOrderAmt = '0.00';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, options: ['comment' => '不含退款的支付金额，单位：元'])]
    private string $excludeCashbackRefundedAmt = '0.00';

    #[Filterable]
    #[IndexColumn]
    #[ListColumn(order: 98, sorter: true)]
    #[ExportColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ListColumn(order: 99, sorter: true)]
    #[Filterable]
    #[ExportColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    private ?\DateTimeInterface $updateTime = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): self
    {
        $this->account = $account;
        return $this;
    }

    public function getShop(): Shop
    {
        return $this->shop;
    }

    public function setShop(Shop $shop): self
    {
        $this->shop = $shop;
        return $this;
    }

    public function getCurrentDay(): int
    {
        return $this->currentDay;
    }

    public function setCurrentDay(int $currentDay): self
    {
        $this->currentDay = $currentDay;
        return $this;
    }

    public function getUv(): int
    {
        return $this->uv;
    }

    public function setUv(int $uv): self
    {
        $this->uv = $uv;
        return $this;
    }

    public function getPv(): int
    {
        return $this->pv;
    }

    public function setPv(int $pv): self
    {
        $this->pv = $pv;
        return $this;
    }

    public function getAddCartUv(): int
    {
        return $this->addCartUv;
    }

    public function setAddCartUv(int $addCartUv): self
    {
        $this->addCartUv = $addCartUv;
        return $this;
    }

    public function getPaidOrderCnt(): int
    {
        return $this->paidOrderCnt;
    }

    public function setPaidOrderCnt(int $paidOrderCnt): self
    {
        $this->paidOrderCnt = $paidOrderCnt;
        return $this;
    }

    public function getPaidOrderAmt(): string
    {
        return $this->paidOrderAmt;
    }

    public function setPaidOrderAmt(string $paidOrderAmt): self
    {
        $this->paidOrderAmt = $paidOrderAmt;
        return $this;
    }

    public function getExcludeCashbackRefundedAmt(): string
    {
        return $this->excludeCashbackRefundedAmt;
    }

    public function setExcludeCashbackRefundedAmt(string $excludeCashbackRefundedAmt): self
    {
        $this->excludeCashbackRefundedAmt = $excludeCashbackRefundedAmt;
        return $this;
    }

    public function setCreateTime(?\DateTimeInterface $createdAt): void
    {
        $this->createTime = $createdAt;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): void
    {
        $this->updateTime = $updateTime;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }
}
