<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isManager() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:150'],
            'category'    => ['required', Rule::in(Product::CATEGORIES)],
            'price'       => ['required', 'numeric', 'min:1', 'max:10000000'],
            'stock'       => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url'   => ['nullable', 'url', 'max:500'],
            'tags'        => ['nullable', 'array'],
            'tags.*'      => ['string', 'max:30'],
            'is_active'   => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'tags'      => is_string($this->tags)
                ? array_filter(array_map('trim', explode(',', $this->tags)))
                : ($this->tags ?? []),
        ]);
    }
}
