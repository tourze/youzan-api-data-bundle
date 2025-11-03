<?php

declare(strict_types=1);

namespace YouzanApiDataBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use YouzanApiBundle\YouzanApiBundle;
use Tourze\EasyAdminMenuBundle\EasyAdminMenuBundle;

class YouzanApiDataBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            YouzanApiBundle::class => ['all' => true],
            DoctrineBundle::class => ['all' => true],
            EasyAdminMenuBundle::class => ['all' => true],
        ];
    }
}
