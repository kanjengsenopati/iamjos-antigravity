<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required',
            'avatar' => 'nullable|image|max:2048',
        ];

        if ($this->isMethod('post')) {
            // saat create admin
            $rules['email'] = 'required|email|unique:admins,email';
        } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
            // saat update admin
            $rules['email'] = 'required|email|unique:admins,email,' . $this->route('admin');
        }

        return $rules;
    }
}
