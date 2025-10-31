<?php

declare(strict_types=1);

namespace YouzanApiDataBundle\Service;

use EasyCorp\Bundle\EasyAdminBundle\Config\Menu\CrudMenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Menu\SectionMenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use YouzanApiDataBundle\Controller\Admin\DailyStatCrudController;

/**
 * 有赞API数据模块后台菜单服务
 */
class AdminMenu implements MenuProviderInterface
{
    /**
     * 实现调用接口方法
     */
    public function __invoke(ItemInterface $item): void
    {
        // 实现MenuProviderInterface所需的方法
        // 这个接口用于Knp Menu集成，在这里暂时留空
    }

    /**
     * 获取菜单项
     *
     * @return array<int, CrudMenuItem|SectionMenuItem>
     */
    public function getMenuItems(): array
    {
        return [
            MenuItem::section('有赞数据管理', 'fas fa-chart-bar'),
            MenuItem::linkToCrud('每日统计数据', 'fas fa-calendar-day', DailyStatCrudController::class),
        ];
    }
}
