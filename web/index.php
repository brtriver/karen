<?php
require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;

$request = Zend\Diactoros\ServerRequestFactory::fromGlobals();
$response = new Zend\Diactoros\Response();

$app = new class($request, $response) extends Karen\Framework\Karen {
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
