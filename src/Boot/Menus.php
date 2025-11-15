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
 
namespace Tobento\App\View\Boot;

use Tobento\App\Boot;
use Tobento\Service\Icon\IconsInterface;
use Tobento\Service\Menu\MenuFactory;
use Tobento\Service\Menu\MenuIconsFactory;
use Tobento\Service\Menu\MenuInterface;
use Tobento\Service\Menu\MenusInterface;
use Tobento\Service\Menu\Menus as ServiceMenus;
use Tobento\Service\View\ViewInterface;

/**
 * Menus boot.
 */
class Menus extends Boot
{
    public const INFO = [
        'boot' => [
            'MenusInterface implementation',
            'Adds menu view macro',
        ],
    ];
    
    /**
     * Boot application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if (! $this->app->has(MenusInterface::class)) {
            $this->app->set(MenusInterface::class, static function (null|IconsInterface $icons) {
                return new ServiceMenus(
                    menuFactory: $icons ? new MenuIconsFactory(icons: $icons) : null
                );
            });
        }
        
        $this->app->on(ViewInterface::class, function(ViewInterface $view) {
            $view->addMacro('menu', [$this, 'menu']);
            $view->addMacro('createMenu', [$this, 'createMenu']);
        });
    }
    
    /**
     * Get the menu or create it.
     *
     * @param string $name The menu name
     * @return MenuInterface
     */
    public function menu(string $name): MenuInterface
    {
        return $this->app->get(MenusInterface::class)->menu($name);
    }
    
    /**
     * Create a new menu.
     *
     * @param string $name The menu name
     * @return MenuInterface
     */
    public function createMenu(string $name): MenuInterface
    {
        if ($this->app->has(IconsInterface::class)) {
            return new MenuIconsFactory(icons: $this->app->get(IconsInterface::class))->createMenu(name: $name);
        }
        
        return new MenuFactory()->createMenu(name: $name);
    }
}