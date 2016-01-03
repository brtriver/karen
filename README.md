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

Karen uses blow components by default:

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
php composer.phar create-project brtriver/karen --stability=dev ./your_app
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

You have to write your logic of a controller with anonymous function:
```php
$map->get('hello', '/hello/{name}', function($args, $controller) {
    $name = $args['name']?? 'karen';
    return $controller->render('Hello, ' . $name);
})->tokens(['name' => '.*']);
```

`$args` is arguments from routing path,
and `$controller` is a instance of `Karen\Controller` class.

`Karen\Controller` has a `render` method. this is equal to `$controller->response->getBody()->write($output)`.

Extends Controller
------------------
For example, you want to render as simple plain:

```php
$c['controller'] = function($c) {
	return Controller($c['request'], $['response']);
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

License
-------

Karen is licensed under the MIT license.


