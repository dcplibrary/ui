{{--
    Mini colored stat card (e.g. Catalog results, Editions found).

    Usage:
        <x-dcpl::stat-card icon="search" label="Catalog" value="1 found" subtitle="Patron rejected" color="blue" />
        <x-dcpl::stat-card icon="book" label="Editions" value="5 found" subtitle="Click to view →" color="purple" :href="$url" />

    Props:
        icon     — string      — 'search' | 'book'
        label    — string      — small heading
        value    — string      — prominent value text
        subtitle — string|null — small text below value
        color    — string      — 'blue' | 'purple' | 'green' (default: 'blue')
        href     — string|null — when provided renders as a clickable link
--}}
@props([
    'icon'     => 'search',
    'label'    => '',
    'value'    => '',
    'subtitle' => null,
    'color'    => 'blue',
    'href'     => null,
])

@php
    $colorMap = [
        'blue'   => ['bg' => 'bg-blue-50',   'border' => 'border-blue-100',   'icon' => 'text-blue-600',   'hover' => 'hover:bg-blue-100 hover:border-blue-200',   'sub' => 'text-blue-700'],
        'purple' => ['bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'icon' => 'text-purple-600', 'hover' => 'hover:bg-purple-100 hover:border-purple-300', 'sub' => 'text-purple-700'],
        'green'  => ['bg' => 'bg-green-50',  'border' => 'border-green-100',  'icon' => 'text-green-600',  'hover' => 'hover:bg-green-100 hover:border-green-200',  'sub' => 'text-green-700'],
    ];
    $c   = $colorMap[$color] ?? $colorMap['blue'];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->class([$c['bg'], 'rounded-lg p-4 border', $c['border'], $href ? $c['hover'] . ' transition-colors' : '', 'text-left block']) }}>
    <div class="flex items-center gap-1.5 mb-1.5">
        @if($icon === 'search')
            <svg class="h-4 w-4 {{ $c['icon'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
        @elseif($icon === 'book')
            <svg class="h-4 w-4 {{ $c['icon'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
        @endif
        <p class="text-xs font-medium text-gray-600">{{ $label }}</p>
    </div>
    <p class="text-base font-semibold text-gray-900">{{ $value }}</p>
    @if($subtitle)
        <p class="text-xs {{ $href ? $c['sub'] : 'text-gray-600' }} mt-0.5">{{ $subtitle }}</p>
    @endif
</{{ $tag }}>
