<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Assert;

use PHPUnit\Framework\Assert;
use Sulu\Bundle\AdminBundle\Admin\View\View;

class AssertView extends Assert
{
    public static function assertResourceView(array $expected, View $view): void
    {
        self::assertSame($expected['name'], $view->getName(), 'name does not match');
        self::assertSame($expected['path'], $view->getPath(), 'path does not match');
        self::assertSame($expected['locales'] ?? [], $view->getOption('locales'), 'locales do not match');
        self::assertSame($expected['backView'] ?? '', $view->getOption('backView'), 'backView does not match');
        self::assertSame($expected['routerAttributesToBackView'] ?? null, $view->getOption('routerAttributesToBackView'), 'routerAttributesToBackView does not match');
        self::assertSame($expected['parent'] ?? null, $view->getOption('parent'), 'parent does not match');
    }

    public static function assertListView(array $expected, View $view): void
    {
        self::assertSame($expected['name'], $view->getName(), 'name does not match');
        self::assertSame($expected['path'], $view->getPath(), 'path does not match');
        self::assertSame($expected['locales'] ?? [], $view->getOption('locales'), 'locales do not match');
        self::assertSame($expected['backView'] ?? null, $view->getOption('backView'), 'backView does not match');
        self::assertSame($expected['parent'] ?? null, $view->getOption('parent'), 'parent does not match');
        self::assertSame($expected['requestParameters'], $view->getOption('requestParameters'), 'requestParameters does not match');
        self::assertSame($expected['resourceKey'], $view->getOption('resourceKey'), 'resourceKey does not match');
        self::assertSame($expected['title'], $view->getOption('title'), 'title does not match');
        self::assertSame($expected['listKey'], $view->getOption('listKey'), 'listKey does not match');
        self::assertSame($expected['routerAttributesToListRequest'], $view->getOption('routerAttributesToListRequest'), 'routerAttributesToListRequest does not match');
        self::assertSame($expected['editView'], $view->getOption('editView'), 'editView does not match');
        self::assertSame($expected['addView'], $view->getOption('addView'), 'addView does not match');
        self::assertSame($expected['toolbarActions'], $view->getOption('toolbarActions'), 'toolbarActions do not match');
    }

    public static function assertFormView(array $expected, View $view): void
    {
        self::assertSame($expected['name'] ?? null, $view->getName(), 'name does not match');
        self::assertSame($expected['path'] ?? null, $view->getPath(), 'path does not match');
        self::assertSame($expected['backView'] ?? null, $view->getOption('backView'), 'backView does not match');
        self::assertSame($expected['parent'] ?? null, $view->getParent(), 'parent does not match');
        self::assertSame($expected['resourceKey'] ?? null, $view->getOption('resourceKey'), 'resourceKey does not match');
        self::assertSame($expected['editView'] ?? null, $view->getOption('editView'), 'editView does not match');
        self::assertSame($expected['toolbarActions'] ?? null, $view->getOption('toolbarActions'), 'toolbarActions do not match');
        self::assertSame($expected['metadataRequestParameters'] ?? null, $view->getOption('metadataRequestParameters'), 'metaDataRequestParameters do not match');
    }
}
