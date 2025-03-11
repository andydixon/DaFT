<?php

use PHPUnit\Framework\TestCase;
use DaFT\Router;
use DaFT\Helpers;

class RouterTest extends TestCase
{
    private $router;

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    public function testMapsStaticRouteToController()
    {
        $this->router->addRoute('GET', '/home', function() {
            return 'Home Page';
        });

        $result = $this->router->dispatch('GET', '/home');

        $this->assertEquals('Home Page', $result);
    }

    public function testHandlesDynamicParameters()
    {
        $this->router->addRoute('GET', '/user/{id}', function($id) {
            return "User ID: $id";
        });

        $result = $this->router->dispatch('GET', '/user/42');

        $this->assertEquals('User ID: 42', $result);
    }

    public function testReturns404ForUnknownRoute()
    {
        $result = $this->router->dispatch('GET', '/unknown');

        $this->assertEquals(404, $result['status']);
        $this->assertEquals('Not Found', $result['message']);
    }

    public function testMethodNotAllowed()
    {
        $this->router->addRoute('POST', '/submit', function() {
            return 'Form Submitted';
        });

        $result = $this->router->dispatch('GET', '/submit');

        $this->assertEquals(405, $result['status']);
        $this->assertEquals('Method Not Allowed', $result['message']);
    }
}
