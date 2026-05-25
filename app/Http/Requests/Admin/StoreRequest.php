<?php

namespace App\Http\Requests\Admin;

use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isManager() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:80'],
            'category'      => ['required', Rule::in(Store::CATEGORIES)],
            'description'   => ['nullable', 'string', 'max:500'],
            'banner_color'  => ['nullable', 'string', 'max:30'],
            'logo_url'      => ['nullable', 'url', 'max:300'],
            'address'       => ['nullable', 'string', 'max:200'],
            'contact_email' => ['nullable', 'email', 'max:120'],
            'contact_phone' => ['nullable', 'string', 'max:25'],
            'is_active'     => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active'    => $this->boolean('is_active', true),
            'banner_color' => $this->banner_color ?: 'brand',
        ]);
    }
}
