<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-primary w-full justify-center py-2.5']) }}>
    {{ $slot }}
</button>
