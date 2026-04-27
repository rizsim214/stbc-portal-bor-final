<?php

namespace App\Modules\LabResults\Requests;

use App\Modules\LabResults\DTOs\StoreLabResultDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreLabResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['required', 'integer', 'exists:appointments,id'],
            'file_key' => ['nullable', 'string', 'max:2048'],
            // Backward-compatible field name; prefer file_key moving forward.
            'file_path' => ['nullable', 'string', 'max:2048'],
            'result_data' => ['nullable', 'array'],
            'released_at' => ['nullable', 'date'],
        ];
    }

    public function toDTO(): StoreLabResultDTO
    {
        return new StoreLabResultDTO(
            appointmentId: (int) $this->integer('appointment_id'),
            fileKey: $this->resolveFileKey(),
            resultData: $this->has('result_data') ? $this->input('result_data') : null,
            releasedAt: $this->filled('released_at') ? (string) $this->string('released_at') : null,
        );
    }

    private function resolveFileKey(): ?string
    {
        if ($this->filled('file_key')) {
            return (string) $this->string('file_key');
        }

        if ($this->filled('file_path')) {
            return (string) $this->string('file_path');
        }

        return null;
    }
}
