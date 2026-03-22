{{--
    Full-width sidebar action button with icon.

    Usage (link):
        <x-dcpl::sidebar-btn href="https://…" icon="external-link" label="View on Amazon" />

    Usage (Alpine / Livewire action):
        <x-dcpl::sidebar-btn icon="user" label="Reassign" @click="$dispatch('dcpl:open-modal', 'reassign')" />

    Usage (danger):
        <x-dcpl::sidebar-btn icon="trash" label="Delete" variant="danger" wire:click="delete" />

    Props:
        icon    — string      — heroicon name: 'user' | 'search' | 'external-link' | 'cart' | 'trash' | 'arrow-right-left' | 'pencil' | 'check'
        label   — string      — button text
        variant — string      — 'default' (gray outline) | 'danger' (red outline)
        href    — string|null — when provided renders an <a>, otherwise renders a <button>
--}}
@props([
    'icon'    => 'user',
    'label'   => '',
    'variant' => 'default',
    'href'    => null,
])

@php
    $isDanger  = $variant === 'danger';
    $classes   = 'w-full justify-start h-10 inline-flex items-center gap-2 px-3 text-sm font-medium rounded border transition-colors '
               . ($isDanger
                   ? 'text-red-600 hover:text-red-700 hover:bg-red-50 border-red-200'
                   : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50 border-gray-300');
    $iconClass = $isDanger ? 'h-4 w-4' : 'h-4 w-4 text-gray-500';
@endphp

@if($href)
<a href="{{ $href }}" target="_blank" rel="noopener noreferrer"
   {{ $attributes->class([$classes]) }}>
@else
<button type="button" {{ $attributes->class([$classes]) }}>
@endif

    @if($icon === 'user')
        <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
    @elseif($icon === 'search')
        <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
    @elseif($icon === 'external-link')
        <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
    @elseif($icon === 'cart')
        <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
    @elseif($icon === 'trash')
        <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
    @elseif($icon === 'arrow-right-left')
        <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
    @elseif($icon === 'pencil')
        <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
    @elseif($icon === 'check')
        <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
    @endif

    {{ $label }}

@if($href)
</a>
@else
</button>
@endif
