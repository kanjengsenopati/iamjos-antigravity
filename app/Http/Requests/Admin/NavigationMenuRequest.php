<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NavigationMenuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user has permission to manage navigation
        return $this->user() && $this->user()->can('manage-navigation');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $routeNavigationMenu = $this->route('navigation_menu') ?? $this->route('navigationMenu') ?? $this->route('menu');
        $navigationMenuId = is_object($routeNavigationMenu) ? $routeNavigationMenu->getKey() : $routeNavigationMenu;
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH']);

        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'area_name' => [
                'required',
                'string',
                'max:255',
                // Ensure unique area_name per journal (or site-level with null journal_id)
                $isUpdate 
                    ? Rule::unique('navigation_menus', 'area_name')
                        ->ignore($navigationMenuId)
                        ->where(function ($query) {
                            return $query->where('journal_id', $this->input('journal_id'));
                        })
                    : Rule::unique('navigation_menus', 'area_name')
                        ->where(function ($query) {
                            return $query->where('journal_id', $this->input('journal_id'));
                        }),
            ],
            'journal_id' => [
                'nullable',
                'uuid',
                'exists:journals,id',
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Menu title is required',
            'title.max' => 'Menu title cannot exceed 255 characters',
            
            'area_name.required' => 'Menu location is required',
            'area_name.max' => 'Menu location cannot exceed 255 characters',
            'area_name.unique' => 'A menu already exists for this location. Please choose a different location.',
            
            'journal_id.uuid' => 'Invalid journal reference',
            'journal_id.exists' => 'The selected journal does not exist',
            
            'is_active.required' => 'Active status is required',
            'is_active.boolean' => 'Active status must be true or false',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'menu title',
            'area_name' => 'menu location',
            'journal_id' => 'journal',
            'is_active' => 'active status',
        ];
    }
}
