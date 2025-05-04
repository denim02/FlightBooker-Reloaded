<?php

namespace App\Helpers;

use App\Traits\HasSorting;
use Illuminate\Http\JsonResponse;


enum ResponseCodes: int
{
    case SUCCESS = 1;
    case NO_CONTENT = 2;
    case RESOURCE_NOT_FOUND = 3;
    case VALIDATION_FAILED = 4;
    case CONFLICT = 5;
    case BAD_REQUEST = 6;
    case GENERAL_ERROR = 7;
    case PERMISSION_DENINED = 8;
    case NOT_AUTHENTICATED = 9;
    case CSRF_TOKEN_MISMATCH = 10;
}

class ApiResponse
{
    use HasSorting;

    protected static function responseToJson($message, $statusId, $data = []): array
    {
        return [
            'message' => $message,
            'status' => $statusId,
            'data' => $data,
        ];
    }

    public static function success($message = 'Success', $data = []): JsonResponse
    {
        $json = self::responseToJson($message, ResponseCodes::SUCCESS, $data);
        return response()->json($json, 200);
    }

    public static function noContent($message = 'No content', $status = ResponseCodes::NO_CONTENT): JsonResponse
    {
        $json = self::responseToJson($message, $status, []);
        return response()->json($json, 204);
    }

    public static function resourceNotFound($message = 'Resource not found', $status = ResponseCodes::RESOURCE_NOT_FOUND): JsonResponse
    {
        $json = self::responseToJson($message, $status, []);
        return response()->json($json, 404);
    }

    public static function validationFailed($error = null, $status = ResponseCodes::VALIDATION_FAILED, $messageOverride = null): JsonResponse
    {
        $message = $messageOverride ?? self::transformValidationMessages($error);

        $json = self::responseToJson($message, $status, $error);
        return response()->json($json, 422);
    }

    public static function conflict($message = 'This record already exists', $status = ResponseCodes::CONFLICT, $httpStatusCode = 409): JsonResponse
    {
        $json = self::responseToJson($message, $status, []);
        return response()->json($json, $httpStatusCode);
    }

    public static function badRequest($message, $status = ResponseCodes::BAD_REQUEST): JsonResponse
    {
        $json = self::responseToJson($message, $status, []);
        return response()->json($json, 400);
    }

    public static function generalError(
        ?string $message = 'Internal Server Error',
        ResponseCodes $status = ResponseCodes::GENERAL_ERROR,
    ): JsonResponse {
        $json = self::responseToJson($message, $status, []);
        return response()->json($json, 500);
    }

    public static function permissionDenied($message = 'You do not have permission to perform this action', $status = ResponseCodes::PERMISSION_DENINED): JsonResponse
    {
        $json = self::responseToJson($message, $status, []);
        return response()->json($json, 403);
    }

    public static function notAuthenticated($message = 'You must be authenticated to access this resource', $status = ResponseCodes::NOT_AUTHENTICATED): JsonResponse
    {
        $json = self::responseToJson($message, $status, []);
        return response()->json($json, 401);
    }

    protected static function transformValidationMessages($errors): string
    {
        $errorMessages = '';
        if (is_string($errors)) {
            return $errors;
        }
        $errorsList = is_array($errors) ? $errors : $errors->all();
        foreach ($errorsList as $error) {
            $errorMessages .= $error . "\n";
        }

        return $errorMessages;
    }

    public static function fetchResults($query)
    {
        $page = request()->get('page') ?? 1;
        $pageSize = request()->get('page-size') ?? 25;
        $sortBy = request()->get('sort-by');
        $sortDirection = request()->get('sort-direction', 'asc');

        $query = self::prepareSortingQuery($query, $sortBy, $sortDirection);

        return (request()->has('no-pagination') && (bool) request()->get('no-pagination')) ?
            $query->get() :
            $query->paginate(perPage: $pageSize, page: $page);
    }
}
