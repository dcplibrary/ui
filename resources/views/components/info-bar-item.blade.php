{{--
    Single item within an info-bar: icon + label + value.

    Usage:
        <x-dcpl::info-bar-item icon="clock" label="Submitted" value="Mar 12, 2026" />
        <x-dcpl::info-bar-item icon="user"  label="Assigned to" value="Brian Lashbrook" />
        <x-dcpl::info-bar-item label="Status" value="Pending" dot-color="#F47920" />

    Props:
        icon     — string|null — heroicon name: 'clock' | 'user' (renders outline SVG)
        label    — string      — muted label text
        value    — string      — bold value text
        dotColor — string|null — CSS color value for a filled dot (replaces the icon)
--}}
@props([
    'icon'     => null,
    'label'    => '',
    'value'    => '',
    'dotColor' => null,
])

<div class="flex items-center gap-2">
    @if($dotColor)
        <span class="inline-block h-3 w-3 rounded-full flex-shrink-0" style="background-color: {{ $dotColor }};"></span>
    @elseif($icon === 'clock')
        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
    @elseif($icon === 'user')
        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
    @elseif($icon === 'calendar')
        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
    @elseif($icon === 'tag')
        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg>
    @endif
    <span class="text-gray-600">{{ $label }}:</span>
    <span class="font-medium text-gray-900">{{ $value }}</span>
</div>
