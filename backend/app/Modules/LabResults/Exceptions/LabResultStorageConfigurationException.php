<?php

namespace App\Modules\LabResults\Exceptions;

use App\Modules\Shared\Exceptions\ApiException;

class LabResultStorageConfigurationException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            message: 'Lab result storage disk is misconfigured.',
            statusCode: 500,
            errorCode: 'LAB_RESULT_STORAGE_MISCONFIGURED',
        );
    }
}
