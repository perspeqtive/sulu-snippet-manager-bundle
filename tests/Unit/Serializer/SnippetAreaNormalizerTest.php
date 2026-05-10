<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Unit\Serializer;

use PERSPEQTIVE\SuluSnippetManagerBundle\Security\PermissionTypes;
use PERSPEQTIVE\SuluSnippetManagerBundle\Serializer\SnippetAreaNormalizer;
use PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu\MockSecurityChecker;
use PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Symfony\MockNormalizer;
use PHPUnit\Framework\TestCase;

class SnippetAreaNormalizerTest extends TestCase
{
    private MockSecurityChecker $securityChecker;
    private MockNormalizer $normalizer;
    private SnippetAreaNormalizer $snippetAreaNormalizer;

    protected function setUp(): void
    {
        $this->securityChecker = new MockSecurityChecker(['*' => false]);
        $this->normalizer = new MockNormalizer(['normalized-result']);

        $this->snippetAreaNormalizer = new SnippetAreaNormalizer(
            $this->securityChecker
        );
        $this->snippetAreaNormalizer->setNormalizer($this->normalizer);
    }

    public function testSupportsNormalization(): void
    {
        $data = ['_embedded' => []];

        self::assertTrue($this->snippetAreaNormalizer->supportsNormalization(
            $data,
            null,
            ['sulu_admin_snippet_list' => true]
        ));

        self::assertFalse($this->snippetAreaNormalizer->supportsNormalization(
            $data,
            null,
            ['sulu_admin_snippet_list' => false]
        ));

        self::assertFalse($this->snippetAreaNormalizer->supportsNormalization(
            $data,
            null,
            [SnippetAreaNormalizer::class => true]
        ));

        self::assertFalse($this->snippetAreaNormalizer->supportsNormalization(
            ['other' => 'data'],
            null,
            ['sulu_admin_snippet_list' => true]
        ));
    }

    public function testGetSupportedTypes(): void
    {
        self::assertEquals(['*' => false], $this->snippetAreaNormalizer->getSupportedTypes(null));
    }

    public function testNormalizeWithPermissions(): void
    {
        $data = [
            '_embedded' => [
                'snippet_areas' => [
                    ['templateKey' => 'area1'],
                    ['templateKey' => 'area2'],
                ],
            ],
        ];

        $context = ['sulu_admin_snippet_list' => true];

        $this->securityChecker->hasPermission = [
            'snippet_manager.area1_default_snippets' => [PermissionTypes::EDIT => true],
            'snippet_manager.area2_default_snippets' => [PermissionTypes::EDIT => false],
        ];

        $expectedModifiedData = [
            '_embedded' => [
                'snippet_areas' => [
                    ['templateKey' => 'area1'],
                ],
            ],
        ];

        $this->normalizer->result = ['normalized-data'];

        $result = $this->snippetAreaNormalizer->normalize($data, null, $context);

        self::assertSame($expectedModifiedData, $this->normalizer->dataToNormalize);
        self::assertSame(['sulu_admin_snippet_list' => true, SnippetAreaNormalizer::class => true], $this->normalizer->context);
       self::assertSame(['normalized-data'], $result);
    }

    public function testNormalizeWithoutCorrectContext(): void
    {
        $data = ['some' => 'data'];
        $context = ['sulu_admin_snippet_list' => false];

        $result = $this->snippetAreaNormalizer->normalize($data, null, $context);

        self::assertSame(['normalized-result'], $result);
        self::assertSame($data, $this->normalizer->dataToNormalize);
        self::assertSame(['sulu_admin_snippet_list' => false, SnippetAreaNormalizer::class => true], $this->normalizer->context);
    }
}