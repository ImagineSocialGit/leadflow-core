<?php

namespace Tests\Feature\Modules;

use App\Support\Modules\ModuleManager;
use Tests\TestCase;

class ModuleManagerTest extends TestCase
{
    public function test_core_is_always_enabled(): void
    {
        config()->set('modules.enabled', []);

        $modules = app(ModuleManager::class);

        $this->assertTrue($modules->enabled('core'));
    }

    public function test_enabled_module_returns_true(): void
    {
        config()->set('modules.enabled', ['crm', 'tasks']);

        $modules = app(ModuleManager::class);

        $this->assertTrue($modules->enabled('crm'));
        $this->assertTrue($modules->enabled('tasks'));
    }

    public function test_disabled_module_returns_false(): void
    {
        config()->set('modules.enabled', ['crm']);

        $modules = app(ModuleManager::class);

        $this->assertFalse($modules->enabled('webinars'));
    }

    public function test_module_enabled_helper_uses_module_manager(): void
    {
        config()->set('modules.enabled', ['tasks']);

        $this->assertTrue(module_enabled('tasks'));
        $this->assertFalse(module_enabled('webinars'));
    }
}