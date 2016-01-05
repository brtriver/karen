<?php
require __DIR__ . '/../../vendor/autoload.php';

$request = Zend\Diactoros\ServerRequestFactory::fromGlobals();
$response = new Zend\Diactoros\Response();

$app = new class($request, $response) extends Karen\Framework\Karen2 {
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
