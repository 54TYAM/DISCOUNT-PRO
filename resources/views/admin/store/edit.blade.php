<x-admin-layout title="Edit Store">

<div class="max-w-3xl mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.store.show') }}"
           class="p-2 text-slate-400 hover:text-slate-600 hover:bg-stone-100 rounded-lg transition-colors flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="page-title">Edit Store</h1>
            <p class="page-subtitle">{{ $store->name }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.store.update') }}" class="card-form space-y-5">
        @csrf @method('PATCH')

        <div>
            <label class="form-label">Store name</label>
            <input type="text" name="name" value="{{ old('name', $store->name) }}" required maxlength="80" class="form-input">
            @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="form-label">Category</label>
            <select name="category" required class="form-input">
                @foreach (\App\Models\Store::CATEGORIES as $cat)
                    <option value="{{ $cat }}" {{ old('category', $store->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Description</label>
            <textarea name="description" rows="3" maxlength="500" class="form-input">{{ old('description', $store->description) }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Contact email</label>
                <input type="email" name="contact_email" value="{{ old('contact_email', $store->contact_email) }}" maxlength="120" class="form-input">
            </div>
            <div>
                <label class="form-label">Contact phone</label>
                <input type="text" name="contact_phone" value="{{ old('contact_phone', $store->contact_phone) }}" maxlength="25" class="form-input">
            </div>
        </div>

        <div>
            <label class="form-label">Address</label>
            <input type="text" name="address" value="{{ old('address', $store->address) }}" maxlength="200" class="form-input">
        </div>

        <div>
            <label class="form-label">Logo URL</label>
            <input type="url" name="logo_url" value="{{ old('logo_url', $store->logo_url) }}" maxlength="300" class="form-input">
        </div>

        <div>
            <label class="form-label">Banner colour</label>
            <div class="flex items-center gap-2 flex-wrap">
                @foreach (['brand', 'violet', 'emerald', 'amber', 'rose', 'sky', 'slate'] as $color)
                    <label class="cursor-pointer">
                        <input type="radio" name="banner_color" value="{{ $color }}"
                               {{ old('banner_color', $store->banner_color ?? 'brand') === $color ? 'checked' : '' }}
                               class="peer sr-only">
                        <span class="block w-9 h-9 rounded-lg border-2 border-slate-200 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-brand-500
                                     {{ ['brand' => 'bg-brand-500', 'violet' => 'bg-violet-500', 'emerald' => 'bg-emerald-500', 'amber' => 'bg-amber-500', 'rose' => 'bg-rose-500', 'sky' => 'bg-sky-500', 'slate' => 'bg-slate-500'][$color] }}"></span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-3">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $store->is_active) ? 'checked' : '' }}
                       class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                <span class="text-sm text-slate-700">Store is active (visible to customers)</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <a href="{{ route('admin.store.show') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save changes</button>
        </div>
    </form>

</div>

</x-admin-layout>
