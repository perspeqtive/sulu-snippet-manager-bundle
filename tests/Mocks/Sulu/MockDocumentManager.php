<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu;

use Sulu\Bundle\SnippetBundle\Document\SnippetDocument;
use Sulu\Component\DocumentManager\DocumentManagerInterface;
use Sulu\Component\DocumentManager\Exception\DocumentManagerException;

class MockDocumentManager implements DocumentManagerInterface
{
    public function __construct(public ?SnippetDocument $foundDocument = null)
    {
    }

    public function find($identifier, $locale = null, array $options = []): SnippetDocument
    {
        if ($this->foundDocument === null) {
            throw new DocumentManagerException('Document not found');
        }

        return $this->foundDocument;
    }

    public function create($alias)
    {
    }

    public function persist($document, $locale = null, array $options = [])
    {
    }

    public function remove($document)
    {
    }

    public function removeLocale($document, $locale)
    {
    }

    public function move($document, $destId)
    {
    }

    public function copy($document, $destPath)
    {
    }

    public function copyLocale($document, $srcLocale, $destLocale)
    {
    }

    public function reorder($document, $destId)
    {
    }

    public function publish($document, $locale = null, array $options = [])
    {
    }

    public function unpublish($document, $locale)
    {
    }

    public function removeDraft($document, $locale)
    {
    }

    public function restore($document, $locale, $version, array $options = [])
    {
    }

    public function refresh($document)
    {
    }

    public function flush()
    {
    }

    public function clear()
    {
    }

    public function createQuery($query, $locale = null, array $options = [])
    {
    }
}
