<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\StoreMenuRequest;
use App\Http\Requests\CMS\UpdateMenuRequest;
use App\Http\Resources\CMS\MenuResource;
use App\Models\CMS\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MenuController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Menu::with('items.children');

        if ($request->has('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->has('location')) {
            $query->byLocation($request->location);
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $menus = $query->get();

        return MenuResource::collection($menus);
    }

    public function store(StoreMenuRequest $request): MenuResource
    {
        $menu = Menu::create($request->validated());

        return new MenuResource($menu->load('items.children'));
    }

    public function show(Menu $menu): MenuResource
    {
        return new MenuResource($menu->load('items.children'));
    }

    public function update(UpdateMenuRequest $request, Menu $menu): MenuResource
    {
        $menu->update($request->validated());

        return new MenuResource($menu->load('items.children'));
    }

    public function destroy(Menu $menu): JsonResponse
    {
        $menu->delete();

        return response()->json(['message' => 'Menu deleted successfully']);
    }
}
