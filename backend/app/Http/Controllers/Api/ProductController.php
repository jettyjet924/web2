<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/products",
     *     summary="List and filter catalog products",
     *     description="Retrieve paginated products with filtering by category, price bounds, keyword search, and sorting.",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Keyword matching title, sku, or description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by MongoDB Category ID",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Minimum price",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="Maximum price",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="featured",
     *         in="query",
     *         description="Filter featured items",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort field (price_asc, price_desc, newest)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"price_asc", "price_desc", "newest"}, default="newest")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of products per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=12)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        if ($request->has('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'newest');
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $perPage = (int) $request->input('per_page', 12);
        $products = $query->paginate($perPage);

        return $this->successResponse($products, 'Products retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/products",
     *     summary="Create product (Admin only)",
     *     description="Add a new catalog item with stock and pricing details.",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","sku","price","stock","category_id"},
     *             @OA\Property(property="name", type="string", example="MacBook Pro 16 M3 Max"),
     *             @OA\Property(property="sku", type="string", example="MBP-16-M3-001"),
     *             @OA\Property(property="description", type="string", example="Apple M3 Max chip, 36GB Unified Memory, 1TB SSD"),
     *             @OA\Property(property="price", type="number", format="float", example=3499.99),
     *             @OA\Property(property="stock", type="integer", example=25),
     *             @OA\Property(property="category_id", type="string", example="65e8a9d123456789abcdef01"),
     *             @OA\Property(property="images", type="array", @OA\Items(type="string", example="https://images.unsplash.com/photo-1517336714731-489689fd1ca8")),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string", example="apple")),
     *             @OA\Property(property="is_featured", type="boolean", example=true),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_featured'] = $data['is_featured'] ?? false;

        $product = Product::create($data);

        AuditLog::record($request->user()->id, 'product_created', 'Product', $product->id, [
            'sku' => $product->sku,
            'name' => $product->name,
            'price' => $product->price,
        ]);

        return $this->successResponse($product, 'Product created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/products/{id}",
     *     summary="Get single product details",
     *     description="Retrieve detailed product record by ID.",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function show($id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }

        return $this->successResponse($product, 'Product retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/products/{id}",
     *     summary="Update product details (Admin only)",
     *     description="Update all or partial attributes of an existing product.",
     *     tags={"Products"},
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
     *             @OA\Property(property="name", type="string", example="MacBook Pro 16 M3 Max - Space Black"),
     *             @OA\Property(property="price", type="number", example=3399.99),
     *             @OA\Property(property="stock", type="integer", example=30),
     *             @OA\Property(property="is_featured", type="boolean", example=true),
     *             @OA\Property(property="category_id", type="string", example="6aa5e45b803cf7ec42042050")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function update(ProductRequest $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }

        $data = $request->validated();
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $product->update($data);

        AuditLog::record($request->user()->id, 'product_updated', 'Product', $product->id, $data);

        return $this->successResponse($product, 'Product updated successfully');
    }

    /**
     * @OA\Patch(
     *     path="/products/{id}/stock",
     *     summary="Quick stock adjustment (Admin only)",
     *     description="Directly increment, decrement, or override inventory quantities.",
     *     tags={"Products"},
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
     *             required={"stock"},
     *             @OA\Property(property="stock", type="integer", example=50),
     *             @OA\Property(property="operation", type="string", enum={"set", "increment", "decrement"}, default="set")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock level updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function updateStock(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }

        $validated = $request->validate([
            'stock' => 'required|integer|min:0',
            'operation' => 'nullable|in:set,increment,decrement',
        ]);

        $operation = $validated['operation'] ?? 'set';
        $quantity = (int) $validated['stock'];

        if ($operation === 'increment') {
            $product->stock += $quantity;
        } elseif ($operation === 'decrement') {
            $product->stock = max(0, $product->stock - $quantity);
        } else {
            $product->stock = $quantity;
        }

        $product->save();

        AuditLog::record($request->user()->id, 'stock_adjusted', 'Product', $product->id, [
            'operation' => $operation,
            'quantity' => $quantity,
            'new_stock' => $product->stock,
        ]);

        return $this->successResponse($product, 'Stock inventory updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/products/{id}",
     *     summary="Delete product (Admin only)",
     *     description="Remove product from catalog.",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function destroy(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }

        $product->delete();

        AuditLog::record($request->user()->id, 'product_deleted', 'Product', $id);

        return $this->successResponse(null, 'Product deleted successfully');
    }
}

