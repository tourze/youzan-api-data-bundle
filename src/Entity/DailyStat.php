<?php

declare(strict_types=1);

namespace YouzanApiDataBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use YouzanApiBundle\Entity\Account;
use YouzanApiBundle\Entity\Shop;
use YouzanApiDataBundle\Repository\DailyStatRepository;

/**
 * 有赞每日统计数据实体
 */
#[ORM\Entity(repositoryClass: DailyStatRepository::class)]
#[ORM\Table(name: 'ims_youzan_daily_stats', options: ['comment' => '有赞每日统计数据表'])]
#[ORM\UniqueConstraint(name: 'uk_account_shop_day', columns: ['account_id', 'shop_id', 'current_day'])]
class DailyStat implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Account::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private Account $account;

    #[ORM\ManyToOne(targetEntity: Shop::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private Shop $shop;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '统计日期，格式：YYYYMMDD'])]
    #[Assert\NotBlank]
    #[Assert\Range(min: 20000101, max: 30001231)]
    private int $currentDay;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '访客数'])]
    #[Assert\GreaterThanOrEqual(value: 0)]
    private int $uv = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '浏览量'])]
    #[Assert\GreaterThanOrEqual(value: 0)]
    private int $pv = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '加购人数'])]
    #[Assert\GreaterThanOrEqual(value: 0)]
    private int $addCartUv = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '支付订单数'])]
    #[Assert\GreaterThanOrEqual(value: 0)]
    private int $paidOrderCnt = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '支付金额，单位：元'])]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d+\.\d{2}$/', message: '金额格式必须为两位小数')]
    #[Assert\Length(max: 10)]
    private string $paidOrderAmt = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '不含退款的支付金额，单位：元'])]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d+\.\d{2}$/', message: '金额格式必须为两位小数')]
    #[Assert\Length(max: 10)]
    private string $excludeCashbackRefundedAmt = '0.00';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getShop(): Shop
    {
        return $this->shop;
    }

    public function setShop(Shop $shop): void
    {
        $this->shop = $shop;
    }

    public function getCurrentDay(): int
    {
        return $this->currentDay;
    }

    public function setCurrentDay(int $currentDay): void
    {
        $this->currentDay = $currentDay;
    }

    public function getUv(): int
    {
        return $this->uv;
    }

    public function setUv(int $uv): void
    {
        $this->uv = $uv;
    }

    public function getPv(): int
    {
        return $this->pv;
    }

    public function setPv(int $pv): void
    {
        $this->pv = $pv;
    }

    public function getAddCartUv(): int
    {
        return $this->addCartUv;
    }

    public function setAddCartUv(int $addCartUv): void
    {
        $this->addCartUv = $addCartUv;
    }

    public function getPaidOrderCnt(): int
    {
        return $this->paidOrderCnt;
    }

    public function setPaidOrderCnt(int $paidOrderCnt): void
    {
        $this->paidOrderCnt = $paidOrderCnt;
    }

    public function getPaidOrderAmt(): string
    {
        return $this->paidOrderAmt;
    }

    public function setPaidOrderAmt(string $paidOrderAmt): void
    {
        $this->paidOrderAmt = $paidOrderAmt;
    }

    public function getExcludeCashbackRefundedAmt(): string
    {
        return $this->excludeCashbackRefundedAmt;
    }

    public function setExcludeCashbackRefundedAmt(string $excludeCashbackRefundedAmt): void
    {
        $this->excludeCashbackRefundedAmt = $excludeCashbackRefundedAmt;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s - %s (%d)',
            $this->account->getName(),
            $this->shop->getName(),
            $this->currentDay
        );
    }
}
