@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-[#1B2537] bg-[#101828] text-gray-100 placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm']) }}>
