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
use Tobento\App\Http\Boot\Routing;
use Tobento\App\Http\Boot\Http;
use Tobento\Service\View\ViewInterface;
use Tobento\Service\Responser\ResponserInterface;
use Tobento\Service\Language\LanguagesInterface;
use Tobento\Service\Language\LanguageFactory;
use Tobento\Service\Language\Languages;
use Tobento\Service\Menu\MenuInterface;
use Tobento\Service\Routing\UrlInterface;
use Tobento\Service\Tag\AttributesInterface;
use Tobento\Service\Translation;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\Service\Filesystem\Dir;
use Tobento\App\Http\ResponseEmitterInterface;
use Tobento\App\Http\Test\Mock\ResponseEmitter;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
    
/**
 * ViewTest
 */
class ViewTest extends TestCase
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
    
    public function testRenderViewUsingApp()
    {
        $app = $this->createApp();
        $app->boot(View::class);
        $app->booting();
        
        $content = $app->get(ViewInterface::class)->render(
            view: 'exception/error',
            data: ['code' => '404', 'message' => '404'],
        );
        
        $this->assertStringStartsWith(
            '<!DOCTYPE html>',
            $content
        );
    }
    
    public function testRenderViewUsingBoot()
    {
        $app = $this->createApp();
        $app->boot(View::class);
        $app->booting();
        
        $content = $app->get(View::class)->render(
            view: 'exception/error.xml',
            data: ['code' => '404', 'message' => '404'],
        );
        
        $this->assertStringStartsWith(
            '<error>',
            $content
        );
    }
    
    public function testRenderViewUsingResponser()
    {
        $app = $this->createApp();
        $app->boot(View::class);
        $app->boot(\Tobento\App\Http\Boot\RequesterResponser::class);
        $app->booting();
        
        $response = $app->get(ResponserInterface::class)->render(
            view: 'exception/error.xml',
            data: ['code' => '404', 'message' => '404'],
        );
        
        $this->assertStringStartsWith(
            '<error>',
            (string)$response->getBody()
        );
    }

    public function testGlobalViewDataAreAvailableWithinViewData()
    {
        $app = $this->createApp();
        $app->boot(View::class);
        $app->booting();

        $this->assertSame(
            ['htmlLang' => 'en', 'locale' => 'en', 'routeName' => ''],
            $app->get(ViewInterface::class)->data()->all()
        );
    }
    
    public function testGlobalViewVariablesAreAvailableWithinViewFile()
    {
        $app = $this->createApp();
        
        $app->dirs()
            ->dir($app->dir('root').'/tests/views', 'test-views', group: 'views', priority: 1000);
            
        $app->boot(View::class);
        $app->booting();
        
        $content = $app->get(View::class)->render(
            view: 'global-variables',
        );
        
        $this->assertStringStartsWith(
            'htmlLang:en locale:en routeName:',
            $content
        );
    }
    
    public function testGlobalViewDataWithLanguagesUsesCurrentLanguage()
    {
        $app = $this->createApp();
        $app->boot(View::class);
        $app->booting();
        
        $app->set(LanguagesInterface::class, function() {
            $languageFactory = new LanguageFactory();

            $languages = new Languages(
                $languageFactory->createLanguage('en', default: true),
                $languageFactory->createLanguage('de', fallback: 'en'),
            );
            
            $languages->current('de');
            
            return $languages;
        });

        $this->assertSame(
            ['htmlLang' => 'de', 'locale' => 'de', 'routeName' => ''],
            $app->get(ViewInterface::class)->data()->all()
        );
    }
    
    public function testGlobalViewDataWithHttpRouting()
    {
        $app = $this->createApp();
        
        // Replace response emitter for testing:
        $app->on(ResponseEmitterInterface::class, ResponseEmitter::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(
                method: 'GET',
                uri: 'foo',
                serverParams: [],
            );
        });
        
        $app->boot(View::class);
        $app->boot(Routing::class);
        $app->booting();
        
        $app->route('GET', 'foo', function() use ($app) {
            return $app->get(ViewInterface::class)->data()->get('routeName');
        })->name('foo');
        
        $app->run();

        $this->assertSame(
            'foo',
            (string)$app->get(Http::class)->getResponse()->getBody()
        );
    }

    public function testAppMacroIsAvailable()
    {
        $app = $this->createApp();
        $app->boot(View::class);
        $app->booting();
        
        $this->assertInstanceof(
            AppInterface::class,
            $app->get(ViewInterface::class)->app()
        );
    }
    
    public function testMenuMacroIsAvailable()
    {
        $app = $this->createApp();
        $app->boot(View::class);
        $app->booting();
        
        $this->assertInstanceof(
            MenuInterface::class,
            $app->get(ViewInterface::class)->menu(name: 'main')
        );
    }
    
    public function testTransMacrosAreAvailable()
    {
        $app = $this->createApp();
        $app->boot(View::class);
        $app->booting();
        
        $translated = $app->get(ViewInterface::class)->trans(
            message: 'Hi :name',
            parameters: [':name' => 'John'],
            locale: 'de',
        );
        
        $this->assertSame('Hi :name', $translated);
        
        $translated = $app->get(ViewInterface::class)->etrans(
            message: '<p>Hi</p>',
            parameters: [],
            locale: 'de',
        );
        
        $this->assertSame('&lt;p&gt;Hi&lt;/p&gt;', $translated);
    }
    
    public function testTransMacrosWithTranslator()
    {
        $app = $this->createApp();
        $app->boot(View::class);
        $app->booting();
        
        $app->set(Translation\TranslatorInterface::class, function() {
            return new Translation\Translator(
                new Translation\Resources(
                    new Translation\Resource('*', 'de', [
                        'Hello World' => 'Hallo Welt',
                    ]),
                ),
                new Translation\Modifiers(),
                new Translation\MissingTranslationHandler(),
                'de',
            );
        });
        
        $translated = $app->get(ViewInterface::class)->trans(
            message: 'Hello World',
            parameters: [],
            locale: 'de',
        );
        
        $this->assertSame('Hallo Welt', $translated);
        
        $translated = $app->get(ViewInterface::class)->etrans(
            message: 'Hello World',
            parameters: [],
            locale: 'de',
        );
        
        $this->assertSame('Hallo Welt', $translated);
    }    
    
    public function testRouteUrlMacroIsAvailable()
    {
        $app = $this->createApp();
        $app->boot(View::class);
        $app->boot(Routing::class);
        $app->booting();
        
        $app->route('GET', 'foo', function() use ($app) {
            return ['page' => 'foo'];
        })->name('foo');
        
        $url = $app->get(ViewInterface::class)->routeUrl(
            name: 'foo',
            parameters: [],
        );
        
        $this->assertInstanceof(
            UrlInterface::class,
            $url
        );
    }

    public function testTagAttributesMacroIsAvailable()
    {
        $app = $this->createApp();
        $app->boot(View::class);
        $app->booting();
        
        $this->assertInstanceof(
            AttributesInterface::class,
            $app->get(ViewInterface::class)->tagAttributes('body')
        );
    }
}