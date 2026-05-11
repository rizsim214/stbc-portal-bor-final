<?php

namespace App\Modules\LabResults\Requests;

use App\Modules\LabResults\DTOs\GenerateLabResultUploadUrlDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateLabResultUploadUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['required', 'integer', 'exists:appointments,id'],
            'file_name' => ['required', 'string', 'max:255', 'not_regex:/[\/\\\\]/'],
            'content_type' => [
                'required',
                'string',
                Rule::in(config('lab_results.allowed_content_types', [])),
            ],
            'size_bytes' => [
                'required',
                'integer',
                'min:1',
                'max:'.(int) config('lab_results.max_file_size_bytes', 10485760),
            ],
        ];
    }

    public function toDTO(): GenerateLabResultUploadUrlDTO
    {
        return new GenerateLabResultUploadUrlDTO(
            appointmentId: (int) $this->integer('appointment_id'),
            fileName: (string) $this->string('file_name'),
            contentType: (string) $this->string('content_type'),
            sizeBytes: (int) $this->integer('size_bytes'),
        );
    }
}
