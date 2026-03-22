{{--
    Staff primary navigation tab for the DCPL teal nav bar.

    Usage:
        <x-dcpl::staff-nav-tab href="{{ route('staff.index') }}" label="Requests" :active="true" />
        <x-dcpl::staff-nav-tab href="{{ route('staff.patrons') }}" label="Patrons" :active="false" />

    Props:
        href   — string — destination URL
        label  — string — tab label text (use :label prop, not slot, for correct Livewire output)
        active — bool   — whether this tab is the current page
--}}
@props([
    'href',
    'label',
    'active' => false,
])

@php
    $classes  = 'inline-flex items-center justify-center whitespace-nowrap text-white text-[15px] font-semibold tracking-wide px-10 py-3 border-b-[5px] transition-all shrink-0 ';
    $classes .= $active
        ? 'border-dcpl-orange bg-black/20'
        : 'border-transparent hover:bg-black/10 hover:border-white/30';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => trim($classes)]) }}>
    {{ $label }}
</a>
