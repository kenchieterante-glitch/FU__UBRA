<?php

use CodeIgniter\Test\CIUnitTestCase;

final class AuthViewTest extends CIUnitTestCase
{
    public function testLoginPageDoesNotRenderSidebar(): void
    {
        $output = view('auth/login', ['title' => 'Login']);

        $this->assertStringNotContainsString('<aside class="sidebar">', $output);
        $this->assertStringContainsString('Sign in to your account', $output);
    }
}
