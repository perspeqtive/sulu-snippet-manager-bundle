<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\DefinitionBuilder;

use Symfony\Component\DependencyInjection\Definition as BaseDefinition;

class Definition extends BaseDefinition
{

    public static function fromDefinition(BaseDefinition $definition): static {

        $newDefinition = new static($definition->getClass(), $definition->getArguments());
        $newDefinition->setTags($definition->getTags());
        $newDefinition->setBindings($definition->getBindings());
        return $newDefinition;

    }

}