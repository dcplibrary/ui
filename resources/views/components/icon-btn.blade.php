{{--
    Icon button — renders as <a href> for navigation or <button> for Livewire/Alpine actions.

    Usage (link):
        <x-dcpl::icon-btn :href="route('staff.edit', $id)" variant="edit" label="Edit" />

    Usage (Livewire button — no href):
        <x-dcpl::icon-btn variant="edit" label="Edit" wire:click="openEdit({{ $id }})" />
        <x-dcpl::icon-btn variant="delete" label="Delete" x-on:click="confirm('Delete?') && $wire.delete({{ $id }})" />

    Props:
        href    — string|null — when provided renders <a>, otherwise renders <button>
        label   — string      — tooltip text and aria-label
        variant — string      — 'view' | 'edit' | 'delete' | 'remove'
--}}
@props([
    'href'    => null,
    'label'   => '',
    'variant' => 'view',
])

@php
    $isRed     = in_array($variant, ['delete', 'remove']);
    $colors    = $isRed
        ? 'text-red-500 hover:text-red-700 hover:bg-red-50'
        : 'text-blue-500 hover:text-blue-700 hover:bg-blue-50';
    $baseClass = 'inline-flex items-center justify-center w-7 h-7 rounded ' . $colors;
@endphp

<span class="relative group">
    @if($href)
    <a href="{{ $href }}" class="{{ $baseClass }}" title="{{ $label }}" aria-label="{{ $label }}" {{ $attributes }}>
    @else
    <button type="button" class="{{ $baseClass }}" title="{{ $label }}" aria-label="{{ $label }}" {{ $attributes }}>
    @endif

        @if($variant === 'view')
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
            </svg>
        @elseif($variant === 'edit')
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
            </svg>
        @else
            {{-- delete / remove --}}
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
            </svg>
        @endif

    @if($href)
    </a>
    @else
    </button>
    @endif

    <span class="pointer-events-none absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-1.5 py-0.5 text-xs text-white bg-gray-700 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap z-10">{{ $label }}</span>
</span>
