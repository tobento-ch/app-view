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
use Tobento\App\Migration\Boot\Migration;
use Tobento\Service\Table\Table as ServiceTable;
use Tobento\Service\View\ViewInterface;

/**
 * Table boot.
 */
class Table extends Boot
{
    public const INFO = [
        'boot' => [
            'Migrates table.css to the public css directory',
            'Adds table view macro',
        ],
    ];
    
    public const BOOT = [
        Migration::class,
    ];
    
    /**
     * Boot application services.
     *
     * @param Migration $migration
     * @return void
     */
    public function boot(Migration $migration): void
    {
        // Install migrations:
        $migration->install(\Tobento\App\View\Migration\Table::class);
        
        $this->app->on(ViewInterface::class, function(ViewInterface $view) {
            $view->addMacro('table', function(string $name) {
                return new ServiceTable($name);
            });
        });
    }
}