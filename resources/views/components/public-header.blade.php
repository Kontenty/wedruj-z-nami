<header class="sticky top-0 z-50 bg-white border-b border-gray-200" x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="{{ route('home') }}" class="text-xl font-bold text-primary">
                Kanon
            </a>

            <nav class="hidden md:flex items-center space-x-8" aria-label="Nawigacja główna">
                <a href="/katalog" class="text-gray-600 hover:text-gray-900 transition-colors">Mapa</a>
                <a href="/katalog?view=list" class="text-gray-600 hover:text-gray-900 transition-colors">Katalog</a>
                <a href="{{ route('news.index') }}" class="text-gray-600 hover:text-gray-900 transition-colors">Aktualności</a>
            </nav>

            <button
                @click="open = !open"
                class="md:hidden p-2 text-gray-600 hover:text-gray-900"
                aria-label="Menu"
                :aria-expanded="open.toString()"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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
        class="md:hidden border-t border-gray-200 bg-white"
    >
        <nav class="px-4 py-3 space-y-3" aria-label="Nawigacja mobilna">
            <a href="/katalog" class="block text-gray-600 hover:text-gray-900 py-1">Mapa</a>
            <a href="/katalog?view=list" class="block text-gray-600 hover:text-gray-900 py-1">Katalog</a>
            <a href="{{ route('news.index') }}" class="block text-gray-600 hover:text-gray-900 py-1">Aktualności</a>
        </nav>
    </div>
</header>
