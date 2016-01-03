<?php
namespace Karen;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Controller
{
    protected $request;
    protected $response;
    protected $queue = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function render(string $output)
    {
        $this->response->getBody()->write($output);

        return $this->response;
    }

    public static function sendResponse(Response $response)
    {
        header(sprintf(
            'HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        ));
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        if (!in_array($response->getStatusCode(), [204, 205, 304])) {
            echo $response->getBody();
        }
    }

    public function addQueue(string $name, callable $queue)
    {
        $this->queue[$name] = $queue;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * get routing action queue of Middleware interface
     */
    public function actionQueue(callable $callable, array $args)
    {
        return function (Request $request, Response $response) use ($callable, $args){

            return call_user_func($callable, $args, $this);
        };
    }
}
