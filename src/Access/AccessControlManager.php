<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Access;

use Exception;
use Sulu\Component\Security\Authorization\AccessControl\AccessControlManagerInterface;
use Sulu\Component\Security\Authorization\SecurityCondition;
use Sulu\Component\Webspace\Analyzer\Attributes\RequestAttributes;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Infrastructure\Doctrine\DimensionContentQueryEnhancer;
use Sulu\Snippet\Domain\Model\SnippetDimensionContentInterface;
use Sulu\Snippet\Domain\Repository\SnippetRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use function array_map;
use function is_string;
use function str_contains;
use function str_starts_with;

readonly class AccessControlManager implements AccessControlManagerInterface
{
    public function __construct(
        private AccessControlManagerInterface $accessControlManager,
        private RequestStack $requestStack,
        private SnippetRepositoryInterface $snippetRepository,
        private array $snippetAreas,
    ) {
    }

    public function getUserPermissions(SecurityCondition $securityCondition, $user): array
    {
        /** @var array<string,bool> $parentPermissions */
        $parentPermissions = $this->accessControlManager->getUserPermissions($securityCondition, $user);
        if ($this->shouldBeHandled($securityCondition) === false) {
            return $parentPermissions;
        }

        $types = $this->getRequestedTypes($securityCondition);
        if ($types === []) {
            return $parentPermissions;
        }

        /** @var array<string, bool> $permissions */
        $permissions = array_map(static fn () => true, $parentPermissions);
        foreach ($types as $type) {
            $subSecurityCondition = $this->buildSecurityCondition($type, $securityCondition);
            /** @var array<string, bool> $subResult */
            $subResult = $this->accessControlManager->getUserPermissions($subSecurityCondition, $user);
            $permissions = $this->mergeDetailPermissions($permissions, $subResult);
        }

        /** @var array<string,bool> $subResult */
        return $this->mergePermissions($parentPermissions, $subResult);
    }

    private function shouldBeHandled(SecurityCondition $securityCondition): bool
    {
        if ($securityCondition->getSecurityContext() !== 'sulu.snippet.snippets') {
            return false;
        }

        $objectType = $securityCondition->getObjectType();
        if (empty($objectType) === false) {
            return false;
        }

        return true;
    }

    /**
     * @return string[]
     */
    private function getRequestedTypes(SecurityCondition $securityCondition): array
    {
        $securityType = $securityCondition->getObjectType();
        if (empty($securityType) === false) {
            return [$securityType];
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request instanceof Request === false) {
            return [];
        }

        /** @var ?string $type */
        $type = $request->query->get('types');
        if (is_string($type) === true && str_contains($type, ',') === false) {
            return [$type];
        }

        $area = $request->query->get('areas');
        if (is_string($area) === true && str_contains($area, ',') === false && isset($this->snippetAreas[$area]['template']) === true) {
            return [$this->snippetAreas[$area]['template']];
        }

        /** @var ?string $type */
        $type = $request->request->get('template');
        if (is_string($type) === true && str_contains($type, ',') === false) {
            return [$type];
        }

        return $this->reconstructSnippetTypeFromRequest($request);
    }

    /**
     * @return string[]
     */
    private function reconstructSnippetTypeFromRequest(Request $request): array
    {
        /** @var ?string $route */
        $route = $request->attributes->get('_route');
        if (
            $route === null
            || str_starts_with($route, 'sulu_snippet.') === false
        ) {
            return [];
        }

        $templateKey = $this->getTemplateKeyFromSnippet($request);
        if ($templateKey === null) {
            return [];
        }

        return [$templateKey];
    }

    private function buildSecurityCondition(string $type, SecurityCondition $securityCondition): SecurityCondition
    {
        return new SecurityCondition(
            'snippet_manager.' . $type,
            $securityCondition->getLocale(),
            $securityCondition->getObjectType(),
            $securityCondition->getObjectId(),
            $securityCondition->getSystem(),
        );
    }

    /**
     * @param array<string,bool> $parentPermissions
     * @param array<string,bool> $subResult
     *
     * @return array<string,bool>
     */
    private function mergePermissions(array $parentPermissions, array $subResult): array
    {
        foreach ($parentPermissions as $key => $value) {
            $parentPermissions[$key] = $value || ($subResult[$key] ?? false);
        }

        return $parentPermissions;
    }

    /**
     * @param array<string,bool> $basePermission
     * @param array<string,bool> $subResult
     *
     * @return array<string,bool>
     */
    private function mergeDetailPermissions(array $basePermission, array $subResult): array
    {
        foreach ($basePermission as $key => $value) {
            $basePermission[$key] = $value && ($subResult[$key] ?? false);
        }

        return $basePermission;
    }

    private function getTemplateKeyFromSnippet(Request $request): ?string
    {
        /** @var ?RequestAttributes $suluAttributes */
        $suluAttributes = $request->attributes->get('_sulu');
        /** @var ?string $locale */
        $locale = $suluAttributes?->getAttribute('locale');
        /** @var string $id */
        $id = $request->attributes->get('id', 'none');

        if ($id === null || $id === 'none' || $locale === null) {
            return null;
        }

        try {
            $snippet = $this->snippetRepository->findOneBy(
                [
                    'uuid' => $id,
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_DRAFT,
                    'version' => DimensionContentInterface::CURRENT_VERSION,
                ],
                [
                    SnippetRepositoryInterface::SELECT_SNIPPET_CONTENT => [
                        'selects' => [DimensionContentQueryEnhancer::GROUP_SELECT_CONTENT_ADMIN => true],
                        'dimensionAttributes' => [
                            'locale' => $locale,
                            'stage' => [DimensionContentInterface::STAGE_DRAFT],
                        ],
                    ],
                ],
            );
        } catch (Exception) {
            return null;
        }

        /** @var SnippetDimensionContentInterface $dimensionContent */
        foreach ($snippet?->getDimensionContents() as $dimensionContent) {
            if ($dimensionContent->getTemplateKey() === null) {
                continue;
            }

            return $dimensionContent->getTemplateKey();
        }

        return null;
    }

    public function getUserPermissionByArray($locale, $securityContext, $objectPermissionsByRole, $user, $system = null): array
    {
        return $this->accessControlManager->getUserPermissionByArray($locale, $securityContext, $objectPermissionsByRole, $user, $system);
    }

    /**
     * @phpstan-ignore missingType.return
     */
    public function setPermissions($type, $identifier, $permissions, bool $inherit = false)
    {
        return $this->accessControlManager->setPermissions($type, $identifier, $permissions, $inherit);
    }

    public function getPermissions($type, $identifier): array
    {
        return $this->accessControlManager->getPermissions($type, $identifier);
    }
}
