<?php
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
