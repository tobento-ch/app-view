<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\App\Test\Boot;

use PHPUnit\Framework\TestCase;
use Tobento\App\View\Boot\View;
use Tobento\App\View\Boot\Breadcrumb;
use Tobento\Service\View\ViewInterface;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\Service\Filesystem\Dir;
    
/**
 * BreadcrumbTest
 */
class BreadcrumbTest extends TestCase
{    
    protected function createApp(bool $deleteDir = true): AppInterface
    {
        if ($deleteDir) {
            (new Dir())->delete(__DIR__.'/../app/');
        }
        
        (new Dir())->create(__DIR__.'/../app/');
        
        $app = (new AppFactory())->createApp();
        
        $app->dirs()
            ->dir(realpath(__DIR__.'/../../'), 'root')
            ->dir(realpath(__DIR__.'/../app/'), 'app')
            ->dir($app->dir('app').'config', 'config', group: 'config')
            ->dir($app->dir('root').'vendor', 'vendor')
            ->dir($app->dir('app').'views', 'views', group: 'views')
            // for testing only we add public within app dir.
            ->dir($app->dir('app').'public', 'public');
        
        return $app;
    }
    
    public static function tearDownAfterClass(): void
    {
        (new Dir())->delete(__DIR__.'/../app/');
    }
    
    public function testRendersBreadcrumbIfRouteNameMatchesMenuTree()
    {
        $app = $this->createApp();
        
        $app->dirs()
            ->dir($app->dir('root').'/tests/views', 'test-views', group: 'views', priority: 1000);
        
        $app->boot(View::class);
        $app->boot(Breadcrumb::class);
        $app->booting();
        
        $menu = $app->get(ViewInterface::class)->menu('main');
        $menu->link('home', 'Home')->id('home');
        $menu->link('about', 'About')->id('about');
        $menu->link('team', 'Team')->id('team')->parent('about');
        
        $content = $app->get(View::class)->render(
            view: 'page-with-breadcrumb',
            data: [
                'routeName' => 'team',
            ],
        );
        
        $this->assertStringContainsString(
            '<a class="active" href="#">Team</a>',
            $content
        );
    }
    
    public function testDoesNotRendersBreadcrumbIfRouteNameDoesNotMatch()
    {
        $app = $this->createApp();
        
        $app->dirs()
            ->dir($app->dir('root').'/tests/views', 'test-views', group: 'views', priority: 1000);
        
        $app->boot(View::class);
        $app->boot(Breadcrumb::class);
        $app->booting();
        
        $menu = $app->get(ViewInterface::class)->menu('main');
        $menu->link('home', 'Home')->id('home');
        $menu->link('about', 'About')->id('about');
        $menu->link('team', 'Team')->id('team')->parent('about');
        
        $content = $app->get(View::class)->render(
            view: 'page-with-breadcrumb',
            data: [
                'routeName' => 'unknown',
            ],
        );
        
        $this->assertStringContainsString(
            '',
            $content
        );
    }
    
    public function testDoesNotRendersBreadcrumbIfNoMainMenu()
    {
        $app = $this->createApp();
        
        $app->dirs()
            ->dir($app->dir('root').'/tests/views', 'test-views', group: 'views', priority: 1000);
        
        $app->boot(View::class);
        $app->boot(Breadcrumb::class);
        $app->booting();
        
        $content = $app->get(View::class)->render(
            view: 'page-with-breadcrumb',
            data: [
                'routeName' => 'team',
            ],
        );
        
        $this->assertStringContainsString(
            '',
            $content
        );
    }
}