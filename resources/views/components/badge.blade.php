{{--
    Generic inline badge with color variants.

    Usage:
        <x-dcpl::badge variant="outline">Material Type</x-dcpl::badge>
        <x-dcpl::badge variant="purple">Audience</x-dcpl::badge>
        <x-dcpl::badge variant="green">Found</x-dcpl::badge>
        <x-dcpl::badge variant="yellow">Duplicate</x-dcpl::badge>

    Props:
        variant — string — 'outline' | 'blue' | 'purple' | 'green' | 'yellow' | 'red' | 'gray' (default: 'gray')
--}}
@props([
    'variant' => 'gray',
])

@php
    $variantClasses = match ($variant) {
        'outline' => 'bg-white text-gray-700 border border-gray-300',
        'blue'    => 'bg-blue-100 text-blue-700',
        'purple'  => 'bg-purple-100 text-purple-700',
        'green'   => 'bg-green-100 text-green-700',
        'yellow'  => 'bg-yellow-100 text-yellow-700',
        'red'     => 'bg-red-100 text-red-600',
        default   => 'bg-gray-100 text-gray-500',
    };
@endphp

<span {{ $attributes->class(['inline-block px-2 py-0.5 rounded text-xs font-medium', $variantClasses]) }}>
    {{ $slot }}
</span>
