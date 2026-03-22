{{--
    Horizontal info bar — gray rounded strip with inline key/value items.

    Usage:
        <x-dcpl::info-bar>
            <x-dcpl::info-bar-item icon="clock" label="Submitted" value="Mar 12, 2026" />
            <x-dcpl::info-bar-item label="Status" value="Pending" dot-color="#F47920" />
        </x-dcpl::info-bar>
--}}
@props([])

<div {{ $attributes->class(['bg-gray-100 rounded-lg px-4 py-3 mb-6 flex items-center gap-6 text-sm']) }}>
    {{ $slot }}
</div>
