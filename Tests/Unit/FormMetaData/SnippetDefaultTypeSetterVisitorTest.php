<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Unit\FormMetaData;

use PERSPEQTIVE\SuluSnippetManagerBundle\FormMetaData\SnippetDefaultTypeSetterVisitor;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\TypedFormMetadata;

class SnippetDefaultTypeSetterVisitorTest extends TestCase
{
    public function testVisitDoesNotApply(): void
    {
        $expected = new TypedFormMetadata();
        $expected->setDefaultType('defaultType');

        $formMetaData = new TypedFormMetadata();
        $formMetaData->setDefaultType('defaultType');
        $visitor = new SnippetDefaultTypeSetterVisitor();
        $visitor->visitTypedFormMetadata($formMetaData, 'snippet', 'de', []);

        self::assertEquals($expected, $formMetaData);
    }

    public function testVisitAppliesDefaultType(): void
    {
        $expected = new TypedFormMetadata();
        $expected->setDefaultType('testSnippet');

        $formMetaData = new TypedFormMetadata();
        $formMetaData->setDefaultType('defaultType');
        $visitor = new SnippetDefaultTypeSetterVisitor();
        $visitor->visitTypedFormMetadata($formMetaData, 'snippet', 'de', ['overwriteDefaultType' => 'testSnippet']);

        self::assertEquals($expected, $formMetaData);
    }
}
