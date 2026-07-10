<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHrPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->get('hr_role') === 'admin';
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru belum sama.',
            'password.regex' => 'Password baru harus berisi huruf dan angka.',
        ];
    }
}
