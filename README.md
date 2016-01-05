Karen -- PSR-7 micro framework with PHP7
==============================================

Karen is a simple PSR-7 micro framework with PHP7.
This framework provide these names of blocks and simple Controller class:

* container
* middleware
* routes
* response
* run

You have only to write a code in your way with PSR-7 objects.

Karen uses following components by default :

* PSR-7 Request, Response
  * zendframework/zend-diactoros
* middleware
  * relay/relay
* container
  * pimple/pimple
* aura/router
* twig/twig


Requirements
------------

* PHP 7.0 or later.

Install
-------
```bash
php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"
php composer.phar create-project brtriver/karen ./your_app
```

Demo
----
```bash
cd your_app
make server
```

and open `http://localhost:8888/hello/karen_girls` in your browser.

Usage
-----

see [web/index.php](https://github.com/brtriver/karen/blob/master/web/index.php).

```php
<?php
require __DIR__ . '/../vendor/autoload.php';

$app = new class extends Karen\Framework\Karen {
        public function action($map)
        {
            // hello name controller sample.
            $map->get('hello', '/hello/{name}', function($args, $controller) {
                $name = $args['name']?? 'karen';
                return $controller->render('[Karen] Hello, ' . $name);
            })->tokens(['name' => '.*']);

            // with twig
            $map->get('render_with_twig', '/template/{name}', function($args, $controller) {
                return $controller->renderWithT('demo.html', ['name' => $args['name']]);
            });

            return $map;
        }
    };

$app->run();
$app->sendResponse();
```

You have to write your logic of a controller with anonymous function:
```php
$map->get('hello', '/hello/{name}', function($args, $controller) {
    $name = $args['name']?? 'karen';
    return $controller->render('Hello, ' . $name);
})->tokens(['name' => '.*']);
```

If you write your application class and write logic there instead of anonymous class, it is to be a simple one:
```php
<?php
$app = new YourFramework(); // YourFramework class extends Application class and implement your logic.
$app->run();
$app->sendResponse();
```

`$args` is arguments from routing path,
and `$controller` is a instance of `Karen\Controller` class.

`Karen\Controller` has a `render` method. this is equal to `$controller->response->getBody()->write($output)`.

Extends Controller
------------------
For example, you want to render without a template engine:

```php
$c['controller'] = function($c) {
	return new Controller($c['request'], $['response']);
};
```

And if you want to use a template engine like Twig, you have only to write with anonymous class and trait:
```php
$c['controller'] = function($c) {
    $controller = new class($c['request'], $c['response']) extends Controller{
            use Templatable;
        };
    $controller->setTemplate($c['template']);

    return $controller;
};
```

Create your own framework
-------------------------

This microframework application is a simple template method pattern.
```php
    public function run()
    {
        $this->container();
        $this->middleware();
        $this->route();
        $this->response();
    }
```

and developers has only to call run like this:

```php
$app = new Application();
$app->run();
$app->sendResponse();
```

Within this pattern, you have only to implement your framework logic in these methods.
For example, Karen2 is a sample microframework with FastRoute instead of Aura.Router but you have only to call same methods.
see code `web/karen2/index.php` and `src/Framework/Karen2.php`

```php
require __DIR__ . '/../../vendor/autoload.php';

$app = new class extends Karen\Framework\Karen2 {
        public function handlers()
        {
            return function(FastRoute\RouteCollector $r) {
                $r->addRoute('GET', '/karen2/hello/{name}', function($args, $controller){
                    return $controller->render('[Karen2] Hello ' . $args['name']);
                });
            };
        }
    };

$app->run();
$app->sendResponse();
```

License
-------

Karen is licensed under the MIT license.


