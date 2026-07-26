<?php

use CodeIgniter\Test\CIUnitTestCase;

final class SettingsViewTest extends CIUnitTestCase
{
    public function testSettingsViewRendersWithMinimalData(): void
    {
        $output = view('settings/index', [
            'title' => 'System Settings',
            'settings' => [],
            'users' => [],
            'logs' => [],
            'sys_info' => [],
            'flash_success' => null,
            'flash_error' => null,
        ]);

        $this->assertIsString($output);
        $this->assertStringContainsString('System Settings', $output);
        $this->assertStringContainsString('General Configuration', $output);
    }
}
