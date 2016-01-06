<?php
namespace Brtriver\Karen\Test;

use Karen\Controller;
use PHPUnit_Framework_Assert;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    private function getController()
    {
        return new Controller();
    }

    public function testRender()
    {
        $output = 'test string';

        $stream = $this->getMockBuilder('Psr\Http\Message\StreamInterface')->getMock();
        $stream->expects($this->once())
            ->method('write')
            ->with($output);

        $response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')
            ->getMock();
        $response->method('getBody')->willReturn($stream);

        $controller = $this->getController();
        $controller->response = $response;
        $returnedResponse = $controller->render($output);

        $this->assertSame($response, $returnedResponse);
    }

    public function testActionQueue()
    {
        $this->request = $this->getMockBuilder('Psr\Http\Message\ServerRequestInterface')
            ->getMock();
        $this->response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')
            ->getMock();

        $controller = $this->getController();
        $f = function($args, $controller){
            PHPUnit_Framework_Assert::assertSame(['name' => 'test'], $args);
            PHPUnit_Framework_Assert::assertSame($this->request, $controller->request);
            PHPUnit_Framework_Assert::assertSame($this->response, $controller->response);
        };
        $callable = $controller->actionQueue($f, ['name' => 'test']);


        $callable($this->request, $this->response);
    }
}
