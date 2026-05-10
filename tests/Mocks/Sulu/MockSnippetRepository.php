<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu;

use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Snippet\Domain\Model\Snippet;
use Sulu\Snippet\Domain\Model\SnippetInterface;
use Sulu\Snippet\Domain\Repository\SnippetRepositoryInterface;

class MockSnippetRepository implements SnippetRepositoryInterface
{
    public function __construct(public ?Snippet $findOneByResult = null)
    {
    }

    public function createNew(?string $uuid = null): SnippetInterface
    {
    }

    public function getOneBy(array $filters, array $selects = []): SnippetInterface
    {
    }

    public function findOneBy(array $filters, array $selects = []): ?SnippetInterface
    {
        return $this->findOneByResult;
    }

    public function findBy(array $filters = [], array $sortBy = [], array $selects = []): iterable
    {
    }

    public function findIdentifiersBy(array $filters = [], array $sortBy = []): iterable
    {
    }

    public function countBy(array $filters = []): int
    {
    }

    public function add(SnippetInterface $snippet): void
    {
    }

    public function remove(SnippetInterface $snippet): void
    {
    }

    public function removeDimensionContent(DimensionContentInterface $dimensionContent): void
    {
    }
}
