<?php

namespace App\Http\Middleware;

use App\Support\Modules\ModuleManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function __construct(
        private readonly ModuleManager $modules,
    ) {}

    public function handle(Request $request, Closure $next, string $module): Response
    {
        $this->modules->require($module);

        return $next($request);
    }
}