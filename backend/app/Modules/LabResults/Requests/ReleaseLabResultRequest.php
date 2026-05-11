<?php

namespace App\Modules\LabResults\Requests;

use App\Models\LabResult;
use App\Modules\LabResults\DTOs\ReleaseLabResultDTO;
use Illuminate\Foundation\Http\FormRequest;

class ReleaseLabResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'released_at' => ['nullable', 'date'],
        ];
    }

    public function toDTO(LabResult $labResult): ReleaseLabResultDTO
    {
        return new ReleaseLabResultDTO(
            labResultId: (int) $labResult->id,
            releasedAt: $this->filled('released_at') ? (string) $this->string('released_at') : null,
        );
    }
}

