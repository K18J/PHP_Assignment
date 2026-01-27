<?php

namespace Modules\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Cms\Entities\Page;

class PageController
{
    public function index(): JsonResponse
    {
        return response()->json(Page::all());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $page = Page::create($data);

        return response()->json($page, 201);
    }

    public function show(Page $page): JsonResponse
    {
        return response()->json($page);
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $page->update($data);

        return response()->json($page);
    }

    public function destroy(Page $page): JsonResponse
    {
        $page->delete();

        return response()->json(null, 204);
    }
}

