<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContentBlockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user has permission to manage content blocks
        return $this->user() && $this->user()->can('manage-content-blocks');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $routeContentBlock = $this->route('content_block') ?? $this->route('contentBlock');
        $contentBlockId = is_object($routeContentBlock) ? $routeContentBlock->getKey() : $routeContentBlock;
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH']);

        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'identifier' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                $isUpdate 
                    ? Rule::unique('site_content_blocks', 'key')->ignore($contentBlockId)
                    : Rule::unique('site_content_blocks', 'key'),
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'content' => [
                'nullable',
                'string',
            ],
            'category' => [
                'required',
                'string',
                'in:content,hero,feature,stats,cta,footer',
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'icon' => [
                'nullable',
                'string',
                'max:255',
            ],
            'config' => [
                'nullable',
                'array',
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
            'title.required' => 'Block title is required',
            'title.max' => 'Block title cannot exceed 255 characters',
            
            'identifier.required' => 'Block identifier is required',
            'identifier.max' => 'Block identifier cannot exceed 255 characters',
            'identifier.regex' => 'Block identifier must contain only lowercase letters, numbers, and underscores (e.g., hero_search)',
            'identifier.unique' => 'This identifier is already in use. Please choose a different identifier or try adding a number (e.g., hero_search_2)',
            
            'description.string' => 'Block description must be valid text',
            
            'content.string' => 'Block content must be valid text',
            
            'category.required' => 'Block category is required',
            'category.in' => 'Block category must be one of: content, hero, feature, stats, cta, footer',
            
            'is_active.required' => 'Active status is required',
            'is_active.boolean' => 'Active status must be true or false',
            
            'sort_order.integer' => 'Sort order must be a number',
            'sort_order.min' => 'Sort order cannot be negative',
            
            'icon.max' => 'Icon name cannot exceed 255 characters',
            
            'config.array' => 'Configuration must be a valid array',
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
            'title' => 'block title',
            'identifier' => 'block identifier',
            'description' => 'block description',
            'content' => 'block content',
            'category' => 'block category',
            'is_active' => 'active status',
            'sort_order' => 'sort order',
            'icon' => 'icon',
            'config' => 'configuration',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Map 'identifier' input to 'key' field for database
        if ($this->has('identifier')) {
            $this->merge([
                'key' => $this->identifier,
            ]);
        }
    }

    /**
     * Get the validated data from the request.
     *
     * @param  array|int|string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        // If getting all validated data, map 'identifier' to 'key'
        if (is_null($key) && is_array($validated)) {
            if (isset($validated['identifier'])) {
                $validated['key'] = $validated['identifier'];
                unset($validated['identifier']);
            }
        }
        
        return $validated;
    }
}
