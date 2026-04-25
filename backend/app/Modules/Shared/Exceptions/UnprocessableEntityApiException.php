<?php

namespace App\Modules\Shared\Exceptions;

class UnprocessableEntityApiException extends ApiException
{
    public function __construct(
        string $message,
        string $errorCode = 'UNPROCESSABLE_ENTITY',
    ) {
        parent::__construct(
            message: $message,
            statusCode: 422,
            errorCode: $errorCode,
        );
    }
}

