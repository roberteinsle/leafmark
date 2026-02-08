<?php

namespace Tests\Unit\Models;

use App\Models\SystemSetting;
use Tests\TestCase;

class SystemSettingTest extends TestCase
{
    public function test_get_returns_default_when_key_missing(): void
    {
        $this->assertEquals('default', SystemSetting::get('nonexistent', 'default'));
    }

    public function test_set_creates_and_updates(): void
    {
        SystemSetting::set('test_key', 'value1');
        $this->assertEquals('value1', SystemSetting::get('test_key'));

        SystemSetting::set('test_key', 'value2');
        $this->assertEquals('value2', SystemSetting::get('test_key'));
    }

    public function test_is_registration_enabled_default_true(): void
    {
        $this->assertTrue(SystemSetting::isRegistrationEnabled());
    }

    public function test_is_email_domain_allowed(): void
    {
        SystemSetting::set('allowed_email_domains', 'example.com,test.org');

        $this->assertTrue(SystemSetting::isEmailDomainAllowed('user@example.com'));
        $this->assertTrue(SystemSetting::isEmailDomainAllowed('user@test.org'));
        $this->assertFalse(SystemSetting::isEmailDomainAllowed('user@other.com'));
    }

    public function test_is_email_domain_allowed_returns_true_when_no_domains(): void
    {
        $this->assertTrue(SystemSetting::isEmailDomainAllowed('anyone@anywhere.com'));
    }
}
