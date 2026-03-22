{{--
    Reusable uppercase section heading.

    Usage:
        <x-dcpl::section-heading>Material</x-dcpl::section-heading>
        <x-dcpl::section-heading class="mb-4">With extra margin</x-dcpl::section-heading>

    Renders an <h2> with the standard section-heading style used throughout
    the DCPL staff interface.
--}}
@props([])

<h2 {{ $attributes->class(['text-sm font-medium text-gray-700 uppercase tracking-wide mb-3']) }}>
    {{ $slot }}
</h2>
