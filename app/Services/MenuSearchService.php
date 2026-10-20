<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class MenuSearchService
{
    /**
     * Cache TTL in seconds (1 hour).
     */
    protected const CACHE_TTL = 3600;

    /**
     * Retrieve authorized menu registry for the given user, cached for instant retrieval.
     *
     * @param User|null $user
     * @return array
     */
    public function getAuthorizedMenus(?User $user = null): array
    {
        if (!$user) {
            return [];
        }

        $cacheKey = "user_{$user->id}_menu_search_registry";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return $this->buildAuthorizedMenus($user);
        });
    }

    /**
     * Build the filtered array of menu items based on the user's roles and permissions.
     *
     * @param User $user
     * @return array
     */
    public function buildAuthorizedMenus(User $user): array
    {
        $allMenus = config('menu_search', []);
        $authorized = [];

        // Check if user is Super Admin or Admin role (full bypass like CheckPermission middleware)
        $isAdmin = $user->hasRole('Admin') || $user->can('superadmin');

        foreach ($allMenus as $item) {
            // 1. Enforce Role & Permission Authorization
            if (!$isAdmin && empty($item['public'])) {
                $hasAccess = false;

                // Check roles if specified
                if (!empty($item['roles']) && is_array($item['roles'])) {
                    foreach ($item['roles'] as $role) {
                        if ($user->hasRole($role)) {
                            $hasAccess = true;
                            break;
                        }
                    }
                }

                // Check permissions if not already granted by role
                if (!$hasAccess && !empty($item['permissions']) && is_array($item['permissions'])) {
                    foreach ($item['permissions'] as $perm) {
                        if ($user->can($perm)) {
                            $hasAccess = true;
                            break;
                        }
                    }
                }

                // If user doesn't meet authorization criteria, NEVER expose this menu
                if (!$hasAccess) {
                    continue;
                }
            }

            // 2. Validate route existence in Laravel
            $routeName = $item['route'] ?? '';
            if (empty($routeName) || !Route::has($routeName)) {
                continue;
            }

            try {
                $url = route($routeName);
            } catch (\Throwable $e) {
                // If route requires mandatory parameter that wasn't supplied, safely skip
                continue;
            }

            // 3. Append authorized payload
            $authorized[] = [
                'id' => $item['id'],
                'title' => $item['title'],
                'category' => $item['category'] ?? 'mixed',
                'group' => $item['group'] ?? 'General',
                'url' => $url,
                'icon' => $item['icon'] ?? 'fas fa-circle',
                'color' => $item['color'] ?? '#38bdf8',
                'description' => $item['description'] ?? '',
                'keywords' => $item['keywords'] ?? '',
            ];
        }

        return $authorized;
    }

    /**
     * Clear menu search cache for a user or all users.
     *
     * @param User|null $user
     * @return void
     */
    public function clearUserCache(?User $user = null): void
    {
        if ($user) {
            Cache::forget("user_{$user->id}_menu_search_registry");
        }
    }
}
