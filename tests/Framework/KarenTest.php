<?php
namespace Brtriver\Karen\Test\Framework;

use Karen\Framework\Karen;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface as Uri;
use Psr\Http\Message\ResponseInterface as Response;
use PHPUnit\Framework\TestCase;

class KarenTest extends TestCase
{
    public function setUp()
    {
        $this->request = $this->getMockBuilder('Psr\Http\Message\ServerRequestInterface')
            ->getMock();
        $this->response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')
            ->getMock();
    }

    private function getEmptyNext()
    {
        return function($request, $response) {
            return $response;
        };
    }

    public function testChangeStatusMiddleware()
    {
        $app = new Karen();
        $app->middleware();
        $q = $app->getQueues();

        $this->request->method('getQueryParams')->willReturn(['status' => 404]);
        $this->response->expects($this->once())->method('withStatus')->with(404);

        $q['changeStatus']($this->request, $this->response, $this->getEmptyNext());
    }

    public function testRoute()
    {
        $uri = $this->getMockBuilder('Psr\Http\Message\UriInterface')
            ->getMock();
        $uri->method('getPath')->willReturn('/hello');
        $this->request->expects($this->once())->method('getUri')->willReturn($uri);

        $app = new Karen($this->request, $this->response);
        $app->container();
        $app->route();
    }

    /**
     * @expectedException Exception
     */
    public function testRouteRearchAcitionMethod()
    {
        $app = new class($this->request, $this->response) extends Karen {
                public function action($map)
                {
                    throw new \Exception('should rearch this point');
                }
            };
        $app->container();
        $app->route();
    }


    public function testResponseNotFound()
    {
        $app = new Karen($this->request, $this->response);

        $stream = $this->getMockBuilder('Psr\Http\Message\StreamInterface')
            ->getMock();
        $stream->expects($this->once())->method('write')->with('not found');

        $this->response->expects($this->once())->method('withStatus')->with(404)->willReturn($this->response);
        $this->response->expects($this->once())->method('getBody')->willReturn($stream);

        $app->response();
    }
}
