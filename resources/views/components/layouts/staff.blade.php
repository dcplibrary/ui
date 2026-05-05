<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Staff') &mdash; {{ config('app.name') }}</title>

    {{-- Favicons --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    {{-- Google Fonts: Outfit --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- DCPL UI CSS --}}
    <link rel="stylesheet" href="{{ route('dcpl.ui.css') }}?v={{ $dcplUiCssVersion ?? 'dev' }}">

    {{-- Additional CSS from the consuming package (pass via $packageCss slot) --}}
    @isset($packageCss){{ $packageCss }}@endisset

    {{-- Alpine.js — loaded by dcplibrary/ui since the layout uses Alpine internally --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Livewire styles --}}
    @livewireStyles

    {{-- Flux styles — only if livewire/flux is installed --}}
    @if(class_exists(\Livewire\Flux\Flux::class))
        @fluxStyles
    @endif
</head>
<body class="bg-gray-50 min-h-screen font-outfit">

{{-- ── Header ─────────────────────────────────────────────────────────────── --}}
<header class="w-full bg-white shadow-sm">

    {{-- Tier 1: Logo / App Name + User Controls --}}
    <div class="px-6 py-3 flex items-center justify-between border-b border-gray-200">

        {{-- Left: Logo + App Name --}}
        <div class="flex items-center gap-4">
            <x-dcpl::logo :href="$logoHref ?? url('/')" :show-name="false" />
            <span class="font-outfit text-2xl font-light tracking-widest uppercase text-dcpl-text select-none">
                {{ config('app.name') }}
            </span>
        </div>

        @php
            $authUser = auth()->user();
            $isAdmin  = method_exists($authUser, 'isAdmin')
                ? $authUser->isAdmin()
                : (($authUser->role ?? null) === 'admin');
        @endphp

        {{-- Right: Optional header links + Settings + Help + Avatar dropdown --}}
        <div class="flex items-center gap-3">
            @auth
            @isset($headerLinks)
                {{ $headerLinks }}
            @endisset

            @if($isAdmin && isset($settingsRoute))
            <a href="{{ $settingsRoute }}"
               class="hidden md:inline-flex items-center justify-center w-10 h-10 rounded-full border border-gray-300 bg-gray-50 text-gray-600 shadow-sm hover:text-white hover:bg-dcpl-blue hover:border-dcpl-blue transition-colors"
               aria-label="Settings"
               title="Settings">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </a>
            @endif

            {{-- Optional help link --}}
            @isset($helpUrl)
            <a href="{{ $helpUrl }}"
               target="_blank"
               rel="noopener noreferrer"
               class="hidden md:inline-flex items-center justify-center w-10 h-10 rounded-full border border-gray-300 bg-gray-50 text-gray-600 shadow-sm hover:text-white hover:bg-dcpl-blue hover:border-dcpl-blue transition-colors"
               aria-label="Help"
               title="Help"
               onclick="try { window.open(this.href, 'dcplHelp', 'width=980,height=720,scrollbars=yes'); return false; } catch (e) { return true; }">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.251 1.09-1.251 1.902v.75m0 3h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </a>
            @endisset

            {{-- Avatar dropdown --}}
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open"
                        class="hover:opacity-90 transition-opacity focus:outline-none focus:ring-2 focus:ring-offset-1 rounded-full"
                        style="--tw-ring-color: var(--dcpl-blue, #0075A3)">
                    <x-dcpl::avatar :name="$authUser->name ?? ''" size="md" />
                </button>

                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-56 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-gray-900/10 z-50"
                     style="display:none">

                    {{-- User info --}}
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-sm font-semibold text-gray-900">{{ $authUser->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $authUser->email }}</p>
                        @if($authUser->role ?? null)
                            <p class="text-xs font-medium mt-1" style="color: var(--dcpl-blue, #0075A3)">{{ ucfirst($authUser->role) }}</p>
                        @endif
                    </div>

                    {{-- Settings link — admin only; consuming package passes $settingsRoute --}}
                    @if($isAdmin && isset($settingsRoute))
                    <div class="py-1 border-b border-gray-100">
                        <a href="{{ $settingsRoute }}"
                           class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Settings
                        </a>
                    </div>
                    @endif

                    {{-- Extra dropdown items from consuming package --}}
                    @isset($dropdownItems)
                        <div class="py-1 border-b border-gray-100">
                            {{ $dropdownItems }}
                        </div>
                    @endisset

                    {{-- Logout --}}
                    <div class="py-1">
                        @isset($logoutRoute)
                        <form method="POST" action="{{ $logoutRoute }}">
                            @csrf
                            <button type="submit"
                                    class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Sign out
                            </button>
                        </form>
                        @endisset
                    </div>

                </div>
            </div>
            @endauth
        </div>
    </div>

    {{-- Tier 2: Primary navigation — DCPL teal bar --}}
    @isset($nav)
        {{-- Desktop --}}
        <nav class="hidden md:block w-full" style="background-color: var(--dcpl-blue, #0075A3);">
            <div class="flex w-full flex-wrap items-stretch">
                {{ $nav }}
            </div>
        </nav>

        {{-- Mobile --}}
        <div class="md:hidden" x-data="{ mobileOpen: false }" style="background-color: var(--dcpl-blue, #0075A3);">
            <div class="px-4 py-2 flex items-center justify-between">
                <span class="text-white text-sm font-semibold">
                    @yield('nav-active-label', config('app.name'))
                </span>
                <button @click="mobileOpen = !mobileOpen" class="text-white p-1 rounded hover:bg-black/10">
                    <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                    <svg x-show="mobileOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="display:none">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div x-show="mobileOpen" class="border-t border-white/20 pb-2" style="display:none">
                {{ $mobileNav ?? $nav }}
            </div>
        </div>
    @endisset

</header>

{{-- ── Main ─────────────────────────────────────────────────────────────────── --}}
<main class="max-w-7xl mx-auto px-6 py-8">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{ $slot }}

</main>

@livewireScripts

{{-- Flux scripts — only if livewire/flux is installed --}}
@if(class_exists(\Livewire\Flux\Flux::class))
    @fluxScripts
@endif

</body>
</html>
