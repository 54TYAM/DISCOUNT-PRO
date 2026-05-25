<div class="card-form space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="sm:col-span-2">
            <label class="form-label">Product name</label>
            <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required maxlength="150" class="form-input">
            @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="form-label">Category</label>
            <select name="category" required class="form-input">
                <option value="">Select…</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}" {{ old('category', $product->category ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            @error('category') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Price (₹)</label>
            <input type="number" name="price" value="{{ old('price', $product->price ?? '') }}" required min="1" step="1" class="form-input">
            @error('price') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="form-label">Stock</label>
            <input type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" required min="0" step="1" class="form-input">
            @error('stock') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="form-label">Description</label>
        <textarea name="description" rows="3" maxlength="1000" class="form-input"
                  placeholder="What does this product do? What's special about it?">{{ old('description', $product->description ?? '') }}</textarea>
    </div>

    <div>
        <label class="form-label">Image URL</label>
        <input type="url" name="image_url" value="{{ old('image_url', $product->image_url ?? '') }}" maxlength="500"
               class="form-input" placeholder="https://images.unsplash.com/...">
        <p class="text-xs text-slate-400 mt-1">Paste a direct image URL (jpg / png / webp).</p>
        @error('image_url') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label">Tags <span class="text-slate-400 font-normal text-xs">(comma-separated)</span></label>
        <input type="text" name="tags"
               value="{{ old('tags', isset($product) && $product->tags ? implode(', ', (array) $product->tags) : '') }}"
               class="form-input" placeholder="bestseller, new, sale">
    </div>

    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}
               class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
        <span class="text-sm text-slate-700">Product is active (visible to customers)</span>
    </label>

    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
        <a href="{{ route('admin.products.index') }}" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary">{{ isset($product) ? 'Save changes' : 'Add product' }}</button>
    </div>
</div>
