<?php
namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    /**
     * Success response.
     */
    protected function success(
        mixed $data = null,
        string $message = 'Request successful',
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'code'    => Response::HTTP_OK,
            'message' => $message,
            'data'    => $data,
            'errors'  => null,
        ], $code);
    }

    /**
     * Created response.
     */
    protected function created(
        mixed $data = null,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return $this->success($data, $message, 201);
    }

    /**
     * Paginated collection response.
     */
    protected function paginated(
        $data,
        string $message = 'Data retrieved successfully'
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'code'    => Response::HTTP_OK,
            'message' => $message,
            'data'    => $data,
            'errors'  => [],
        ]);
    }

    /**
     * Error response.
     */
    protected function error(
        string $message = 'An error occurred',
        int $code = 400,
        mixed $errors = null
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'code'    => Response::HTTP_OK,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ], $code);
    }

    /**
     * Not found response.
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Unauthorized response.
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }

    /**
     * Forbidden response.
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    /**
     * Unprocessable entity (validation failed).
     */
    protected function validationFailed(mixed $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * No content response.
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
