{{--
    Two-column settings layout: narrow sidebar of action buttons + wide content panel.

    Usage:
        <x-dcpl::settings-layout>
            <x-slot:sidebar>
                <x-dcpl::section-heading>Actions</x-dcpl::section-heading>
                <x-dcpl::sidebar-btn icon="pencil" label="Edit" wire:click="openEdit" />
                <x-dcpl::sidebar-btn icon="trash" label="Delete" variant="danger" wire:click="confirmDelete" />
            </x-slot:sidebar>

            {{-- Main content panel --}}
            <x-dcpl::section-heading>Details</x-dcpl::section-heading>
            <x-dcpl::info-bar>
                <x-dcpl::info-bar-item icon="clock" label="Created" value="{{ $record->created_at->format('M j, Y') }}" />
            </x-dcpl::info-bar>
            ...
        </x-dcpl::settings-layout>

    Slots:
        sidebar — left column (200px), for sidebar-btns, section-headings, etc.
        default — right column (flex-1), main content area

    Props:
        sidebarWidth — string — Tailwind width class for the sidebar (default: 'w-48')
--}}
@props([
    'sidebarWidth' => 'w-48',
])

<div {{ $attributes->class(['flex gap-8 items-start']) }}>
    {{-- Sidebar --}}
    <div class="{{ $sidebarWidth }} flex-shrink-0 flex flex-col gap-2">
        {{ $sidebar }}
    </div>

    {{-- Content panel --}}
    <div class="flex-1 min-w-0">
        {{ $slot }}
    </div>
</div>
