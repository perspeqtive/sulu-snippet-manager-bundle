<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Unit\EventListener;

use PERSPEQTIVE\SuluSnippetManagerBundle\EventListener\SnippetAreaResponseListener;
use PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\MockConfiguredSnippetTypesProvider;
use PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\MockKernel;
use PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu\MockSecurityChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use function json_decode;
use function json_encode;

class SnippetAreaResponseListenerTest extends TestCase
{
    private MockSecurityChecker $securityChecker;
    private MockConfiguredSnippetTypesProvider $configuredSnippetTypesProvider;
    private SnippetAreaResponseListener $listener;

    protected function setUp(): void
    {
        $this->securityChecker = new MockSecurityChecker();
        $this->configuredSnippetTypesProvider = new MockConfiguredSnippetTypesProvider([]);
        $this->listener = new SnippetAreaResponseListener(
            $this->securityChecker,
            $this->configuredSnippetTypesProvider,
        );
    }

    public function testAllEmpty(): void
    {
        $request = new Request(attributes: ['_route' => 'sulu_snippet.get_snippet-areas']);
        $response = new Response((string) json_encode([]));

        $event = new ResponseEvent(
            new MockKernel(),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        );

        $this->listener->onKernelResponse($event);

        self::assertSame([], json_decode((string) $response->getContent()));
    }

    public function testAllEmptyButIsHandled(): void
    {
        $expected = ['_embedded' => ['areas' => []]];

        $responseContent = ['_embedded' => ['areas' => []]];
        $request = new Request(attributes: ['_route' => 'sulu_snippet.get_snippet-areas']);
        $response = new Response((string) json_encode($responseContent), 200, ['Content-Type' => 'application/json']);

        $event = new ResponseEvent(
            new MockKernel(),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        );

        $this->listener->onKernelResponse($event);

        self::assertSame($expected, json_decode((string) $response->getContent(), true));
    }

    public function testAllHandlesConfiguredSnippetTypes(): void
    {
        $expected = [
            '_embedded' => [
                'areas' => [
                    ['template' => 'default'],
                    ['template' => 'shop'],
                ],
            ],
        ];

        $responseContent = ['_embedded' => ['areas' => [
            ['template' => 'default'],
            ['template' => 'settings'],
            ['template' => 'shop'],
        ]]];
        $request = new Request(attributes: ['_route' => 'sulu_snippet.get_snippet-areas']);
        $response = new Response((string) json_encode($responseContent), 200, ['Content-Type' => 'application/json']);
        $this->configuredSnippetTypesProvider->result = ['settings', 'shop'];
        $this->securityChecker->hasPermission = [
            'snippet_manager.settings_default_snippets' => ['edit' => false],
            'snippet_manager.shop_default_snippets' => ['edit' => true],
        ];

        $event = new ResponseEvent(
            new MockKernel(),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        );

        $this->listener->onKernelResponse($event);

        self::assertSame($expected, json_decode((string) $response->getContent(), true));
    }

    public function testAllHandlesConfiguredButWrongRoute(): void
    {
        $expected = [
            '_embedded' => [
                'areas' => [
                    ['template' => 'default'],
                    ['template' => 'settings'],
                    ['template' => 'shop'],
                ],
            ],
        ];

        $responseContent = ['_embedded' => ['areas' => [
            ['template' => 'default'],
            ['template' => 'settings'],
            ['template' => 'shop'],
        ]]];
        $request = new Request(attributes: ['_route' => 'somewhere-else']);
        $response = new Response((string) json_encode($responseContent), 200, ['Content-Type' => 'application/json']);
        $this->configuredSnippetTypesProvider->result = ['settings', 'shop'];
        $this->securityChecker->hasPermission = [
            'snippet_manager.settings_default_snippets' => ['edit' => false],
            'snippet_manager.shop_default_snippets' => ['edit' => true],
        ];

        $event = new ResponseEvent(
            new MockKernel(),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        );

        $this->listener->onKernelResponse($event);

        self::assertSame($expected, json_decode((string) $response->getContent(), true));
    }
}