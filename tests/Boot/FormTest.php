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
use Tobento\App\View\Boot\Form;
use Tobento\Service\Form\Form as ServiceForm;
use Tobento\Service\Form\FormFactoryInterface;
use Tobento\Service\Form\TokenizerInterface;
use Tobento\Service\View\ViewInterface;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\Service\Filesystem\Dir;
    
/**
 * FormTest
 */
class FormTest extends TestCase
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
    
    public function testInterfacesAreAvailable()
    {
        $app = $this->createApp();
        //$app->boot(View::class);
        $app->boot(Form::class);
        $app->booting();

        $this->assertInstanceof(
            TokenizerInterface::class,
            $app->get(TokenizerInterface::class)
        );
        
        $this->assertInstanceof(
            FormFactoryInterface::class,
            $app->get(FormFactoryInterface::class)
        );
    }
    
    public function testFormMacroIsAvailable()
    {
        $app = $this->createApp();
        $app->boot(View::class);
        $app->boot(Form::class);
        $app->booting();
        
        $view = $app->get(ViewInterface::class);
        
        $this->assertInstanceof(
            ServiceForm::class,
            $view->form()
        );
    }
    
    public function testViewCsrfTokenIsAvailable()
    {
        $app = $this->createApp();
        $app->boot(View::class);
        $app->boot(Form::class);
        $app->booting();
        
        $view = $app->get(ViewInterface::class);
        
        $this->assertSame(32, strlen($view->data()->get('csrfToken')));
    }
}