<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\EventListener;

use PERSPEQTIVE\SuluSnippetManagerBundle\Security\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use function array_values;
use function in_array;
use function json_decode;
use function json_encode;
use function str_contains;

readonly class SnippetAreaResponseListener
{
    public function __construct(
        private SecurityCheckerInterface $securityChecker,
        private ConfiguredSnippetTypesProviderInterface $configuredSnippetTypesProvider,
    ) {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($this->shouldHandle($event) === false) {
            return;
        }

        $response = $event->getResponse();
        /**
         * @var array{
         *     _embedded: array{
         *         areas: list<array{
         *             key: string,
         *             template: string,
         *             title: string,
         *             defaultUuid: string|null,
         *             defaultTitle: string|null,
         *             valid: bool
         *         }>
         *     },
         *     total: int
         * } $data */
        $data = json_decode((string) $response->getContent(), true);

        $data['_embedded']['areas'] = $this->rebuildAreas($data['_embedded']['areas']);
        $content = (string) json_encode($data);
        $response->setContent($content);
    }

    private function shouldHandle(ResponseEvent $event): bool
    {
        $request = $event->getRequest();
        if ($request->attributes->get('_route') !== 'sulu_snippet.get_snippet-areas') {
            return false;
        }

        $response = $event->getResponse();
        if (!str_contains($response->headers->get('Content-Type', ''), 'application/json')) {
            return false;
        }

        return true;
    }

    /**
     * @param list<array{
     *     key: string,
     *     template: string,
     *     title: string,
     *     defaultUuid: string|null,
     *     defaultTitle: string|null,
     *     valid: bool
     * }> $areas
     *
     * @return list<array{
     *      key: string,
     *      template: string,
     *      title: string,
     *      defaultUuid: string|null,
     *      defaultTitle: string|null,
     *      valid: bool
     *  }>
     */
    private function rebuildAreas(array $areas): array
    {
        $configuredTypes = $this->configuredSnippetTypesProvider->getConfiguredSnippetTypes();
        foreach ($areas as $key => $area) {
            if ($this->areaShouldBeRemoved($area, $configuredTypes) === false) {
                continue;
            }

            unset($areas[$key]);
        }

        return array_values($areas);
    }

    /**
     * @param array{
     *      key: string,
     *      template: string,
     *      title: string,
     *      defaultUuid: string|null,
     *      defaultTitle: string|null,
     *      valid: bool
     *  } $area
     * @param string[] $configuredTypes
     */
    private function areaShouldBeRemoved(array $area, array $configuredTypes): bool
    {
        $type = $area['template'];
        if (in_array($type, $configuredTypes, true) === false) {
            return false;
        }
        try {
            if ($this->securityChecker->checkPermission('snippet_manager.' . $type . '_' . PermissionTypes::CONTEXT_DEFAULT_SNIPPETS, PermissionTypes::EDIT) === true) {
                return false;
            }
        } catch (AccessDeniedException) {
        }

        return true;
    }
}
