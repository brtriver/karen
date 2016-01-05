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
