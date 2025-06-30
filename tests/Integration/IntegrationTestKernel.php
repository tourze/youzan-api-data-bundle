<?php

namespace YouzanApiDataBundle\Tests\Integration;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use YouzanApiBundle\YouzanApiBundle;
use YouzanApiDataBundle\YouzanApiDataBundle;

/**
 * @internal
 */
class IntegrationTestKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new YouzanApiBundle(),
            new YouzanApiDataBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config.yaml');
        
        // 手动添加 Repository 和 Command 服务
        $loader->load(function (ContainerBuilder $container) {
            $container->autowire('YouzanApiDataBundle\Repository\DailyStatsRepository')
                ->setPublic(true);
                
            $container->autowire('YouzanApiDataBundle\Command\SyncDailyStatsCommand')
                ->setAutoconfigured(true)
                ->setPublic(true);
        });
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/youzan_api_data_bundle/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/youzan_api_data_bundle/logs';
    }
} 