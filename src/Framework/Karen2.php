<?php
namespace Karen\Framework;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Pimple\Container;
use Relay\RelayBuilder;
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
        $this->c['controller'] = new Controller($this->request, $this->response);
        $this->c['dispatcher'] = function($c) {
            return \FastRoute\simpleDispatcher($c['handlers']);
        };
    }
    public function middleware()
    {
    }

    public function route()
    {
        $this->c['handlers'] = function($c) {
            return $this->handlers();
        };
        $dispatcher = $this->c['dispatcher'];
        $serverParams = $this->request->getServerParams();
        $uri = $serverParams['REQUEST_URI'];
        $method = $serverParams['REQUEST_METHOD'];
        $this->route = $dispatcher->dispatch($method, $uri);
    }

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
                echo "unknown";
        }
        // apply middleware and get response
        $relayBuilder = new RelayBuilder();
        $relay = $relayBuilder->newInstance($this->getQueues());
        $this->response = $relay($this->request, $this->response);
    }
}
