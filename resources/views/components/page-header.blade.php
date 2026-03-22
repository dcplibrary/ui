{{--
    Show-page breadcrumb header with back link, title, and optional badges.

    Usage:
        <x-dcpl::page-header
            :back-url="route('staff.index')"
            back-label="Back to list"
            title="Item #42"
        >
            <x-dcpl::badge variant="blue">Active</x-dcpl::badge>
        </x-dcpl::page-header>

    Props:
        backUrl   — string      — URL for the back link
        backLabel — string      — text for the back link (without arrow)
        title     — string      — page heading text
        id        — string|null — optional ID shown as muted monospace after title

    Slot:
        Optional trailing content (badges, timestamps, etc.)
--}}
@props([
    'backUrl'   => '#',
    'backLabel' => 'Back',
    'title'     => '',
    'id'        => null,
])

<div class="mb-6 flex items-center gap-3">
    <a href="{{ $backUrl }}" class="text-sm hover:underline" style="color: var(--dcpl-blue, #0075A3);">&larr; {{ $backLabel }}</a>
    <span class="text-gray-300">/</span>
    <h1 class="text-xl font-semibold" style="color: var(--dcpl-text, #4d6375);">{{ $title }}</h1>
    @if($id)
        <span class="text-sm text-gray-400 font-mono">#{{ $id }}</span>
    @endif
    {{ $slot }}
</div>
