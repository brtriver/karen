<?php
namespace Karen;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

interface ApplicationInterface
{
    public function __construct(Request $request = null, Response $response = null);
    public function addQueue(string $name, callable $callable);
    public function getQueues(): array;
    public function run();
    public function sendResponse();
}
