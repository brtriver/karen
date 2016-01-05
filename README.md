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
If try FastRoute version, open `http://localhost:8888/karen2/hello/karen_girls`

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

this abstract application knows about Request, Response, MiddlewareBulder(relay), so you have to write your application logic in methods(container, middleware, route and response methods) of your extends application class. these methods are executed by `run()` method.

## method

### container

create your container and set necessary object in your container:

```php
    public function container()
    {
        $this->c = new Container();
        $this->c['template'] = function($c) {
            $loader = new \Twig_Loader_Filesystem( __DIR__ . '/../../templates');
            return new \Twig_Environment($loader, array(
                'cache' => '/tmp/',
            ));
        };
    }
```

Karen use pimple, but you can change it.

### middleware (option)

Middleware method is to add your middleware through `$this->addQueue()` method as needed.
Karen use (Relay)[http://relayphp.com/], so you have to pass callabe following signature:
```php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;

function (
    Request $request,   // the request
    Response $response, // the response
    callable $next      // the next middleware
) {
    // ...
}
```

This is a sample to change status by query parameter(status):

```php
    public function middleware()
    {
        // if open http://localhost:8888/hello/ssss?status=404, response status is set to 404
        $this->addQueue('changeStatus', function (Request $request, Response $response, callable $next) {
            $response = $next($request, $response);
            $status = $request->getQueryParams()['status']?? null;
            if ($status) {
                $response = $response->withStatus((int)$status);
            }

            return $response;
        });
    }
```

### route

Route method is to define you application routes and controller logics:

```php
    public function route()
    {
        $map = $this->c['router']->getMap();
        // define routes at an action method in an extended class
        $map->get('hello', '/hello/{name}', function($args, $controller) {
            $name = $args['name']?? 'karen';
                return $controller->render('[Karen] Hello, ' . $name);
            })->tokens(['name' => '.*']);
        $this->route = $this->c['router']->getMatcher()->match($this->request);;
    }
```

or if you use anonymous class, you can your logic to this class:

* route method:
```php
    public function route()
    {
        $map = $this->c['router']->getMap();
        // define routes at an action method in an extended class
        $map = $this->action($map);
        $this->route = $this->c['router']->getMatcher()->match($this->request);;
    }
```

* anonymous class:
```php
$app = new class extends Karen\Framework\Karen {
        public function action($map)
        {
            // hello name controller sample.
            $map->get('hello', '/hello/{name}', function($args, $controller) {
                $name = $args['name']?? 'karen';
                return $controller->render('[Karen] Hello, ' . $name);
            })->tokens(['name' => '.*']);

            return $map;
        }
    };

```

If you change Aura.Route to another, read `src/Framework/Karen2.php`

### response

In response method, you have to pass your controller callable handler to  `$this->addQueue('action', $handler, $args)`.
it is different how to get `$handler`, because it depends on your selected router library.

* Aura.Router version (Karen.php)
```php
    public function response(){
        if (!$this->route) {
            $response =$this->response->withStatus(404);
            $response->getBody()->write('not found');
            return;
        }

        // parse args
        $args = [];
        foreach ((array)$this->route->attributes as $key => $val) {
            $args[$key] = $val;
        }
        // add route action to the queue of Midlleware
        $this->addQueue('action', $this->c['controller']->actionQueue($this->route->handler, $args));
    }
```

* FastRoute version (Karen2.php)
```php
    public function response(){
        switch ($this->route[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                echo "Not Found\n";
                break;
            case \FastRoute\Dispatcher::FOUND:
                $handler = $this->route[1];
                $args = $this->route[2];
                $this->addQueue('action', $this->c['controller']->actionQueue($handler, $args));
                break;
            default:
                throw new \LogicException('Should not reach this point');
        }
    }
```

After execute this response method, queue in middleware are executed automatically.

### Your Own Framework

Within this pattern, you have only to implement your framework logic in these methods.

```php
$app = new YourApplication(); // extends Karen\Application
$app->run();
$app->sendResponse();
```


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

Feel free to create your own framework for PHP7.

License
-------

Karen is licensed under the MIT license.


