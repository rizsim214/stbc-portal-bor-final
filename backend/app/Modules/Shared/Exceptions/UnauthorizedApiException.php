<?php

namespace App\Modules\Shared\Exceptions;

class UnauthorizedApiException extends ApiException
{
    public function __construct(string $message = 'Unauthenticated.')
    {
        parent::__construct(
            message: $message,
            statusCode: 401,
            errorCode: 'UNAUTHENTICATED',
        );
    }
}

