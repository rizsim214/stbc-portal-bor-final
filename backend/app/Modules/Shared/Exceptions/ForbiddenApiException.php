<?php

namespace App\Modules\Shared\Exceptions;

class ForbiddenApiException extends ApiException
{
    public function __construct(string $message = 'You do not have the required role.')
    {
        parent::__construct(
            message: $message,
            statusCode: 403,
            errorCode: 'FORBIDDEN',
        );
    }
}

