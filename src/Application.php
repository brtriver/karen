<?php
namespace Karen;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

abstract class Application implements ApplicationInterface
{
    public $request;
    public $response;
    public $queues = [];

    public function __construct(Request $request = null, Response $response = null)
    {
        $this->request = ($request)?: \Zend\Diactoros\ServerRequestFactory::fromGlobals();
        $this->response = ($response)?: new \Zend\Diactoros\Response();
    }

    public function addQueue(string $name, callable $callable)
    {
        $this->queues[$name] = $callable;
    }

    public function getQueues(): array
    {
        return $this->queues;
    }

    public abstract function container();
    public abstract function middleware();
    public abstract function route();
    public abstract function response();

    public function run()
    {
        $this->container();
        $this->middleware();
        $this->route();
        $this->response();
    }

    public function sendResponse()
    {
        header(sprintf(
            'HTTP/%s %s %s',
            $this->response->getProtocolVersion(),
            $this->response->getStatusCode(),
            $this->response->getReasonPhrase()
        ));
        foreach ($this->response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }
        if (!in_array($this->response->getStatusCode(), [204, 205, 304])) {
            echo $this->response->getBody();
        }
    }
}
