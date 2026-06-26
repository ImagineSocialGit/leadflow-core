<?php

namespace Tests\Feature\Modules;

use App\Support\Modules\ModuleManager;
use Illuminate\Support\Str;
use Tests\TestCase;

class ModuleDependencyBoundaryTest extends TestCase
{
    public function test_module_manager_expands_enabled_module_dependencies(): void
    {
        config()->set('modules.enabled', [
            'flow_routes',
            'inbound_messaging',
        ]);

        $enabled = app(ModuleManager::class)->enabledKeysWithDependencies();

        $this->assertContains('core', $enabled);
        $this->assertContains('workflow', $enabled);
        $this->assertContains('flow_routes', $enabled);
        $this->assertContains('messaging', $enabled);
        $this->assertContains('inbound_messaging', $enabled);

        $this->assertLessThan(
            array_search('flow_routes', $enabled, true),
            array_search('workflow', $enabled, true),
        );

        $this->assertLessThan(
            array_search('inbound_messaging', $enabled, true),
            array_search('messaging', $enabled, true),
        );
    }

    public function test_messaging_module_does_not_import_feature_modules(): void
    {
        $this->assertModuleDoesNotImport('Messaging', [
            'Campaigns',
            'FlowRoutes',
            'InboundMessaging',
            'InternalNotifications',
            'Mortgage',
            'Reporting',
            'Tasks',
            'Webinars',
            'Workflow',
        ]);
    }

    public function test_inbound_messaging_module_does_not_import_internal_notifications(): void
    {
        $this->assertModuleDoesNotImport('InboundMessaging', [
            'InternalNotifications',
        ]);
    }

    public function test_core_module_does_not_import_feature_modules(): void
    {
        $this->assertModuleDoesNotImport('Core', [
            'Campaigns',
            'FlowRoutes',
            'InboundMessaging',
            'InternalNotifications',
            'Messaging',
            'Mortgage',
            'Reporting',
            'Tasks',
            'Webinars',
        ]);
    }

    /**
     * @param  array<int, string>  $forbiddenModules
     */
    private function assertModuleDoesNotImport(string $module, array $forbiddenModules): void
    {
        $basePath = app_path("Modules/{$module}");

        $this->assertDirectoryExists($basePath);

        $violations = [];

        foreach ($this->phpFiles($basePath) as $file) {
            $contents = file_get_contents($file);

            if ($contents === false) {
                continue;
            }

            foreach ($forbiddenModules as $forbiddenModule) {
                $needle = "App\\Modules\\{$forbiddenModule}\\";

                if (! Str::contains($contents, $needle)) {
                    continue;
                }

                $violations[] = str_replace(base_path().'/', '', $file).' imports '.$needle;
            }
        }

        $this->assertSame([], $violations);
    }

    /**
     * @return array<int, string>
     */
    private function phpFiles(string $path): array
    {
        $files = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file instanceof \SplFileInfo) {
                continue;
            }

            if (! $file->isFile()) {
                continue;
            }

            if ($file->getExtension() !== 'php') {
                continue;
            }

            $files[] = $file->getPathname();
        }

        sort($files);

        return $files;
    }
}