<?php
namespace Karen\Framework;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Pimple\Container;
use Aura\Router\RouterContainer;
use Karen\Application;
use Karen\Controller;
use Karen\Templatable;

class Karen extends Application
{
    protected $c;
    protected $route;

    public function container()
    {
        $this->c = new Container();
        $this->c['template'] = function ($c) {
            $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../../templates');
            return new \Twig_Environment($loader, array(
                'cache' => '/tmp/',
            ));
        };

        $this->c['controller'] = function ($c) {
            $controller = new class extends Controller
            {
                use Templatable;
            };
            $controller->setTemplate($c['template']);

            return $controller;
        };
        $this->c['router'] = new RouterContainer();
    }
    public function middleware()
    {
        // write your middleware
        $this->addQueue('changeStatus', function (Request $request, Response $response, callable $next) {
            $response = $next($request, $response);
            $status = $request->getQueryParams()['status']?? null;
            if ($status) {
                $response = $response->withStatus((int)$status);
            }

            return $response;
        });
    }

    public function route()
    {
        $map = $this->c['router']->getMap();
        // define routes at an action method in an extended class
        $map = $this->action($map);
        $this->route = $this->c['router']->getMatcher()->match($this->request);
        ;
    }

    public function response()
    {
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

    public function action($map)
    {
        return $map;
    }
}
