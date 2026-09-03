<header class="sticky top-0 z-50 border-b border-gray-200 bg-white" x-data="{ open: false }">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-primary focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                <img src="/images/pttk-logo.webp" alt="PTTK" width="40" height="40" class="size-10 shrink-0" />
                Wędruj z Nami
            </a>

            <nav class="hidden items-center space-x-8 md:flex" aria-label="Nawigacja główna">
                <a href="{{ route('catalog.index') }}" class="text-gray-600 transition-colors hover:text-gray-900 focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">Katalog</a>
                <a href="{{ route('news.index') }}" class="text-gray-600 transition-colors hover:text-gray-900 focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">Aktualności</a>
                @auth
                    <a href="{{ route('filament.admin.pages.dashboard') }}" class="text-gray-600 transition-colors hover:text-gray-900 focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">Panel CMS</a>
                @endauth
            </nav>

            <button
                @click="open = !open"
                class="p-2 text-gray-600 hover:text-gray-900 focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2 md:hidden"
                aria-label="Menu"
                :aria-expanded="open.toString()">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="border-t border-gray-200 bg-white md:hidden">
        <nav class="space-y-3 px-4 py-3" aria-label="Nawigacja mobilna">
            <a href="{{ route('catalog.index') }}" class="block py-1 text-gray-600 hover:text-gray-900 focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">Katalog</a>
            <a href="{{ route('news.index') }}" class="block py-1 text-gray-600 hover:text-gray-900 focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">Aktualności</a>
            @auth
                <a href="{{ route('filament.admin.pages.dashboard') }}" class="block py-1 text-gray-600 hover:text-gray-900 focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">Panel CMS</a>
            @endauth
        </nav>
    </div>
</header>
