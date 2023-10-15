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
use Tobento\Service\View\ViewInterface;
use Tobento\Service\View\View as DefaultView;
use Tobento\Service\View\PhpRenderer;
use Tobento\Service\View\Data;
use Tobento\Service\View\Assets;
use Tobento\Service\View\TagsAttributes;
use Tobento\Service\Uri\AssetUriInterface;
use Tobento\Service\Routing\RouterInterface;
use Tobento\Service\Language\LanguagesInterface;
use Tobento\Service\Translation\TranslatorInterface;

/**
 * View boot.
 */
class View extends Boot
{
    public const INFO = [
        'boot' => [
            'Migrates views and css assets for default layout',
            'ViewInterface implementation macros and with global data',
        ],
    ];
    
    public const BOOT = [
        Migration::class,
        Menus::class,
    ];
    
    /**
     * Boot application services.
     *
     * @param Migration $migration
     * @param null|AssetUriInterface $assetUri
     * @return void
     */
    public function boot(Migration $migration, null|AssetUriInterface $assetUri = null): void
    {
        // Add view dir if not exists:
        if (! $this->app->dirs()->has('views')) {
            $this->app->dirs()->dir(
                dir: $this->app->dir('app').'views/',
                name: 'views',
                group: 'views',
                priority: 100,
            );
        }
        
        // Install migrations:
        $migration->install(\Tobento\App\View\Migration\View::class);
        
        $this->app->set(ViewInterface::class, function() use ($assetUri) {

            if ($assetUri) {
                $assetUri = ltrim((string)$assetUri, '/').'/';
            }

            $view = new DefaultView(
                new PhpRenderer(
                    $this->app->dirs()->sort()->group('views')
                ),
                new Data(),
                new Assets(
                    assetDir: $this->app->dirs()->get('public'),
                    assetUri: (string)$assetUri
                )
            );
            
            // Global data and macros:
            $view->with('htmlLang', 'en');
            $view->with('locale', 'en');
            $view->with('routeName', '');
            
            $tags = new TagsAttributes();
            $view->addMacro('tagAttributes', [$tags, 'get']);
            
            $app = $this->app;
            $view->addMacro('app', function() use ($app) {
                return $app;
            });
            
            // we might set this on app-http router boot
            if ($this->app->has(RouterInterface::class)) {
                
                $view->addMacro('routeUrl', [$this->app->get(RouterInterface::class), 'url']);
                
                $matchedRoute = $this->app->get(RouterInterface::class)->getMatchedRoute();
                $view->with('routeName', $matchedRoute?->getName() ?: '');
            }
            
            $view->addMacro('trans', [$this, 'trans']);
            
            $view->addMacro('etrans', function(string $message, array $parameters = [], null|string $locale = null): string {
                /** @psalm-suppress UndefinedMethod **/
                return $this->esc($this->trans($message, $parameters, $locale));
            });
            
            if ($this->app->has(LanguagesInterface::class)) {
                $locale = $this->app->get(LanguagesInterface::class)->current()->locale();
                $view->with('htmlLang', str_replace('_', '-', $locale));
                $view->with('locale', $locale);
            }
            
            return $view;
        });
        
        // App macros.
        $this->app->addMacro('renderView', [$this, 'render']);
    }
    
    /**
     * Renders a view.
     *
     * @param string $view The view name.
     * @param array $data The view data.
     * @return string The view rendered.
     */
    public function render(string $view, array $data = []): string
    {
        return $this->app->get(ViewInterface::class)->render($view, $data);
    }
    
    /**
     * Returns the translated message.
     *
     * @param string $message The message to translate.
     * @param array $parameters Any parameters for the message.
     * @param null|string $locale The locale or null to use the default.
     * @return string The translated message.
     */
    public function trans(string $message, array $parameters = [], null|string $locale = null): string
    {
        if ($this->app->has(TranslatorInterface::class)) {
            return $this->app->get(TranslatorInterface::class)->trans($message, $parameters, $locale);
        }
        
        return $message;
    }
    
    /**
     * Load config data from view directory.
     *
     * @param string $file
     * @param string $dirGroup
     * @return array
     */
    public function loadConfig(string $file, string $dirGroup = 'config'): array
    {
        // ToDo
        return [];
    }
}