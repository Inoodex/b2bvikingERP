<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\MenuSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuSearchController extends Controller
{
    /**
     * Return authorized menu search registry for the authenticated session user.
     *
     * @param Request $request
     * @param MenuSearchService $menuSearchService
     * @return JsonResponse
     */
    public function registry(Request $request, MenuSearchService $menuSearchService): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated',
                'data' => [],
            ], 401);
        }

        $menus = $menuSearchService->getAuthorizedMenus($user);

        return response()->json([
            'status' => 'success',
            'data' => $menus,
            'count' => count($menus),
        ]);
    }

    /**
     * Invalidate and refresh menu cache for current user.
     *
     * @param Request $request
     * @param MenuSearchService $menuSearchService
     * @return JsonResponse
     */
    public function clearCache(Request $request, MenuSearchService $menuSearchService): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $menuSearchService->clearUserCache($user);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Menu search cache cleared successfully.',
        ]);
    }
}
