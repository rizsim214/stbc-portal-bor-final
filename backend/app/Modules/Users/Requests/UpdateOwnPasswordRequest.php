<?php

namespace App\Modules\Users\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

class UpdateOwnPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed', 'different:old_password'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $user = $this->user();

            if (!$user) {
                return;
            }

            if (!Hash::check((string) $this->string('old_password'), $user->password)) {
                $validator->errors()->add('old_password', 'Old password is incorrect.');
            }
        });
    }

    public function oldPassword(): string
    {
        return (string) $this->string('old_password');
    }

    public function newPassword(): string
    {
        return (string) $this->string('new_password');
    }
}
