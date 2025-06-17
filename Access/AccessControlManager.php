<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Access;

use Sulu\Bundle\SnippetBundle\Snippet\SnippetRepository;
use Sulu\Component\Security\Authorization\AccessControl\AccessControlManagerInterface;
use Sulu\Component\Security\Authorization\SecurityCondition;
use Sulu\Component\Webspace\Analyzer\Attributes\RequestAttributes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use function array_pop;
use function count;
use function in_array;
use function is_string;
use function str_contains;

class AccessControlManager implements AccessControlManagerInterface
{
    private ?SnippetRepository $snippetRepository = null;

    public function __construct(
        private readonly AccessControlManagerInterface $accessControlManager,
        private readonly RequestStack $requestStack,
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

        return $this->reconstructSnippetTypeFromRequest($request);
    }

    private function reconstructSnippetTypeFromRequest(Request $request): string
    {
        if (
            in_array($request->attributes->get('_route'), ['sulu_snippet.get_snippet', 'sulu_snippet.put_snippet'], true) === false
            || $this->snippetRepository instanceof SnippetRepository === false) {
            return '';
        }

        /** @var ?RequestAttributes $suluAttributes */
        $suluAttributes = $request->attributes->get('_sulu');
        /** @var ?string $locale */
        $locale = $suluAttributes?->getAttribute('locale');
        if (empty($locale) === true) {
            return '';
        }

        $id = $request->attributes->get('id', 'none');
        $snippets = $this->snippetRepository->getSnippetsByUuids([$id], $locale);
        if (count($snippets) !== 1) {
            return '';
        }

        $snippet = array_pop($snippets);

        return (string) $snippet->getStructureType();
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

    public function setPermissions($type, $identifier, $permissions, bool $inherit = false): void
    {
        $this->accessControlManager->setPermissions($type, $identifier, $permissions, $inherit);
    }

    public function getPermissions($type, $identifier): array
    {
        return $this->accessControlManager->getPermissions($type, $identifier);
    }

    public function setSnippetRepository(SnippetRepository $snippetRepository): void
    {
        $this->snippetRepository = $snippetRepository;
    }
}
