{{--
    Reusable up/down sort button pair for Livewire sortable lists.

    Usage (index-based, default method names):
        <x-dcpl::sort-btns :value="$index" :first="$loop->first" :last="$loop->last" />

    Usage (index-based, custom method names):
        <x-dcpl::sort-btns :value="$index" up="moveUpField" down="moveDownField"
            :first="$loop->first" :last="$loop->last" />

    Usage (ID-based):
        <x-dcpl::sort-btns :value="$item->id" :first="$loop->first" :last="$loop->last" />

    Props:
        value — int       — argument passed to the up/down Livewire method
        up    — string    — Livewire method name for "move up"   (default: 'moveUp')
        down  — string    — Livewire method name for "move down" (default: 'moveDown')
        first — bool      — disables the up button when true
        last  — bool      — disables the down button when true
        size  — 'sm'|'md' — icon size: sm = w-3.5 h-3.5, md = w-4 h-4 (default: 'sm')
--}}
@props([
    'value' => 0,
    'up'    => 'moveUp',
    'down'  => 'moveDown',
    'first' => false,
    'last'  => false,
    'size'  => 'sm',
])

@php
    $icon = $size === 'md' ? 'w-4 h-4' : 'w-3.5 h-3.5';
@endphp

<div class="flex flex-col gap-0.5 shrink-0">
    <button
        type="button"
        wire:click="{{ $up }}({{ $value }})"
        wire:loading.attr="disabled"
        @disabled($first)
        class="p-0.5 rounded text-gray-400 hover:text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed focus:outline-none"
        title="Move up"
    >
        <svg class="{{ $icon }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
    </button>
    <button
        type="button"
        wire:click="{{ $down }}({{ $value }})"
        wire:loading.attr="disabled"
        @disabled($last)
        class="p-0.5 rounded text-gray-400 hover:text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed focus:outline-none"
        title="Move down"
    >
        <svg class="{{ $icon }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </button>
</div>
