{{--
    Reusable active/inactive status indicator pill.

    Usage:
        <x-dcpl::status-pill :active="$record->active" />
        <x-dcpl::status-pill :active="$field['required']" active-label="Required" inactive-label="Optional" />
        <x-dcpl::status-pill :active="false" :show-inactive="false" />

    Props:
        active         — bool   — current state
        active-label   — string — label when true  (default: "Active")
        inactive-label — string — label when false (default: "Inactive")
        show-inactive  — bool   — whether to render anything when inactive (default: true)
--}}
@props([
    'active'        => false,
    'activeLabel'   => 'Active',
    'inactiveLabel' => 'Inactive',
    'showInactive'  => true,
])

@if($active)
<span {{ $attributes->class(['inline-block px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700']) }}>
    {{ $activeLabel }}
</span>
@elseif($showInactive)
<span {{ $attributes->class(['inline-block px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500']) }}>
    {{ $inactiveLabel }}
</span>
@endif
