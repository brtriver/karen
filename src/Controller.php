<?php
namespace Karen;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Controller
{
    public $request;
    public $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function render(string $output): Response
    {
        $this->response->getBody()->write($output);

        return $this->response;
    }

    /**
     * get routing action queue of Middleware interface
     */
    public function actionQueue(callable $callable, array $args): callable
    {
        return function (Request $request, Response $response) use ($callable, $args){

            return call_user_func($callable, $args, $this);
        };
    }
}
