<x-admin-layout title="Register Your Store">

<div class="max-w-3xl mx-auto">

    <x-page-header
        title="Register Your Store"
        subtitle="Set up your storefront — you'll be able to add products and create coupons once it's live." />

    @if (session('info'))
        <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-sm">
            {{ session('info') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.store.store') }}" class="card-form space-y-5">
        @csrf

        <div>
            <label class="form-label">Store name</label>
            <input type="text" name="name" value="{{ old('name') }}" required maxlength="80"
                   class="form-input" placeholder="e.g. Aurora Electronics">
            @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="form-label">Category</label>
            <select name="category" required class="form-input">
                <option value="">Select a category…</option>
                @foreach (\App\Models\Store::CATEGORIES as $cat)
                    <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            @error('category') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="form-label">Description</label>
            <textarea name="description" rows="3" maxlength="500" class="form-input"
                      placeholder="What do you sell? Tell customers what makes your store special.">{{ old('description') }}</textarea>
            @error('description') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Contact email</label>
                <input type="email" name="contact_email" value="{{ old('contact_email') }}" maxlength="120"
                       class="form-input" placeholder="hello@your-store.com">
                @error('contact_email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Contact phone</label>
                <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" maxlength="25"
                       class="form-input" placeholder="+91 98765 43210">
            </div>
        </div>

        <div>
            <label class="form-label">Address</label>
            <input type="text" name="address" value="{{ old('address') }}" maxlength="200"
                   class="form-input" placeholder="City, State, Country">
        </div>

        <div>
            <label class="form-label">Logo URL <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
            <input type="url" name="logo_url" value="{{ old('logo_url') }}" maxlength="300"
                   class="form-input" placeholder="https://...">
            @error('logo_url') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="form-label">Banner colour</label>
            <div class="flex items-center gap-2 flex-wrap">
                @foreach (['brand', 'violet', 'emerald', 'amber', 'rose', 'sky', 'slate'] as $color)
                    <label class="cursor-pointer">
                        <input type="radio" name="banner_color" value="{{ $color }}"
                               {{ old('banner_color', 'brand') === $color ? 'checked' : '' }}
                               class="peer sr-only">
                        <span class="block w-9 h-9 rounded-lg border-2 border-slate-200 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-brand-500
                                     {{ ['brand' => 'bg-brand-500', 'violet' => 'bg-violet-500', 'emerald' => 'bg-emerald-500', 'amber' => 'bg-amber-500', 'rose' => 'bg-rose-500', 'sky' => 'bg-sky-500', 'slate' => 'bg-slate-500'][$color] }}"></span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                Register store →
            </button>
        </div>
    </form>

</div>

</x-admin-layout>
