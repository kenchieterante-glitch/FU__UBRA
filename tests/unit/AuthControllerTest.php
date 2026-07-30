<?php

use CodeIgniter\Log\Logger;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Logger as LoggerConfig;

final class AuthControllerTest extends CIUnitTestCase
{
    public function testAdminFallbackLoginWorks(): void
    {
        $controller = new \App\Controllers\AuthController();
        $request = service('request');
        $request->setGlobal('request', [
            'username' => 'admin',
            'password' => 'admin',
        ]);
        $request->setMethod('post');

        $controller->initController($request, service('response'), service('logger'));

        $response = $controller->attemptLogin();

        $this->assertNotNull($response);
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testAuthenticatedUserLoginRedirectsAndSetsNoStoreHeaders(): void
    {
        $session = service('session');
        $session->setLogger(new Logger(new LoggerConfig()));
        $session->start();
        $session->set('isLoggedIn', true);

        $controller = new \App\Controllers\AuthController();
        $request = service('request');
        $request->setMethod('get');
        $controller->initController($request, service('response'), service('logger'));

        $response = $controller->login();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('/dashboard', $response->getHeaderLine('Location'));
        $this->assertSame('no-store, max-age=0, no-cache', $response->getHeaderLine('Cache-Control'));
    }
}
