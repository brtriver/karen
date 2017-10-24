<?php
namespace Brtriver\Karen\Test;

use Karen\Application;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    public function setUp()
    {
        $this->request = $this->getMockBuilder('Psr\Http\Message\ServerRequestInterface')
            ->getMock();
        $this->response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')
            ->getMock();
    }

    public function testConstructWithParams()
    {
        $app = $this->getMockForAbstractClass('Karen\Application', [$this->request, $this->response]);
        $this->assertSame($this->request, $app->request);
        $this->assertSame($this->response, $app->response);
    }

    public function testConstructWithoutParams()
    {
        $app = $this->getMockForAbstractClass('Karen\Application');
        $this->assertTrue($app->request instanceof \Zend\Diactoros\ServerRequest);
        $this->assertTrue($app->response instanceof \Zend\Diactoros\Response);
    }

    public function testQueue()
    {
        $app = $this->getMockForAbstractClass('Karen\Application');
        $f1 = function(){return 'first';};
        $f2 = function(){return 'second';};

        $app->addQueue('first', $f1);
        $app->addQueue('second', $f2);

        $this->assertSame(['first' => $f1, 'second' => $f2], $app->getQueues());
    }

    public function testApplyMiddleware()
    {
        $app = $this->getMockForAbstractClass('Karen\Application');
        $this->passed = '';

        $app->addQueue('first', function(Request $request, Response $response, callable $next) {
            $this->passed .= ' first in ->';
            $response = $next($request, $response);
            $this->passed .= ' first out ->';
            return $response;
        });

        $app->addQueue('second', function(Request $request, Response $response, callable $next) {
            $this->passed .= ' second in ->';
            $response = $next($request, $response);
            $this->passed .= ' second out ->';
            return $response;
        });

        $response = $app->applyMiddleware();

        $this->assertSame(' first in -> second in -> second out -> first out ->', $this->passed);
        $this->assertTrue($app->response instanceof \Zend\Diactoros\Response);
    }

    public function testRunOrder()
    {
        $app = new class extends Application{
                public $passed = '';
                public function container()
                {
                    $this->passed .= 'container->';
                }
                public function middleware()
                {
                    $this->passed .= 'middleware->';
                }
                public function route()
                {
                    $this->passed .= 'route->';
                }
                public function response()
                {
                    $this->passed .= 'response';
                }
            };
        $app->run();
        $this->assertSame('container->middleware->route->response', $app->passed);
    }
}
