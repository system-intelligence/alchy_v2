<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-[#172033] border border-[#1B2537] rounded-md font-semibold text-xs text-gray-200 uppercase tracking-widest shadow-sm hover:bg-[#1f2a3d] focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-[#0d1829] disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
