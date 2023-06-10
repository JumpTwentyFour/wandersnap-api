<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class TokenGenerationException extends Exception
{
    /**
     * @param Request $request
     */
    public function render($request): JsonResponse
    {
        return new JsonResponse(
            [
                'error' => 'There was an error registering the user with the provided details',
                'status_code' => Response::HTTP_BAD_REQUEST,
            ],
            Response::HTTP_BAD_REQUEST,
        );
    }
}
