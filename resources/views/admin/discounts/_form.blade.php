{{--
  Shared form partial for create & edit.
  Expects: $discount (null on create), $categories, $products
--}}
@php
    $d          = $discount ?? null;
    $oldType    = old('type', $d?->type ?? 'percentage');
    $oldApplies = old('applicable_to', $d?->applicable_to ?? 'all');
    $oldTiers   = old('tiered_rules', $d?->tiered_rules ?? [['min' => '', 'discount_pct' => '']]);
    $isEdit     = ! is_null($d);
@endphp

<div x-data="{
    type:    '{{ $oldType }}',
    applies: '{{ $oldApplies }}',
    tiers:   {{ json_encode($oldTiers) }},
    addTier()    { this.tiers.push({ min: '', discount_pct: '' }) },
    removeTier(i){ if (this.tiers.length > 1) this.tiers.splice(i, 1) },
    needsValue() { return ['percentage', 'fixed'].includes(this.type) },
}">

{{-- ── Section 1: Basic Info ─────────────────────────────────────────── --}}
<div class="card p-6 mb-5">
    <h3 class="text-sm font-semibold text-slate-700 mb-5 pb-3 border-b border-slate-100">Basic Information</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        {{-- Title --}}
        <div class="sm:col-span-2">
            <x-input-label for="title" value="Discount title" />
            <x-text-input id="title" name="title" type="text"
                          :value="old('title', $d?->title)"
                          placeholder="e.g. Summer Mega Sale" />
            <x-input-error :messages="$errors->get('title')" />
        </div>

        {{-- Code --}}
        <div>
            <x-input-label for="code" value="Coupon code" />
            <x-text-input id="code" name="code" type="text"
                          :value="old('code', $d?->code)"
                          placeholder="e.g. SUMMER25"
                          class="uppercase tracking-widest font-mono" />
            <p class="text-xs text-slate-400 mt-1">Uppercase letters, numbers, underscores only.</p>
            <x-input-error :messages="$errors->get('code')" />
        </div>

        {{-- Type --}}
        <div>
            <x-input-label for="type" value="Discount type" />
            <select id="type" name="type" @change="type = $event.target.value" class="form-input">
                <option value="percentage"    :selected="type === 'percentage'">Percentage (%)</option>
                <option value="fixed"         :selected="type === 'fixed'">Fixed Amount (₹)</option>
                <option value="bogo"          :selected="type === 'bogo'">Buy 1 Get 1 (BOGO)</option>
                <option value="free_shipping" :selected="type === 'free_shipping'">Free Shipping</option>
                <option value="tiered"        :selected="type === 'tiered'">Tiered Discount</option>
            </select>
            <x-input-error :messages="$errors->get('type')" />
        </div>

        {{-- Description --}}
        <div class="sm:col-span-2">
            <x-input-label for="description" value="Description (optional)" />
            <textarea id="description" name="description" rows="2"
                      placeholder="Describe this discount for your records…"
                      class="form-input resize-none">{{ old('description', $d?->description) }}</textarea>
            <x-input-error :messages="$errors->get('description')" />
        </div>
    </div>
</div>

{{-- ── Section 2: Discount Value ────────────────────────────────────── --}}
<div class="card p-6 mb-5">
    <h3 class="text-sm font-semibold text-slate-700 mb-5 pb-3 border-b border-slate-100">Discount Value</h3>

    {{-- Percentage / Fixed value --}}
    <div x-show="needsValue()" class="mb-5">
        <x-input-label for="value" value="Value" />
        <div class="relative">
            <span x-show="type === 'percentage'"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">%</span>
            <span x-show="type === 'fixed'"
                  class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">₹</span>
            <x-text-input id="value" name="value" type="number" step="0.01" min="0"
                          :value="old('value', $d?->value)"
                          x-bind:class="type === 'fixed' ? 'pl-8' : 'pr-8'"
                          placeholder="0" />
        </div>
        <p x-show="type === 'percentage'" class="text-xs text-slate-400 mt-1">Enter a value between 1 and 100.</p>
        <p x-show="type === 'fixed'"      class="text-xs text-slate-400 mt-1">Fixed rupee amount to deduct.</p>
        <x-input-error :messages="$errors->get('value')" />
    </div>

    {{-- BOGO / Free Shipping info --}}
    <div x-show="type === 'bogo'" class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700">
        BOGO: The customer gets the same item for free. Discount logic applied at checkout.
    </div>
    <div x-show="type === 'free_shipping'" class="p-4 bg-sky-50 border border-sky-200 rounded-xl text-sm text-sky-700">
        Free Shipping: The shipping fee is waived entirely. No rupee value needed.
    </div>

    {{-- Tiered rules --}}
    <div x-show="type === 'tiered'">
        <p class="text-sm text-slate-600 mb-3">
            Define tiers — the highest matching tier applies at checkout.
        </p>
        <div class="space-y-3">
            <template x-for="(tier, i) in tiers" :key="i">
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <label class="form-label">Min. order (₹)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">₹</span>
                            <input type="number" :name="`tiered_rules[${i}][min]`" x-model="tier.min"
                                   min="0" placeholder="500" class="form-input pl-8">
                        </div>
                    </div>
                    <div class="flex-1">
                        <label class="form-label">Discount %</label>
                        <div class="relative">
                            <input type="number" :name="`tiered_rules[${i}][discount_pct]`" x-model="tier.discount_pct"
                                   min="1" max="99" placeholder="10" class="form-input pr-8">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">%</span>
                        </div>
                    </div>
                    <button type="button" @click="removeTier(i)"
                            class="mt-5 p-2 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>
        <button type="button" @click="addTier()"
                class="mt-3 btn-secondary text-xs py-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add tier
        </button>
        <x-input-error :messages="$errors->get('tiered_rules')" />
    </div>
