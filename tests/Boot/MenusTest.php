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
use Tobento\App\View\Boot\Menus;
use Tobento\Service\Menu\MenusInterface;
use Tobento\Service\Menu\MenuInterface;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\Service\Filesystem\Dir;
    
/**
 * MenusTest
 */
class MenusTest extends TestCase
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
    
    public function testMenusInterfaceIsAvailable()
    {
        $app = $this->createApp();
        $app->boot(Menus::class);
        $app->booting();
        
        $this->assertInstanceof(
            MenusInterface::class,
            $app->get(MenusInterface::class)
        );
    }
    
    public function testGetMenuUsingMenusBoot()
    {
        $app = $this->createApp();
        $app->boot(Menus::class);
        $app->booting();
        
        $this->assertInstanceof(
            MenuInterface::class,
            $app->get(Menus::class)->menu('main')
        );
    }
}