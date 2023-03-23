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
use Tobento\Service\Menu\MenusInterface;
use Tobento\Service\Menu\MenuInterface;
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
            $this->app->set(MenusInterface::class, ServiceMenus::class);
        }
        
        $this->app->on(ViewInterface::class, function(ViewInterface $view) {
            $view->addMacro('menu', [$this, 'menu']);
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
}