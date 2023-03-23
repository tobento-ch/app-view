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
use Tobento\App\Http\Boot\Middleware;
use Tobento\App\Http\Boot\RequesterResponser;
use Tobento\App\Http\Boot\Session;
use Tobento\Service\Form\Middleware\VerifyCsrfToken;
use Tobento\Service\Form\FormFactoryInterface;
use Tobento\Service\Form\ResponserFormFactory;
use Tobento\Service\Form\TokenizerInterface;
use Tobento\Service\Form\SessionTokenizer;
use Tobento\Service\Form\Form as ServiceForm;
use Tobento\Service\Session\SessionInterface;
use Tobento\Service\View\ViewInterface;

/**
 * Form boot.
 */
class Form extends Boot
{
    public const INFO = [
        'boot' => [
            'Form implementation',
        ],
    ];
    
    public const BOOT = [
        Middleware::class,
        RequesterResponser::class,
        Session::class,
    ];
    
    /**
     * Boot application services.
     *
     * @param Middleware $middleware
     * @return void
     */
    public function boot(Middleware $middleware): void
    {
        $middleware->add(VerifyCsrfToken::class, priority: 5050);
        
        $this->app->set(TokenizerInterface::class, function() {
            return new SessionTokenizer(
                session: $this->app->get(SessionInterface::class),
                tokenName: 'csrf',
                tokenInputName: '_token',
            );
        });
        
        $this->app->set(FormFactoryInterface::class, ResponserFormFactory::class);
        
        $this->app->on(ViewInterface::class, function(ViewInterface $view) {
            $app = $this->app;
            $view->addMacro('form', function() use ($app): ServiceForm {
                return $app->get(FormFactoryInterface::class)->createForm();
            });
        });
    }
}