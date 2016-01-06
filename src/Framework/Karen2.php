<?php
namespace Karen\Framework;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Pimple\Container;
use Karen\Application;
use Karen\Controller;
use Karen\Templatable;

class Karen2 extends Application
{
    protected $c;
    protected $route;

    public function container()
    {
        $this->c = new Container();
        $this->c['controller'] = new Controller();
        $this->c['dispatcher'] = function ($c) {
            return \FastRoute\simpleDispatcher($c['handlers']);
        };
    }

    public function route()
    {
        $this->c['handlers'] = function () {
            return $this->handlers();
        };
        $dispatcher = $this->c['dispatcher'];
        $this->route = $dispatcher->dispatch($this->request->getMethod(), $this->request->getUri()->getPath());
    }

    public function response()
    {
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
}
