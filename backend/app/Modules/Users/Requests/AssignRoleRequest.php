<?php

namespace App\Modules\Users\Requests;

use App\Modules\Users\DTOs\AssignRoleDTO;
use Illuminate\Foundation\Http\FormRequest;

class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ];
    }

    public function toDTO(): AssignRoleDTO
    {
        return new AssignRoleDTO(
            userId: (int) $this->route('user'),
            roleId: (int) $this->integer('role_id'),
        );
    }
}
