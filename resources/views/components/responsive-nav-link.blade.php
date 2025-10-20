@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary-500 text-start text-base font-medium text-primary-400 bg-[#172033] focus:outline-none focus:text-primary-300 focus:bg-[#172033] focus:border-primary-400 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-400 hover:text-primary-400 hover:bg-[#172033] hover:border-primary-500 focus:outline-none focus:text-primary-400 focus:bg-[#172033] focus:border-primary-500 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