</div>

{{-- ── Section 3: Conditions ────────────────────────────────────────── --}}
<div class="card p-6 mb-5">
    <h3 class="text-sm font-semibold text-slate-700 mb-5 pb-3 border-b border-slate-100">Conditions & Limits</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <x-input-label for="min_order_value" value="Minimum order value (₹)" />
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">₹</span>
                <x-text-input id="min_order_value" name="min_order_value" type="number" step="0.01" min="0"
                              :value="old('min_order_value', $d?->min_order_value ?? 0)"
                              class="pl-8" placeholder="0" />
            </div>
            <p class="text-xs text-slate-400 mt-1">Set 0 for no minimum.</p>
            <x-input-error :messages="$errors->get('min_order_value')" />
        </div>

        <div>
            <x-input-label for="uses_per_user" value="Uses per customer" />
            <x-text-input id="uses_per_user" name="uses_per_user" type="number" min="1"
                          :value="old('uses_per_user', $d?->uses_per_user ?? 1)" />
            <x-input-error :messages="$errors->get('uses_per_user')" />
        </div>

        <div>
            <x-input-label for="max_uses" value="Total usage limit" />
            <x-text-input id="max_uses" name="max_uses" type="number" min="1"
                          :value="old('max_uses', $d?->max_uses)"
                          placeholder="Leave blank for unlimited" />
            <x-input-error :messages="$errors->get('max_uses')" />
        </div>

        {{-- Applicable to --}}
        <div>
            <x-input-label for="applicable_to" value="Applies to" />
            <select id="applicable_to" name="applicable_to"
                    @change="applies = $event.target.value" class="form-input">
                <option value="all"      :selected="applies === 'all'">All Products</option>
                <option value="category" :selected="applies === 'category'">Specific Category</option>
                <option value="product"  :selected="applies === 'product'">Specific Products</option>
            </select>
            <x-input-error :messages="$errors->get('applicable_to')" />
        </div>

        {{-- Category multi-select --}}
        <div x-show="applies === 'category'" class="sm:col-span-2">
            <x-input-label for="target_ids_cat" value="Select categories" />
            <div class="flex flex-wrap gap-2 mt-1">
                @foreach ($categories as $cat)
                <label class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer
                              hover:border-brand-400 hover:bg-brand-50 transition-colors text-sm">
                    <input type="checkbox" name="target_ids[]" value="{{ $cat }}"
                           {{ in_array($cat, old('target_ids', $d?->target_ids ?? [])) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    {{ $cat }}
                </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('target_ids')" />
        </div>

        {{-- Product search / multi-select --}}
        <div x-show="applies === 'product'" class="sm:col-span-2"
             x-data="{ search: '' }">
            <x-input-label value="Select products" />
            <input type="text" x-model="search" placeholder="Filter products…"
                   class="form-input mb-3">
            <div class="border border-slate-200 rounded-xl max-h-48 overflow-y-auto p-3 space-y-1">
                @foreach ($products as $product)
                <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-stone-50 cursor-pointer text-sm"
                       x-show="search === '' || '{{ strtolower($product->name) }}'.includes(search.toLowerCase())">
                    <input type="checkbox" name="target_ids[]" value="{{ (string) $product->_id }}"
                           {{ in_array((string) $product->_id, old('target_ids', $d?->target_ids ?? [])) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <span class="flex-1 truncate text-slate-700">{{ $product->name }}</span>
                    <span class="text-xs text-slate-400 flex-shrink-0">{{ $product->category }}</span>
                </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('target_ids')" />
        </div>
    </div>
</div>

{{-- ── Section 4: Schedule & Status ─────────────────────────────────── --}}
<div class="card p-6 mb-5">
    <h3 class="text-sm font-semibold text-slate-700 mb-5 pb-3 border-b border-slate-100">Schedule & Status</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <x-input-label for="start_date" value="Start date & time (optional)" />
            <x-text-input id="start_date" name="start_date" type="datetime-local"
                          :value="old('start_date', $d?->start_date?->format('Y-m-d\TH:i'))" />
            <p class="text-xs text-slate-400 mt-1">Leave blank to activate immediately.</p>
            <x-input-error :messages="$errors->get('start_date')" />
        </div>

        <div>
            <x-input-label for="end_date" value="End date & time (optional)" />
            <x-text-input id="end_date" name="end_date" type="datetime-local"
                          :value="old('end_date', $d?->end_date?->format('Y-m-d\TH:i'))" />
            <p class="text-xs text-slate-400 mt-1">Leave blank for no expiry.</p>
            <x-input-error :messages="$errors->get('end_date')" />
        </div>

        <div class="sm:col-span-2">
            <label class="flex items-center gap-3 cursor-pointer select-none">
                <div class="relative">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active"
                           {{ old('is_active', $d?->is_active ?? true) ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="w-10 h-5 bg-slate-200 peer-checked:bg-brand-600 rounded-full transition-colors duration-200"></div>
                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow
                                peer-checked:translate-x-5 transition-transform duration-200"></div>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-700">Active</p>
                    <p class="text-xs text-slate-400">Customers can apply this discount immediately.</p>
                </div>
            </label>
        </div>
    </div>
</div>

{{-- ── Form actions ─────────────────────────────────────────────────── --}}
<div class="flex items-center gap-3 justify-end">
    <a href="{{ route('admin.discounts.index') }}" class="btn-secondary">Cancel</a>
    <button type="submit" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ $isEdit ? 'Save changes' : 'Create discount' }}
    </button>
</div>

</div>{{-- end x-data --}}
