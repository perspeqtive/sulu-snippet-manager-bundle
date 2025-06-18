<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Unit\Access;

use PERSPEQTIVE\SuluSnippetManagerBundle\Access\AccessControlManager;
use PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu\MockAccessControlManager;
use PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu\MockDocumentManager;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\SnippetBundle\Document\SnippetDocument;
use Sulu\Component\Security\Authorization\SecurityCondition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AccessControlManagerTest extends TestCase
{
    private Request $request;
    private MockAccessControlManager $oldManager;
    private AccessControlManager $manager;
    private MockDocumentManager $documentManager;
    private RequestStack $requestStack;

    protected function setUp(): void
    {
        $this->requestStack = new RequestStack();
        $this->request = new Request();
        $this->requestStack->push($this->request);

        $this->oldManager = new MockAccessControlManager();
        $this->documentManager = new MockDocumentManager();

        $this->manager = new AccessControlManager(
            $this->oldManager,
            $this->requestStack,
            $this->documentManager,
        );
    }

    public function testManagerDecoratesCorrectly(): void
    {
        self::assertSame($this->oldManager->getPermissions('type', 'identifier'), $this->manager->getPermissions('type', 'identifier'));
        self::assertSame($this->oldManager->getUserPermissionByArray('locale', 'context', ['role'], null), $this->manager->getUserPermissionByArray('locale', 'context', ['role'], null));
        self::assertSame($this->oldManager->setPermissions('type', 'identifier', ['permissions'], true), $this->manager->setPermissions('type', 'identifier', ['permissions'], true));
    }

    public function testGetUserPermissionsDoesNotOverwriteUnknownPermission(): void
    {
        $condition = new SecurityCondition('sulu.global.some-permissions');
        $result = $this->manager->getUserPermissions($condition, null);

        self::assertSame([], $result);
    }

    public function testGetUserPermissionsDoesNotOverwriteWhenObjectIdIsSet(): void
    {
        $condition = new SecurityCondition('sulu.global.snippets', 'de', 'snippets', 'aa-bb');
        $result = $this->manager->getUserPermissions($condition, null);

        self::assertSame(['view' => true, 'edit' => false], $result);
    }

    public function testGetUserPermissionsDoesNotOverwriteWhenRequestIsEmpty(): void
    {
        $this->requestStack->pop();
        $condition = new SecurityCondition('sulu.global.snippets');
        $result = $this->manager->getUserPermissions($condition, null);

        self::assertSame(['view' => true, 'edit' => false], $result);
    }

    public function testGetUserPermissionsDoesNotOverwriteWIthEmptyTypes(): void
    {
        $condition = new SecurityCondition('sulu.global.snippets');
        $result = $this->manager->getUserPermissions($condition, null);

        self::assertSame(['view' => true, 'edit' => false], $result);
    }

    public function testGetUserPermissionsDoesNotOverwriteWithNotHandlableTypes(): void
    {
        $this->request->initialize(query: ['types' => '']);
        $condition = new SecurityCondition('sulu.global.snippets');
        $result = $this->manager->getUserPermissions($condition, null);

        self::assertSame(['view' => true, 'edit' => false], $result);

        $this->request->initialize(query: ['types' => 'shop,services']);
        $condition = new SecurityCondition('sulu.global.snippets');
        $result = $this->manager->getUserPermissions($condition, null);

        self::assertSame(['view' => true, 'edit' => false], $result);
    }

    public function testGetUserPermissionsDoesNotGuessForOverwriteWhenNotRouteNotPresent(): void
    {
        $this->request->initialize(query: [], attributes: []);

        $condition = new SecurityCondition('sulu.global.snippets');
        $result = $this->manager->getUserPermissions($condition, null);

        self::assertSame(['view' => true, 'edit' => false], $result);
    }

    public function testGetUserPermissionsDoesNotGuessForOverwriteWhenNotSnippetRoute(): void
    {
        $this->request->initialize(query: [], attributes: ['_route' => 'some_random_snippet']);

        $condition = new SecurityCondition('sulu.global.snippets');
        $result = $this->manager->getUserPermissions($condition, null);

        self::assertSame(['view' => true, 'edit' => false], $result);
    }

    public function testGetUserPermissionsDoesNotGuessForOverwriteWhenSnippetNotFound(): void
    {
        $this->request->initialize(query: [], attributes: ['_route' => 'sulu_snippet.add']);

        $condition = new SecurityCondition('sulu.global.snippets');
        $result = $this->manager->getUserPermissions($condition, null);

        self::assertSame(['view' => true, 'edit' => false], $result);
    }

    public function testGetUserPermissionsUsesTypeOfFoundSnippet(): void
    {
        $this->oldManager->result['snippet_manager.shop'] = ['view' => true, 'edit' => true];
        $foundDoc = new SnippetDocument();
        $foundDoc->setStructureType('shop');
        $this->documentManager->foundDocument = $foundDoc;
        $this->request->initialize(query: [], attributes: ['_route' => 'sulu_snippet.add']);

        $condition = new SecurityCondition('sulu.global.snippets');
        $result = $this->manager->getUserPermissions($condition, null);

        self::assertSame(['view' => true, 'edit' => true], $result);
    }

    public function testGetUserPermissionsUsesTypeFromRequestSnippetPermissions(): void
    {
        $this->oldManager->result['snippet_manager.shop'] = ['view' => true, 'edit' => true];
        $this->request->initialize(query: ['types' => 'shop']);
        $condition = new SecurityCondition('sulu.global.snippets');
        $result = $this->manager->getUserPermissions($condition, null);

        self::assertSame(['view' => true, 'edit' => true], $result);
    }

    public function testGetUserPermissionsUsesTemplateFromRequestSnippetPermissions(): void
    {
        $this->oldManager->result['snippet_manager.shop'] = ['view' => true, 'edit' => true];
        $this->request->initialize(request: ['template' => 'shop']);
        $condition = new SecurityCondition('sulu.global.snippets');
        $result = $this->manager->getUserPermissions($condition, null);

        self::assertSame(['view' => true, 'edit' => true], $result);
    }
}