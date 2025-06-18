<?php

namespace YouzanApiDataBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

class YouzanApiDataBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            \YouzanApiBundle\YouzanApiBundle::class => ['all' => true],
        ];
    }
}
