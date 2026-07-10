<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHrProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->get('hr_role') === 'admin';
    }

    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['nullable', 'email:rfc', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'display_name.required' => 'Nama akun wajib diisi.',
            'display_name.min' => 'Nama akun minimal 3 karakter.',
            'display_name.max' => 'Nama akun maksimal 100 karakter.',
            'email.email' => 'Format email belum sesuai.',
            'email.max' => 'Email maksimal 120 karakter.',
        ];
    }
}
