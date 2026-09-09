<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SitePageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user has permission to manage site pages
        return $this->user() && $this->user()->can('manage-site-pages');
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate slug from title if slug is not provided
        if (!$this->has('slug') && $this->has('title')) {
            $this->merge([
                'slug' => str($this->title)->slug()->toString(),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $routeSitePage = $this->route('site_page') ?? $this->route('sitePage');
        $sitePageId = is_object($routeSitePage) ? $routeSitePage->getKey() : $routeSitePage;
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH']);

        return [
            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                $isUpdate 
                    ? Rule::unique('site_pages', 'slug')->ignore($sitePageId)
                    : Rule::unique('site_pages', 'slug'),
            ],
            'content' => [
                'nullable',
                'string',
            ],
            'meta_description' => [
                'nullable',
                'string',
                'max:160',
            ],
            'is_published' => [
                'required',
                'boolean',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
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
            'title.required' => 'Page title is required',
            'title.min' => 'Page title must be at least 3 characters',
            'title.max' => 'Page title cannot exceed 255 characters',
            
            'slug.required' => 'Page slug is required',
            'slug.max' => 'Page slug cannot exceed 255 characters',
            'slug.regex' => 'Page slug must contain only lowercase letters, numbers, and hyphens (e.g., about-us)',
            'slug.unique' => 'This slug is already in use. Please choose a different slug or try adding a number (e.g., about-us-2)',
            
            'content.string' => 'Page content must be valid text',
            
            'meta_description.max' => 'Meta description cannot exceed 160 characters for optimal SEO',
            
            'is_published.required' => 'Publication status is required',
            'is_published.boolean' => 'Publication status must be true or false',
            
            'sort_order.integer' => 'Sort order must be a number',
            'sort_order.min' => 'Sort order cannot be negative',
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
            'title' => 'page title',
            'slug' => 'page slug',
            'content' => 'page content',
            'meta_description' => 'meta description',
            'is_published' => 'publication status',
            'sort_order' => 'sort order',
        ];
    }
}
