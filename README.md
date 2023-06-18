# App View

The app view includes support for creating menus, forms and more for creating any kind of web applications. It comes with a default layout using the [Basis Css](https://github.com/tobento-ch/css-basis) which you may use or not. Some [App Bundles](https://github.com/tobento-ch?tab=repositories&q=app) may rely on this though.

## Table of Contents

- [Getting Started](#getting-started)
    - [Requirements](#requirements)
- [Documentation](#documentation)
    - [App](#app)
    - [View Boot](#view-boot)
        - [Rendering Views](#rendering-views)
        - [Global View Data And Variables](#global-view-data-and-variables)
        - [Available View Macros](#available-view-macros)
    - [Menus Boot](#menus-boot)
    - [Form Boot](#form-boot)
        - [Form Messages](#form-messages)
    - [Messages Boot](#messages-boot)
    - [Breadcrumb Boot](#breadcrumb-boot)
    - [Table Boot](#table-boot)
    - [Default Layout](#default-layout)
        - [Views](#views)
        - [Exception Views](#exception-views)
    - [Themes](#themes)
        - [Theme Views](#theme-views)
        - [Theme Assets](#theme-asstes)
- [Credits](#credits)
___

# Getting Started

Add the latest version of the app view project running this command.

```
composer require tobento/app-view
```

## Requirements

- PHP 8.0 or greater

# Documentation

## App

Check out the [**App Skeleton**](https://github.com/tobento-ch/app-skeleton) if you are using the skeleton.

You may also check out the [**App**](https://github.com/tobento-ch/app) to learn more about the app in general.

## View Boot

The view boot does the following:

* Migrates views and css assets for default layout
* Implements [ViewInterface](https://github.com/tobento-ch/service-view#view)
* Adds global view data from different services if available
* Adds multiple view macros

```php
use Tobento\App\AppFactory;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'vendor', 'vendor')
    ->dir($app->dir('app').'views', 'views', group: 'views')
    ->dir($app->dir('root').'public', 'public');
    
// Adding boots
$app->boot(\Tobento\App\View\Boot\View::class);

// Run the app
$app->run();
```

### Rendering Views

You can render views in several ways:

**Using the app**

```php
use Tobento\App\AppFactory;
use Tobento\Service\View\ViewInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'vendor', 'vendor')
    ->dir($app->dir('app').'views', 'views', group: 'views')
    ->dir($app->dir('root').'public', 'public');
    
// Adding boots
$app->boot(\Tobento\App\View\Boot\View::class);
$app->booting();

$view = $app->get(ViewInterface::class);
$content = $view->render(view: 'about', data: []);

// or using the app macro:
$content = $app->renderView(view: 'about', data: []);

// Run the app
$app->run();
```

**Using autowiring**

You can also request the ```ViewInterface::class``` in any class resolved by the app.

```php
use Tobento\Service\View\ViewInterface;

class SomeService
{
    public function __construct(
        protected ViewInterface $view,
    ) {}
}
```

**Using the view boot**

```php
use Tobento\App\Boot;
use Tobento\App\View\Boot\View;

class AnyServiceBoot extends Boot
{
    public const BOOT = [
        // you may ensure the view boot.
        View::class,
    ];
    
    public function boot(View $view)
    {
        $content = $view->render(view: 'about', data: []);
    }
}
```

Check out the [**View Service**](https://github.com/tobento-ch/service-view) to learn more about it.

**Using the responser**

If you have booted the [App Http - Requester And Responser](https://github.com/tobento-ch/app-http#requester-and-responser-boot) boot, you may use the ```render``` method:

```php
use Tobento\Service\Responser\ResponserInterface;
use Psr\Http\Message\ResponseInterface;

class SomeHttpController
{
    public function index(ResponserInterface $responser): ResponseInterface
    {
        return $responser->render(view: 'register', data: []);
    }
}
```

### Global View Data And Variables

It adds the following global view data and variables accessible in your view files:

```php
// by variable:
$htmlLang
$locale
$routeName

// by view get method:
$htmlLang = $view->get('htmlLang', 'en');
$locale = $view->get('locale', 'en');
$routeName = $view->get('routeName', '');
```

**$htmlLang / $locale**

Code snippet from the view boot:

```php
use Tobento\Service\Language\LanguagesInterface;

if ($this->app->has(LanguagesInterface::class)) {
    $locale = $this->app->get(LanguagesInterface::class)->current()->locale();
    $view->with('htmlLang', str_replace('_', '-', $locale));
    $view->with('locale', $locale);
}
```

**$routeName**

Code snippet from the view boot:

```php
use Tobento\Service\Routing\RouterInterface;

if ($this->app->has(RouterInterface::class)) {
    $matchedRoute = $this->app->get(RouterInterface::class)->getMatchedRoute();
    $view->with('routeName', $matchedRoute?->getName() ?: '');
}
```

**Adding global data and variables**

You may add more global data by using a boot:

```php
use Tobento\App\Boot;
use Tobento\Service\View\ViewInterface;

class SomeGlobalViewDataBoot extends Boot
{
    public function boot()
    {
        // only add if view is requested:
        $this->app->on(ViewInterface::class, function(ViewInterface $view) {
            
            // using the with method:
            $view->with(name: 'someVariable', value: 'someValue');
            
            // using the data method:
            $view->data([
                'someVariable' => 'someValue',
            ]);
        });
    }
}
```

### Available View Macros

**app**

Returns the app instance.

```php
use Tobento\App\AppInterface;

var_dump($view->app() instanceof AppInterface);
// bool(true)
```

**menu**

Returns the menu for the specified name.

```php
use Tobento\Service\Menu\MenuInterface;

var_dump($view->menu('main') instanceof MenuInterface);
// bool(true)
```

Check out the [Menu Service](https://github.com/tobento-ch/service-menu) to learn more about it in general.

**trans / etrans**

Returns the message translated by the [Translator](https://github.com/tobento-ch/service-translation#translator) if available within the app.

By default, the translator is not available, you might install the [App Translation](https://github.com/tobento-ch/app-translation) bundle to do so.

```php
$translated = $view->trans(
    message: 'Hi :name',
    parameters: [':name' => 'John'],
    locale: 'de',
);

// The etrans method will escape the translated message
// with htmlspecialchars.

echo $view->etrans(
    message: 'Hi :name',
    parameters: [':name' => 'John'],
    locale: 'de',
);
```

**routeUrl**

Returns the url for the specified route if ```Tobento\Service\Routing\RouterInterface``` is available within the app which is the case if the [App Http - Routing Boot](https://github.com/tobento-ch/app-http#routing-boot) has been booted.

```php
use Tobento\Service\Routing\UrlInterface;

$url = $view->routeUrl(
    name: 'route.name',
    parameters: [],
);

var_dump($url instanceof UrlInterface);
// bool(true)
```

**tagAttributes**

Returns the attributes for the specified tag name.

```php
use Tobento\Service\Tag\AttributesInterface;

var_dump($view->tagAttributes('body') instanceof AttributesInterface);
// bool(true)
```

Check out the [Tag Service - Attributes Interface](https://github.com/tobento-ch/service-tag#attributes-interface) to learn more about it in general.

## Menus Boot

The menus boot does the following:

* Implements [MenusInterface](https://github.com/tobento-ch/service-menu#menus)
* Adds menu view macro

```php
use Tobento\App\AppFactory;
use Tobento\Service\Menu\MenusInterface;
use Tobento\Service\Menu\MenuInterface;
use Tobento\Service\View\ViewInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'vendor', 'vendor')
    ->dir($app->dir('app').'views', 'views', group: 'views')
    ->dir($app->dir('root').'public', 'public');
    
// Adding boots
$app->boot(\Tobento\App\View\Boot\View::class);
// no need to boot as already loaded by the view boot:
// $app->boot(\Tobento\App\View\Boot\Menus::class);
$app->booting();

// Get the menus:
$menus = $app->get(MenusInterface::class);

// View menu macro:
$view = $app->get(ViewInterface::class);

var_dump($view->menu('main') instanceof MenuInterface);
// bool(true)

// Run the app
$app->run();
```

Check out the [Menu Service](https://github.com/tobento-ch/service-menu) to learn more about it in general.

**Using menus boot**

You may add menu items using the menus boot:

```php
use Tobento\App\Boot;
use Tobento\App\View\Boot\Menus;

class AnyServiceBoot extends Boot
{
    public const BOOT = [
        // you may ensure the menus boot.
        Menus::class,
    ];
    
    public function boot(Menus $menus)
    {
        $menu = $menus->menu('main');
        $menu->link('https://example.com/foo', 'Foo')->id('foo');
    }
}
```

**Using the app on method**

You may add menu items only if the menus is requested from the app.

```php
use Tobento\App\Boot;
use Tobento\Service\Menu\MenusInterface;

class AnyServiceBoot extends Boot
{
    public function boot()
    {
        $this->app->on(MenusInterface::class, function(MenusInterface $menus) {
            $menu = $menus->menu('main');
            $menu->link('https://example.com/foo', 'Foo')->id('foo');
        });
    }
}
```

## Form Boot

The form boot does the following:

* Implements [FormFactoryInterface](https://github.com/tobento-ch/service-form#form-factory)
* Adds form view macro
* Adds VerifyCsrfToken middleware

This boot requires the [app-http](https://github.com/tobento-ch/app-http) bundle:

```
composer require tobento/app-http
```

```php
use Tobento\App\AppFactory;
use Tobento\Service\Form\FormFactoryInterface;
use Tobento\Service\Form\Form;
use Tobento\Service\View\ViewInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'vendor', 'vendor')
    ->dir($app->dir('app').'views', 'views', group: 'views')
    ->dir($app->dir('root').'public', 'public');
    
// Adding boots
$app->boot(\Tobento\App\View\Boot\View::class);
$app->boot(\Tobento\App\View\Boot\Form::class);
$app->booting();

// Get the form factory:
$formFactory = $app->get(FormFactoryInterface::class);

// View form macro:
$view = $app->get(ViewInterface::class);

$form = $view->form();
// var_dump($form instanceof Form);
// bool(true)

// Run the app
$app->run();
```

Check out the [Form Service](https://github.com/tobento-ch/service-form) to learn more about it in general.

You might boot the [App Http - Error Handler Boot](https://github.com/tobento-ch/app-http#error-handler-boot) which already handles exceptions caused by the form.

### Form Messages

As ```Tobento\Service\Form\ResponserFormFactory::class``` is the default ```Tobento\Service\Form\FormFactoryInterface::class``` implementation you can pass messages to your form fields by the following way:

```php
use Tobento\Service\Responser\ResponserInterface;
use Tobento\Service\Requester\RequesterInterface;
use Psr\Http\Message\ResponseInterface;

class RegisterController
{
    public function index(ResponserInterface $responser): ResponseInterface
    {        
        // set the key corresponding to your form field name:
        $responser->messages()->add('info', 'Some info message', key: 'firstname');

        return $responser->render(view: 'register');
    }
    
    public function register(
        RequesterInterface $requester,
        ResponserInterface $responser,
    ): ResponseInterface {
        // validate request data:
        //$requester->input();
        
        // add message on error:
        $responser->messages()->add('error', 'Message error', key: 'firstname');
        
        // redirect - messages and input data will be flashed:
        return $responser->redirect(
            uri: 'uri',
        )->withInput($requester->input()->all());
    }    
}
```

In your view file:

```php
<?php $form = $view->form(); ?>
<?= $form->form() ?>
<?= $form->input(
    type: 'text',
    name: 'firstname',
) ?>
<?= $form->button('Register') ?>
<?= $form->close() ?>
```

## Messages Boot

```php
// ...
$app->boot(\Tobento\App\View\Boot\Messages::class);
// ...
```

The messages boot does the following:

Renders the passed view messages if they are an instance of ```Tobento\Service\Message\MessagesInterface```:

```php
use Tobento\Service\Message\MessagesInterface;
use Tobento\Service\Message\Messages;

class SomeHttpController
{
    public function index(ViewInterface $view): string
    {
        $messages = new Messages();
        $messages->add(level: 'error', message: 'Error message');

        return $view->render(
            view: 'register',
            data: [
                'messages' => $messages,
            ],
        );
    }
}
```

Check out the [Message Service](https://github.com/tobento-ch/service-message) to learn more about it in general.

**Responser messages**

Renders the message from the responser, if you have booted the [App Http - Requester And Responser](https://github.com/tobento-ch/app-http#requester-and-responser-boot) booted.

```php
use Tobento\Service\Responser\ResponserInterface;
use Psr\Http\Message\ResponseInterface;

class SomeHttpController
{
    public function index(ResponserInterface $responser): ResponseInterface
    {
        $responser->messages()->add('info', 'Message info');
        $responser->messages()->add('success', 'Message success');
        $responser->messages()->add('error', 'Message error');
        $responser->messages()->add('warning', 'Message warning');
        
        // if a key is specified, the message will not be rendered,
        // as it may belong to a form field.
        $responser->messages()->add('error', 'Message error', key: 'firstname');

        return $responser->render(view: 'register');
    }
}
```

**View files**

In your view files, render the messages view:

```php
<?= $view->render('inc.messages') ?>
```

## Breadcrumb Boot

```php
// ...
$app->boot(\Tobento\App\View\Boot\Breadcrumb::class);
// ...
```

The breadcrumb boot does the following:

Adds the breadcrumb view and creates breadcrumb menu based on the main menu using the [global view data](#global-view-data-and-variables) ```routeName``` to determine the active menu tree.

**View files**

In your view files, render the breadcrumb view:

```php
<?= $view->render('inc.breadcrumb') ?>
```

## Table Boot

The table boot does the following:

* Adds table.css to your specified public css directory ```public/css/table.css```
* Adds table view macro

```php
// ...
$app->boot(\Tobento\App\View\Boot\Table::class);
// ...
```

**View file**

```php
<?php
$table = $view->table(name: 'demo');
$table->row([
    'title' => 'Title',
    'description' => 'Description',
])->heading();

$table->row([
    'title' => 'Lorem',
    'description' => 'Lorem ipsum',
]);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <title>Demo Table</title>
        
        <?= $view->assets()->render() ?>
        
        <?php
        // add table.css
        $view->asset('css/table.css');
        ?>
    </head>
    <body>
        <?= $table ?>
    </body>
</html>
```

Check out the [Table Service](https://github.com/tobento-ch/service-table) to learn more about it in general.

## Default Layout

### Views

The default layout uses the ```public/css/app.css``` and the [Basis Css](https://github.com/tobento-ch/css-basis) ```public/css/basis.css``` to style the view files.

A view file using the view boots may look like: 

```php
<!DOCTYPE html>
<html lang="<?= $view->esc($view->get('htmlLang', 'en')) ?>">
	
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <title>Demo View</title>
        
        <?= $view->render('inc/head') ?>
        <?= $view->assets()->render() ?>
    </head>
    
    <body<?= $view->tagAttributes('body')->add('class', 'page')->render() ?>>

        <?= $view->render('inc/header') ?>
        <?= $view->render('inc/nav') ?>

        <main class="page-main">

            <?= $view->render('inc.breadcrumb') ?>
            <?= $view->render('inc.messages') ?>

            <h1 class="text-xl">Demo View</h1>

        </main>

        <?= $view->render('inc/footer') ?>
    </body>
</html>
```

It is not recommended to alter existing views and assets as any app update may overwrite these files. Instead create a [Theme](#themes).

### Exception Views

The following exception views are available in your view directory:

* ```exception/error.php```
* ```exception/error.xml.php```

You might install and boot the [App Http - Error Handler Boot](https://github.com/tobento-ch/app-http#error-handler-boot) which will use these views to render html and xml excpetions.

**Example using the responser to render an exception view**

If you have booted the [App Http - Requester And Responser](https://github.com/tobento-ch/app-http#requester-and-responser-boot) boot, you may use the ```render``` method:

```php
use Tobento\Service\Responser\ResponserInterface;
use Psr\Http\Message\ResponseInterface;

class SomeHttpController
{
    public function index(ResponserInterface $responser): ResponseInterface
    {
        return $responser->render(
            view: 'exception/error',
            data: [
                'code' => '403',
                'message' => 'Forbidden',
            ],
            code: 403,
        );
    }
}
```

## Themes

### Theme Views

You may create a "theme" to customize existing views and assets, otherwise any app update may overwrite these files.

First, create a theme boot:

```php
use Tobento\App\Boot;

class SomeThemeBoot extends Boot
{
    public function boot()
    {
        // add a new view directory to load views from
        // with a higher priority as the default.

        $this->app->dirs()->dir(
            dir: $this->app->dir('app').'/theme/',
            name: 'theme',
            group: 'views',
            priority: 500, // default is 100
        );
    }
}
```

Next, you just place the view file you want to customize in your specified directory. If the view file does not exist, it uses the default view file.

### Theme Assets

You can handle your custom assets in the following ways:

**Replacing assets**

In your customized views you may just replace the assets by your custom assets:

```php
$view->asset('css/my-app.css');
```

**Using an asset handler**

You may create an asset handler to minify, combine or replace assets.

```php
use Tobento\App\Boot;
use Tobento\Service\View\ViewInterface;
use Tobento\Service\View\AssetsHandlerInterface;

class SomeThemeBoot extends Boot
{
    public function boot()
    {
        $this->app->on(ViewInterface::class, function(ViewInterface $view) {
            
            $view->assets()->setAssetsHandler(
                assetsHandler: $assetsHandler, // AssetsHandlerInterface
            );
        });
    }
}
```

A default asset handler is in development to to minify, combine or replace assets!

# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)