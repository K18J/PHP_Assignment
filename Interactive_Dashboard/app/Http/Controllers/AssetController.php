<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\Contracts\AssetServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    private $assetService;

    public function __construct(AssetServiceInterface $assetService)
    {
        $this->assetService = $assetService;
    }

    public function index(): View
    {
        return view('dashboard');
    }

    public function list(): JsonResponse
    {
        $assets = $this->assetService->getAll();

        $data = $assets->map(function ($asset) {
            return [
                'id' => $asset->id,
                'name' => $asset->name,
                'serial_number' => $asset->serial_number,
                'category_id' => $asset->category_id,
                'category_name' => $asset->category->name ?? '',
                'value' => (float) $asset->value,
                'status' => $asset->status,
                'description' => $asset->description,
                'purchase_date' => $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : null,
                'created_at' => $asset->created_at->toIso8601String(),
            ];
        });
        return response()->json($data);
    }

    public function show(int $id): JsonResponse
    {
        $asset = $this->assetService->getById($id);

        if (!$asset) {
            return response()->json(['message' => 'Asset not found'], 404);
        }

        return response()->json([
            'id' => $asset->id,
            'name' => $asset->name,
            'serial_number' => $asset->serial_number,
            'category_id' => $asset->category_id,
            'category_name' => $asset->category->name ?? '',
            'value' => (float) $asset->value,
            'status' => $asset->status,
            'description' => $asset->description,
            'purchase_date' => $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : null,
            'created_at' => $asset->created_at->toIso8601String(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->assetService->validate($request->all());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $asset = $this->assetService->create($request->all());
        return response()->json([
            'message' => 'Asset created successfully',
            'asset' => [
                'id' => $asset->id,
                'name' => $asset->name,
                'serial_number' => $asset->serial_number,
                'category_id' => $asset->category_id,
                'category_name' => $asset->category->name ?? '',
                'value' => (float) $asset->value,
                'status' => $asset->status,
                'description' => $asset->description,
                'purchase_date' => $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : null,
                'created_at' => $asset->created_at->toIso8601String(),
            ],
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $asset = $this->assetService->getById($id);
        if (!$asset) {
            return response()->json(['message' => 'Asset not found'], 404);
        }
        $validator = $this->assetService->validate($request->all(), $id);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $asset = $this->assetService->update($id, $request->all());
        return response()->json([
            'message' => 'Asset updated successfully',
            'asset' => [
                'id' => $asset->id,
                'name' => $asset->name,
                'serial_number' => $asset->serial_number,
                'category_id' => $asset->category_id,
                'category_name' => $asset->category->name ?? '',
                'value' => (float) $asset->value,
                'status' => $asset->status,
                'description' => $asset->description,
                'purchase_date' => $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : null,
                'created_at' => $asset->created_at->toIso8601String(),
            ],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        if (!$this->assetService->getById($id)) {
            return response()->json(['message' => 'Asset not found'], 404);
        }
        $this->assetService->delete($id);
        return response()->json(['message' => 'Asset deleted successfully']);
    }

    public function categories(): JsonResponse
    {
        $categories = Category::orderBy('name')->get(['id', 'name', 'slug']);
        return response()->json($categories);
    }
}