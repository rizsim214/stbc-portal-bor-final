<?php

namespace App\Modules\Auth\Requests;

use App\Modules\Auth\DTOs\UpdateStaffStatusDTO;
use App\Modules\Auth\Enums\StaffStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'staff_status' => ['required', 'string', 'in:'.implode(',', StaffStatus::values())],
        ];
    }

    public function toDTO(): UpdateStaffStatusDTO
    {
        return new UpdateStaffStatusDTO(
            userId: (int) $this->user()->id,
            staffStatus: (string) $this->string('staff_status'),
        );
    }
}

