<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Serializer;

use ArrayObject;
use PERSPEQTIVE\SuluSnippetManagerBundle\Security\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Security\Authorization\SecurityCondition;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use function array_merge;
use function is_array;

class SnippetAreaNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly SecurityCheckerInterface $securityChecker,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
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

        return isset($context['sulu_admin_snippet_list']) === true
            && $context['sulu_admin_snippet_list'] === true
            && is_array($data) === true
            && isset($data['_embedded']) === true;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => false,
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    private function modifyObject(array $context, mixed $object): mixed
    {
        if (
            isset($context['sulu_admin_snippet_list']) === false
            || $context['sulu_admin_snippet_list'] === false
            || !is_array($object)
        ) {
            return $object;
        }

        if (!isset($object['_embedded']) || !is_array($object['_embedded']) || !isset($object['_embedded']['snippet_areas'])) {
            return $object;
        }

        /** @var array{_embedded: array{snippet_areas: array<int, array{templateKey: string}>}} $object */
        $newAreas = [];
        /** @var array<string, string> $area */
        foreach ($object['_embedded']['snippet_areas'] as $area) {
            $templateKey = $area['templateKey'];
            if ($this->securityChecker->hasPermission(
                new SecurityCondition('snippet_manager.' . $templateKey . '_' . PermissionTypes::CONTEXT_DEFAULT_SNIPPETS),
                PermissionTypes::EDIT,
            )) {
                $newAreas[] = $area;
            }
        }
        $object['_embedded']['snippet_areas'] = $newAreas;

        return $object;
    }
}
