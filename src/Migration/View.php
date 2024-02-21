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

namespace Tobento\App\View\Migration;

use Tobento\Service\Migration\MigrationInterface;
use Tobento\Service\Migration\ActionsInterface;
use Tobento\Service\Migration\Actions;
use Tobento\Service\Migration\Action\FilesCopy;
use Tobento\Service\Migration\Action\FilesDelete;
use Tobento\Service\Dir\DirsInterface;

/**
 * View
 */
class View implements MigrationInterface
{
    /**
     * @var array The view files.
     */
    protected array $viewFiles;
    
    /**
     * @var array The asset files.
     */
    protected array $assetFiles;
    
    /**
     * Create a new View.
     *
     * @param DirsInterface $dirs
     */
    public function __construct(
        protected DirsInterface $dirs,
    ) {
        $viewsDir = realpath(__DIR__.'/../../').'/resources/views/';
        
        $this->viewFiles = [
            $this->dirs->get('views').'exception/' => [
                $viewsDir.'exception/error.php',
                $viewsDir.'exception/error.xml.php',
            ],
            $this->dirs->get('views').'inc/' => [
                $viewsDir.'inc/breadcrumb.php',
                $viewsDir.'inc/footer.php',
                $viewsDir.'inc/head.php',
                $viewsDir.'inc/header.php',
                $viewsDir.'inc/messages.php',
                $viewsDir.'inc/nav.php',
            ],
        ];
        
        $this->assetFiles = [
            $this->dirs->get('public').'css/' => [
                realpath(__DIR__.'/../../').'/resources/css/app.css',
                $this->dirs->get('vendor').'/tobento/css-basis/src/basis.css',
            ],
        ];
    }
    
    /**
     * Return a description of the migration.
     *
     * @return string
     */
    public function description(): string
    {
        return 'View files and assets.';
    }
        
    /**
     * Return the actions to be processed on install.
     *
     * @return ActionsInterface
     */
    public function install(): ActionsInterface
    {
        return new Actions(
            new FilesCopy(
                files: $this->viewFiles,
                type: 'views',
                description: 'View files.',
            ),
            new FilesCopy(
                files: $this->assetFiles,
                type: 'assets',
                description: 'Asset files.',
            ),
        );
    }

    /**
     * Return the actions to be processed on uninstall.
     *
     * @return ActionsInterface
     */
    public function uninstall(): ActionsInterface
    {
        return new Actions(
            new FilesDelete(
                files: $this->viewFiles,
                type: 'views',
                description: 'View files.',
            ),
            new FilesDelete(
                files: $this->assetFiles,
                type: 'assets',
                description: 'Asset files.',
            ),
        );
    }
}