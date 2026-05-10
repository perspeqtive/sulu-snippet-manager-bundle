<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Symfony;

use ArrayObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MockNormalizer implements NormalizerInterface
{
    public mixed $dataToNormalize;
    public array $context;

    public function __construct(public array $result = [])
    {
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        $this->dataToNormalize = $data;
        $this->context = $context;

        return $this->result;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return true;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [];
    }
}
