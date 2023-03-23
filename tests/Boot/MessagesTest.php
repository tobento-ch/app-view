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
use Tobento\App\View\Boot\Messages;
use Tobento\Service\Message\MessagesInterface;
use Tobento\Service\Message\Messages as ServiceMessages;
use Tobento\Service\Responser\ResponserInterface;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\Service\Filesystem\Dir;
    
/**
 * MessagesTest
 */
class MessagesTest extends TestCase
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
    
    public function testRendersMessages()
    {
        $app = $this->createApp();
        
        $app->dirs()
            ->dir($app->dir('root').'/tests/views', 'test-views', group: 'views', priority: 1000);
        
        $app->boot(View::class);
        $app->boot(Messages::class);
        $app->booting();
        
        $messages = new ServiceMessages();
        $messages->add(level: 'error', message: 'Error message');

        $content = $app->get(View::class)->render(
            view: 'page-with-messages',
            data: [
                'messages' => $messages,
            ],
        );
        
        $this->assertStringStartsWith(
            'Page:<p class="message error">Error message</p',
            $content
        );
    }
    
    public function testRendersMessagesFromResponser()
    {
        $app = $this->createApp();
        
        $app->dirs()
            ->dir($app->dir('root').'/tests/views', 'test-views', group: 'views', priority: 1000);
        
        $app->boot(View::class);
        $app->boot(Messages::class);
        $app->boot(\Tobento\App\Http\Boot\RequesterResponser::class);
        $app->booting();
        
        $responser = $app->get(ResponserInterface::class);
        // no need to add message as message from migrator gets added.
        //$responser->messages()->add(level: 'success', message: 'Success message');

        $content = $app->get(View::class)->render(
            view: 'page-with-messages',
        );
        
        $this->assertStringStartsWith(
            'Page:<p class="message success">Successfully installed:',
            $content
        );
    }
}