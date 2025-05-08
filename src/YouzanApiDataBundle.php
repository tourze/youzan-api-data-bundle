<?php

namespace YouzanApiDataBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

#[AsPermission(title: '有赞数据接口模块')]
class YouzanApiDataBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            \YouzanApiBundle\YouzanApiBundle::class => ['all' => true],
            \AppBundle\AppBundle::class => ['all' => true],
        ];
    }
} 