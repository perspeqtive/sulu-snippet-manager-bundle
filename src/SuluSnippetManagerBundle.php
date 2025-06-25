<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle;

use PERSPEQTIVE\SuluSnippetManagerBundle\DependencyInjection\RegisterManagersCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SuluSnippetManagerBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new RegisterManagersCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 100);
    }
}