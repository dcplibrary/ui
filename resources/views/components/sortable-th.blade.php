{{--
    Sortable table column header — URL-driven (no Livewire required).

    Renders a <th> that links to the current URL with sort/direction query
    parameters. The active column is highlighted with bolder text and a
    solid triangle indicating sort direction.

    Usage:
        <x-dcpl::sortable-th column="name" label="Name" />
        <x-dcpl::sortable-th column="created_at" label="Submitted" arrow-side="left" />

    Props:
        column    — string — database/query column name used for sorting
        label     — string — visible header text
        arrowSide — string — 'left' | 'right' (default: 'right')
--}}
@props([
    'column',
    'label',
    'arrowSide' => 'right',
])

@php
    $currentSort = request()->query('sort');
    $currentDir  = strtolower(request()->query('direction', 'asc'));
    $isActive    = $currentSort === $column;
    $nextDir     = $isActive && $currentDir === 'asc' ? 'desc' : 'asc';
    $url         = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDir]);
@endphp

<th {{ $attributes->merge(['class' => 'px-4 py-3 text-left']) }}>
    <a href="{{ $url }}"
       class="inline-flex items-center gap-1 hover:text-gray-900 {{ $isActive ? 'text-gray-900 font-semibold' : 'text-gray-600 font-medium' }}">
        @if($isActive && $arrowSide === 'left')
            <svg class="h-3 w-3 flex-shrink-0 text-gray-400" viewBox="0 0 10 6" fill="currentColor" aria-hidden="true">
                @if($currentDir === 'asc')
                    <path d="M5 0L10 6H0z" />
                @else
                    <path d="M5 6L0 0h10z" />
                @endif
            </svg>
        @endif
        {{ $label }}
        @if($isActive && $arrowSide === 'right')
            <svg class="h-3 w-3 flex-shrink-0 text-gray-400" viewBox="0 0 10 6" fill="currentColor" aria-hidden="true">
                @if($currentDir === 'asc')
                    <path d="M5 0L10 6H0z" />
                @else
                    <path d="M5 6L0 0h10z" />
                @endif
            </svg>
        @endif
    </a>
</th>
