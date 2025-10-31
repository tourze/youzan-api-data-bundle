<?php

declare(strict_types=1);

namespace YouzanApiDataBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use YouzanApiDataBundle\Entity\DailyStat;

/**
 * 有赞每日统计数据后台管理控制器
 */
#[AdminCrud(routePath: '/youzan-data/daily-stat', routeName: 'youzan_data_daily_stat')]
final class DailyStatCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DailyStat::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')
                ->hideOnForm(),

            AssociationField::new('account', '账户')
                ->setRequired(true)
                ->setCssClass('col-md-6'),

            AssociationField::new('shop', '店铺')
                ->setRequired(true)
                ->setCssClass('col-md-6'),

            IntegerField::new('currentDay', '统计日期')
                ->setHelp('格式：YYYYMMDD，如：20240101')
                ->setRequired(true)
                ->setCssClass('col-md-6'),

            IntegerField::new('uv', '访客数')
                ->setHelp('当日独立访客数量')
                ->setCssClass('col-md-6'),

            IntegerField::new('pv', '浏览量')
                ->setHelp('当日页面浏览量')
                ->setCssClass('col-md-6'),

            IntegerField::new('addCartUv', '加购人数')
                ->setHelp('当日加入购物车的人数')
                ->setCssClass('col-md-6'),

            IntegerField::new('paidOrderCnt', '支付订单数')
                ->setHelp('当日成功支付的订单数量')
                ->setCssClass('col-md-6'),

            MoneyField::new('paidOrderAmt', '支付金额')
                ->setHelp('当日总支付金额，单位：元')
                ->setCurrency('CNY')
                ->setStoredAsCents(false)
                ->setCssClass('col-md-6'),

            MoneyField::new('excludeCashbackRefundedAmt', '不含退款支付金额')
                ->setHelp('不含退款的支付金额，单位：元')
                ->setCurrency('CNY')
                ->setStoredAsCents(false)
                ->setCssClass('col-md-6'),

            DateTimeField::new('createTime', '创建时间')
                ->hideOnForm()
                ->setCssClass('col-md-6'),

            DateTimeField::new('updateTime', '更新时间')
                ->hideOnForm()
                ->setCssClass('col-md-6'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('每日统计')
            ->setEntityLabelInPlural('每日统计数据')
            ->setSearchFields(['account.name', 'shop.name', 'currentDay'])
            ->setDefaultSort(['currentDay' => 'DESC'])
            ->setPaginatorPageSize(50)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('account', '账户'))
            ->add(EntityFilter::new('shop', '店铺'))
            ->add(NumericFilter::new('currentDay', '统计日期'))
            ->add(NumericFilter::new('uv', '访客数'))
            ->add(NumericFilter::new('pv', '浏览量'))
            ->add(NumericFilter::new('addCartUv', '加购人数'))
            ->add(NumericFilter::new('paidOrderCnt', '支付订单数'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }
}
