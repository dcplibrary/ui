{{--
    Circle avatar with computed initials and DCPL-blue background.

    Usage:
        <x-dcpl::avatar name="Brian Lashbrook" />
        <x-dcpl::avatar name="Jane Doe" size="lg" />

    Props:
        name — string — full name; initials derived automatically
        size — string — 'sm' (w-8 h-8) | 'md' (w-9 h-9, default) | 'lg' (w-12 h-12)
--}}
@props([
    'name' => '',
    'size' => 'md',
])

@php
    $parts    = explode(' ', trim($name));
    $initials = strtoupper(substr($parts[0] ?? '', 0, 1));
    if (count($parts) > 1) {
        $initials .= strtoupper(substr(end($parts), 0, 1));
    }

    $sizeClasses = match ($size) {
        'sm'    => 'w-8 h-8 text-xs',
        'lg'    => 'w-12 h-12 text-base',
        default => 'w-9 h-9 text-sm',
    };
@endphp

<div {{ $attributes->class([
    'flex items-center justify-center rounded-full text-white font-semibold',
    $sizeClasses,
]) }}
     style="background-color: var(--dcpl-blue, #0075A3);"
     title="{{ $name }}">
    {{ $initials ?: 'U' }}
</div>
