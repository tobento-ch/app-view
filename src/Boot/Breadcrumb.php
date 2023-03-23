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
use Tobento\Service\Menu\ItemInterface;
use Tobento\Service\Menu\Link;
use Tobento\Service\Menu\Item;
use Tobento\Service\View\ViewInterface;

/**
 * Breadcrumb boot.
 */
class Breadcrumb extends Boot
{
    public const INFO = [
        'boot' => [
            'Creates breadcrumb menu based on the main menu',
        ],
    ];
    
    /**
     * Boot application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app->on(ViewInterface::class, function(ViewInterface $view) {
            
            // only create breadcrumb menu on rendering its view:
            $view->on('inc.breadcrumb', function(array $data, ViewInterface $view): array {
                
                $view->add(key: 'inc.breadcrumb', view: 'inc/breadcrumb');
                
                $this->createBreadcrumbMenu($view->data()->get('routeName'));
                return $data;
            });
        });
    }
    
    /**
     * Creates breadcrumb menu based on the main menu.
     *
     * @param mixed $routeName
     * @return void
     */
    protected function createBreadcrumbMenu(mixed $routeName): void
    {
        if (!is_string($routeName)) {
            return;
        }
        
        if (! $this->app->has(MenusInterface::class)) {
            return;
        }
        
        $mainMenu = $this->app->get(MenusInterface::class)->menu('main');
        
        if (is_null($activeItem = $mainMenu->get($routeName))) {
            return;
        }
        
        $breadcrumbMenu = $this->app->get(MenusInterface::class)->menu('breadcrumb');
        
        // traverse over parent items and create breadcrumb menu items from:
        $traverseParent = function(ItemInterface $item, MenuInterface $menu)
            use ($routeName, $breadcrumbMenu, &$traverseParent): void {
            
            if ($item instanceof Link) {
                
                $url = $routeName === $item->getTreeId() ? '#' : $item->url();
                
                $breadcrumbMenu->link($url, $item->text())
                    ->id($item->getTreeId())
                    ->order($item->getTreeLevel());
            } elseif ($item instanceof Item) {
                $breadcrumbMenu->item($item->text())
                    ->id($item->getTreeId())
                    ->order($item->getTreeLevel());
            }

            if ($item->getTreeParentItem()) {
                $traverseParent($item->getTreeParentItem(), $menu);
            }
        };
        
        $traverseParent($activeItem, $mainMenu);
    }
}