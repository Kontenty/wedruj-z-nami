<script>
  import { ChevronLeft, ChevronRight } from 'lucide-svelte';

  let { currentPage, lastPage, onPageChange } = $props();

  const pages = $derived.by(() => {
    if (lastPage <= 5) {
      return Array.from({ length: lastPage }, (_, i) => i + 1);
    }

    const result = [];
    result.push(1);

    if (currentPage > 3) {
      result.push('...');
    }

    const start = Math.max(2, currentPage - 1);
    const end = Math.min(lastPage - 1, currentPage + 1);

    for (let i = start; i <= end; i++) {
      result.push(i);
    }

    if (currentPage < lastPage - 2) {
      result.push('...');
    }

    result.push(lastPage);

    return result;
  });
</script>

{#if lastPage > 1}
  <nav
    class="mt-6 flex items-center justify-center gap-2"
    aria-label="Nawigacja stronami"
  >
    <button
      type="button"
      disabled={currentPage === 1}
      onclick={() => onPageChange(currentPage - 1)}
      class="flex h-10 w-10 items-center justify-center rounded-xl text-sm font-medium text-stone-700 transition-colors hover:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent"
      aria-label="Poprzednia strona"
    >
      <ChevronLeft class="size-5" />
    </button>

    {#each pages as page, index (index)}
      {#if page === '...'}
        <span
          class="flex h-10 w-10 items-center justify-center text-sm text-stone-400"
        >
          &hellip;
        </span>
      {:else}
        <button
          type="button"
          onclick={() => onPageChange(page)}
          class="flex h-10 w-10 items-center justify-center rounded-xl text-sm font-medium transition-colors {page ===
          currentPage
            ? 'bg-emerald-700 text-white'
            : 'text-stone-700 hover:bg-stone-100'}"
          aria-label="Strona {page}"
          aria-current={page === currentPage ? 'page' : undefined}
        >
          {page}
        </button>
      {/if}
    {/each}

    <button
      type="button"
      disabled={currentPage === lastPage}
      onclick={() => onPageChange(currentPage + 1)}
      class="flex h-10 w-10 items-center justify-center rounded-xl text-sm font-medium text-stone-700 transition-colors hover:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent"
      aria-label="Następna strona"
    >
      <ChevronRight class="size-5" />
    </button>
  </nav>
{/if}
