<?php

use CodeIgniter\Test\CIUnitTestCase;

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
}
