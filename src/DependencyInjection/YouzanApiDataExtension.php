<?php

declare(strict_types=1);

namespace YouzanApiDataBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class YouzanApiDataExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
