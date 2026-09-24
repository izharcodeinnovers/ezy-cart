<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use App\Enums\UserStatus;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('customer.edit');
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $userId,
            'password' => $this->isMethod('POST') ? ['required', 'confirmed', Password::defaults()] : ['nullable', 'confirmed', Password::defaults()],
            'status' => 'required|in:' . implode(',', UserStatus::values()),
            'roles' => 'nullable|array',
            'roles.*' => 'string|exists:roles,name',
        ];
    }
}