<?php

namespace App\Http\Requests\Admin;

use App\Models\Discount;
use Illuminate\Foundation\Http\FormRequest;

class DiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isManager() ?? false;
    }

    public function rules(): array
    {
        $discountId = $this->route('discount'); // string ID on edit, null on create

        return [
            'title'           => ['required', 'string', 'max:100'],
            'code'            => ['required', 'string', 'max:30', 'regex:/^[A-Z0-9_]+$/',
                function ($attribute, $value, $fail) use ($discountId) {
                    $exists = Discount::where('code', strtoupper($value))
                        ->when($discountId, fn ($q) => $q->where('_id', '!=', $discountId))
                        ->exists();
                    if ($exists) $fail('This coupon code is already taken.');
                },
            ],
            'description'     => ['nullable', 'string', 'max:500'],
            'type'            => ['required', 'in:percentage,fixed,bogo,free_shipping,tiered'],
            'value'           => ['nullable', 'numeric', 'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->type === 'percentage' && $value > 100) {
                        $fail('Percentage discount cannot exceed 100%.');
                    }
                },
            ],
            'tiered_rules'           => ['required_if:type,tiered', 'nullable', 'array', 'min:1'],
            'tiered_rules.*.min'     => ['required_if:type,tiered', 'numeric', 'min:0'],
            'tiered_rules.*.discount_pct' => ['required_if:type,tiered', 'numeric', 'min:1', 'max:99'],
            'min_order_value' => ['nullable', 'numeric', 'min:0'],
            'max_uses'        => ['nullable', 'integer', 'min:1'],
            'uses_per_user'   => ['required', 'integer', 'min:1'],
            'applicable_to'   => ['required', 'in:all,category,product'],
            'target_ids'      => ['required_if:applicable_to,category,product', 'nullable', 'array'],
            'target_label'    => ['nullable', 'string', 'max:100'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date', 'after:start_date'],
            'is_active'       => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex'              => 'Code must contain only uppercase letters, numbers, and underscores.',
            'end_date.after'          => 'End date must be after the start date.',
            'tiered_rules.required_if' => 'At least one pricing tier is required for tiered discounts.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code'      => strtoupper(trim($this->code ?? '')),
            'is_active' => $this->boolean('is_active', true),
        ]);

        // Clear type-specific fields that don't apply
        if (in_array($this->type, ['bogo', 'free_shipping'])) {
            $this->merge(['value' => 0]);
        }
        if ($this->type !== 'tiered') {
            $this->merge(['tiered_rules' => null]);
        }
        if ($this->applicable_to === 'all') {
            $this->merge(['target_ids' => [], 'target_label' => 'All Products']);
        }
    }
}
