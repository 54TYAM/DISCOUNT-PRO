<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isManager() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:150'],
            'description'    => ['nullable', 'string', 'max:1000'],
            'type'           => ['required', 'in:flash_sale,seasonal,loyalty,referral'],
            'discount_id'    => ['nullable', 'string'],
            'banner_color'   => ['nullable', 'string', 'max:30'],
            'target_segment' => ['required', 'in:all,new_users,returning,high_value,inactive'],
            'start_at'       => ['nullable', 'date'],
            'end_at'         => ['nullable', 'date', 'after:start_at'],
            'is_active'      => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_at.after' => 'End date must be after the start date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active'   => $this->boolean('is_active', true),
            'discount_id' => $this->discount_id ?: null,
            'banner_color' => $this->banner_color ?: 'slate',
        ]);
    }
}
