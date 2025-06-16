<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Application;

use Exception;
use PERSPEQTIVE\SuluSnippetManagerBundle\SuluSnippetManagerBundle;
use Sulu\Bundle\TestBundle\Kernel\SuluTestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class Kernel extends SuluTestKernel
{
    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        /** @var BundleInterface[] $bundles */
        $bundles = parent::registerBundles();
        $bundles[] = new SuluSnippetManagerBundle();

        return $bundles;
    }

    /**
     * @throws Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        parent::registerContainerConfiguration($loader);
        $loader->load(__DIR__ . '/config/config.yaml');
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }
}
