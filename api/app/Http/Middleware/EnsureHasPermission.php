<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $resource): Response
    {
        $requiredPermission = $this->getPermissionName($request, $resource);

        if (!auth()->user()?->can($requiredPermission)) {
            return ApiResponse::permissionDenied();
        }

        return $next($request);
    }

    protected function getPermissionName(Request $request, string $resource): string
    {
        $method = $request->route()->getActionMethod();

        return match ($method) {
            'index' => "viewAny-$resource",
            'show' => "view-$resource",
            'store' => "create-$resource",
            'update' => "edit-$resource",
            'destroy' => "delete-$resource",
            default => "$method-$resource"
        };
    }
}
