<script lang="ts">
  import { Link } from '@inertiajs/svelte';
  import type { Snippet } from 'svelte';
  import { Toaster } from '@/components/ui/sonner';

  let {
    children,
  }: {
    children?: Snippet;
  } = $props();

  let mobileMenuOpen = $state(false);
</script>

<div class="min-h-screen bg-background text-foreground">
  <a
    href="#main-content"
    class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:text-stone-950 focus:shadow-lg"
  >
    Przejdź do treści
  </a>

  <header class="sticky top-0 z-50 border-b border-gray-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center justify-between">
        <a
          href="/"
          class="flex items-center gap-2 text-xl font-bold text-primary focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
        >
          <img
            src="/images/pttk-logo.webp"
            alt="PTTK"
            width="40"
            height="40"
            class="size-10 shrink-0"
          />
          Wędruj z Nami
        </a>

        <nav
          class="hidden items-center space-x-8 md:flex"
          aria-label="Nawigacja główna"
        >
          <Link
            href="/katalog?view=map"
            class="text-gray-600 transition-colors hover:text-gray-900 focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
            >Mapa</Link
          >
          <Link
            href="/katalog"
            class="text-gray-600 transition-colors hover:text-gray-900 focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
            >Katalog</Link
          >
          <a
            href="/aktualnosci"
            class="text-gray-600 transition-colors hover:text-gray-900 focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
            >Aktualności</a
          >
        </nav>

        <button
          onclick={() => (mobileMenuOpen = !mobileMenuOpen)}
          class="p-2 text-gray-600 hover:text-gray-900 focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2 md:hidden"
          aria-label="Menu"
          aria-expanded={mobileMenuOpen}
        >
          <svg
            class="h-6 w-6"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            aria-hidden="true"
          >
            {#if !mobileMenuOpen}
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"
              />
            {:else}
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            {/if}
          </svg>
        </button>
      </div>
    </div>

    {#if mobileMenuOpen}
      <div class="border-t border-gray-200 bg-white md:hidden">
        <nav class="space-y-3 px-4 py-3" aria-label="Nawigacja mobilna">
          <Link
            href="/katalog?view=map"
            class="block py-1 text-gray-600 hover:text-gray-900 focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
            >Mapa</Link
          >
          <Link
            href="/katalog?view=list"
            class="block py-1 text-gray-600 hover:text-gray-900 focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
            >Katalog</Link
          >
          <a
            href="/aktualnosci"
            class="block py-1 text-gray-600 hover:text-gray-900 focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
            >Aktualności</a
          >
        </nav>
      </div>
    {/if}
  </header>

  <main id="main-content" tabindex="-1">
    {@render children?.()}
  </main>

  <footer class="border-t border-gray-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
        <div class="flex items-center gap-3">
          <img
            src="/images/pttk-logo-small.webp"
            alt="PTTK"
            width="28"
            height="28"
            class="size-7 shrink-0"
          />
          <p class="text-sm text-gray-600">
            &copy; {new Date().getFullYear()} Wędruj z Nami — Katalog obiektów krajoznawczych
            Polski
          </p>
        </div>
        <div class="flex gap-4">
          <a href="/" class="text-sm text-gray-600 hover:text-gray-900"
            >Strona główna</a
          >
          <a href="/katalog" class="text-sm text-gray-600 hover:text-gray-900"
            >Katalog</a
          >
          <a
            href="/aktualnosci"
            class="text-sm text-gray-600 hover:text-gray-900">Aktualności</a
          >
        </div>
      </div>
    </div>
  </footer>

  <Toaster />
</div>
