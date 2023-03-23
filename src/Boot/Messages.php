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
use Tobento\Service\Message\MessagesInterface;
use Tobento\Service\Message\MessageInterface;
use Tobento\Service\View\ViewInterface;
use Tobento\Service\Responser\ResponserInterface;

/**
 * Messages boot.
 */
class Messages extends Boot
{
    public const INFO = [
        'boot' => [
            'Adds messages view with if any messages are available from the http responser or passed by view data',
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
            
            $view->on('inc.messages', function(array $data, ViewInterface $view): array {
                // specific view data:
                if (isset($data['messages']) && $data['messages'] instanceof MessagesInterface) {
                    $view->add(key: 'inc.messages', view: 'inc/messages');                    
                    return $data;
                }
                
                // shared view data:
                if ($view->data()->get('messages') instanceof MessagesInterface) {
                    $view->add(key: 'inc.messages', view: 'inc/messages');                    
                    return $data;
                }
                
                // responser:
                if ($this->app->has(ResponserInterface::class)) {
                    $view->add(key: 'inc.messages', view: 'inc/messages');
                    
                    $messages = $this->app->get(ResponserInterface::class)->messages();
                    
                    // messages with key might belong to form messages,
                    // so we skip them as not to be diplayed twice:
                    $data['messages'] = $messages->filter(
                        fn(MessageInterface $m): bool => $m->key() === null
                    );
                    
                    return $data;
                }
                
                return $data;
            });
        });
    }
}