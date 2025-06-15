<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class SuluSnippetManagerExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('sulu_snippet_manager.snippets', $config['navigation']);
    }

}