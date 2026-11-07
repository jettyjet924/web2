<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Laravel MongoDB Enterprise REST API",
 *     description="Production-grade RESTful API powered by Laravel, MongoDB, Role-Based Access Control, and OpenAPI 3.0 Documentation.",
 *     @OA\Contact(
 *         name="API Support Team",
 *         email="support@example.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Local Development API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Bearer",
 *     description="Enter JWT or Sanctum Bearer Token to access protected endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints for user registration, token login, logout, and profile management"
 * )
 * @OA\Tag(
 *     name="Users",
 *     description="Administrator user management and role assignment"
 * )
 * @OA\Tag(
 *     name="Categories",
 *     description="Product categorization and taxonomy management"
 * )
 * @OA\Tag(
 *     name="Products",
 *     description="Catalog items, pricing, inventory stock control, and filters"
 * )
 * @OA\Tag(
 *     name="Orders",
 *     description="Customer checkout, order tracking, and status transitions"
 * )
 * @OA\Tag(
 *     name="Dashboard & Audit",
 *     description="Administrator executive metrics, operational KPIs, and system audit logs"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Send standard success JSON response.
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Send standard error JSON response.
     */
    protected function errorResponse(string $message = 'An error occurred', int $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}

