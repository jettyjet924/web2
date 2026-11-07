<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/categories",
     *     summary="List categories",
     *     description="Retrieve all product categories with optional hierarchy support.",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="active_only",
     *         in="query",
     *         description="Filter only active categories",
     *         required=false,
     *         @OA\Schema(type="boolean", default=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Category::with(['children', 'products']);

        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }

        $categories = $query->orderBy('name', 'asc')->get();

        return $this->successResponse($categories, 'Categories retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/categories",
     *     summary="Create category (Admin only)",
     *     description="Add a new category to MongoDB.",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Laptops & Computers"),
     *             @OA\Property(property="slug", type="string", example="laptops-computers"),
     *             @OA\Property(property="description", type="string", example="High-performance laptops and workstations"),
     *             @OA\Property(property="parent_id", type="string", nullable=true, example=null),
     *             @OA\Property(property="image_url", type="string", example="https://images.unsplash.com/photo-1517336714731-489689fd1ca8"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function store(CategoryRequest $request)
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $data['is_active'] = $data['is_active'] ?? true;

        $category = Category::create($data);

        AuditLog::record($request->user()->id, 'category_created', 'Category', $category->id, ['name' => $category->name]);

        return $this->successResponse($category, 'Category created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/categories/{id}",
     *     summary="Get category details",
     *     description="Retrieve category info along with nested products.",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function show($id)
    {
        $category = Category::with(['products', 'children'])->find($id);

        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        return $this->successResponse($category, 'Category retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/categories/{id}",
     *     summary="Update category (Admin only)",
     *     description="Modify category information.",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Ultrabooks"),
     *             @OA\Property(property="description", type="string", example="Slim and lightweight laptops"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function update(CategoryRequest $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        $data = $request->validated();
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        AuditLog::record($request->user()->id, 'category_updated', 'Category', $category->id, $data);

        return $this->successResponse($category, 'Category updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/categories/{id}",
     *     summary="Delete category (Admin only)",
     *     description="Remove category from MongoDB.",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function destroy(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        // Safeguard: Check if category has associated products
        if ($category->products()->count() > 0) {
            return $this->errorResponse('Cannot delete category with associated products. Reassign products first.', 400);
        }

        $category->delete();

        AuditLog::record($request->user()->id, 'category_deleted', 'Category', $id);

        return $this->successResponse(null, 'Category deleted successfully');
    }
}

