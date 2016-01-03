<?php
namespace Karen;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Controller
{
    protected $request;
    protected $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function __invoke($method, $args)
    {
        return call_user_func($method, $args, $this);
    }

    public function render($output)
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
}
