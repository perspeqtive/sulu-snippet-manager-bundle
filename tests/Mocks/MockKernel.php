<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class MockKernel implements HttpKernelInterface
{
    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
    {
    }
}
