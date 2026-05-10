<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Serializer;

use PERSPEQTIVE\SuluSnippetManagerBundle\Security\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Security\Authorization\SecurityCondition;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class SnippetAreaNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private SecurityCheckerInterface $securityChecker,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->modifyObject($context, $data);

        return $this->normalizer->normalize($data, $format, array_merge($context, [__CLASS__ => true]));
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (isset($context[__CLASS__]) === true) {
            return false;
        }

        return isset($context['sulu_admin_snippet_list']) === true &&
            $context['sulu_admin_snippet_list'] === true &&
            is_array($data) === true &&
            isset($data['_embedded']) === true;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => false,
        ];
    }

    private function modifyObject(array $context, mixed $object): mixed
    {
        if (
            isset($context['sulu_admin_snippet_list']) === false ||
            $context['sulu_admin_snippet_list'] === false ||
            is_array($object) === false ||
            isset($object['_embedded']['snippet_areas']) === false
        ) {
            return $object;
        }

        $newAreas = [];
        foreach ($object['_embedded']['snippet_areas'] as $area) {
            $templateKey = $area['templateKey'];
            if ($this->securityChecker->hasPermission(
                new SecurityCondition('snippet_manager.' .$templateKey .'_' . PermissionTypes::CONTEXT_DEFAULT_SNIPPETS),
                PermissionTypes::EDIT
            )) {
                $newAreas[] = $area;
            }
        }
        $object['_embedded']['snippet_areas'] = $newAreas;

        return $object;
    }
}
