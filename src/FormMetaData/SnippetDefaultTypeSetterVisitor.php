<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\FormMetaData;

use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\TypedFormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\TypedFormMetadataVisitorInterface;

use function is_string;

class SnippetDefaultTypeSetterVisitor implements TypedFormMetadataVisitorInterface
{
    public function visitTypedFormMetadata(TypedFormMetadata $formMetadata, string $key, string $locale, array $metadataOptions = []): void
    {
        if (isset($metadataOptions['overwriteDefaultType']) === false || is_string($metadataOptions['overwriteDefaultType']) === false) {
            return;
        }
        $formMetadata->setDefaultType($metadataOptions['overwriteDefaultType']);
    }
}
