<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Access;

use Exception;
use Sulu\Bundle\SnippetBundle\Document\SnippetDocument;
use Sulu\Component\DocumentManager\DocumentManagerInterface;
use Sulu\Component\Security\Authorization\AccessControl\AccessControlManagerInterface;
use Sulu\Component\Security\Authorization\SecurityCondition;
use Sulu\Component\Webspace\Analyzer\Attributes\RequestAttributes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use function is_string;
use function str_contains;
use function str_starts_with;

readonly class AccessControlManager implements AccessControlManagerInterface
{
    public function __construct(
        private AccessControlManagerInterface $accessControlManager,
        private RequestStack $requestStack,
        private DocumentManagerInterface $documentManager,
    ) {
    }

    public function getUserPermissions(SecurityCondition $securityCondition, $user): array
    {
        /** @var array<string,bool> $parentPermissions */
        $parentPermissions = $this->accessControlManager->getUserPermissions($securityCondition, $user);
        if ($this->shouldBeHandled($securityCondition) === false) {
            return $parentPermissions;
        }

        $type = $this->getRequestedType();
        if ($type === '') {
            return $parentPermissions;
        }

        $subSecurityCondition = $this->buildSecurityCondition($type, $securityCondition);
        /** @var array<string,bool> $subResult */
        $subResult = $this->accessControlManager->getUserPermissions($subSecurityCondition, $user);

        return $this->mergePermissions($parentPermissions, $subResult);
    }

    private function shouldBeHandled(SecurityCondition $securityCondition): bool
    {
        if ($securityCondition->getSecurityContext() !== 'sulu.global.snippets') {
            return false;
        }

        $objectType = $securityCondition->getObjectType();
        if (empty($objectType) === false) {
            return false;
        }

        return true;
    }

    private function getRequestedType(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request instanceof Request === false) {
            return '';
        }

        /** @var ?string $type */
        $type = $request->query->get('types');
        if (is_string($type) === true && str_contains($type, ',') === false) {
            return $type;
        }

        /** @var ?string $type */
        $type = $request->request->get('template');
        if (is_string($type) === true && str_contains($type, ',') === false) {
            return $type;
        }

        return $this->reconstructSnippetTypeFromRequest($request);
    }

    private function reconstructSnippetTypeFromRequest(Request $request): string
    {
        /** @var ?string $route */
        $route = $request->attributes->get('_route');
        if (
            $route === null
            || str_starts_with($route, 'sulu_snippet.') === false
        ) {
            return '';
        }

        /** @var ?RequestAttributes $suluAttributes */
        $suluAttributes = $request->attributes->get('_sulu');
        /** @var ?string $locale */
        $locale = $suluAttributes?->getAttribute('locale');
        /** @var string $id */
        $id = $request->attributes->get('id', 'none');

        try {
            /** @var SnippetDocument $snippet */
            $snippet = $this->documentManager->find($id, $locale);

            return (string) $snippet->getStructureType();
        } catch (Exception) {
        }

        return '';
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
