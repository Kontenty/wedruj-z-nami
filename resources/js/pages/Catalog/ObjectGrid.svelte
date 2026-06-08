<script>
  import Pagination from '@/components/Pagination.svelte';
  import ObjectCard from './ObjectCard.svelte';

  let {
    objects,
    isLoading = false,
    selectedObjectId = null,
    onHover,
    onPageChange,
  } = $props();

  const items = $derived(objects.data ?? []);
  const meta = $derived(objects.meta ?? {});
</script>

{#if isLoading}
  <div class="grid gap-3">
    {#each Array(6) as _, index (index)}
      <div
        class="flex animate-pulse items-center gap-3 rounded-[1.5rem] border border-stone-200 bg-white p-3 sm:gap-4 sm:p-4"
      >
        <div
          class="h-24 w-24 shrink-0 rounded-[1.125rem] bg-stone-200 sm:h-28 sm:w-28"
        ></div>
        <div class="flex-1 space-y-3">
          <div class="h-5 w-3/4 rounded-full bg-stone-200"></div>
          <div class="h-4 w-1/2 rounded-full bg-stone-200"></div>
          <div class="flex items-center justify-between gap-3 pt-2">
            <div class="h-3 w-28 rounded-full bg-stone-200"></div>
            <div class="h-4 w-24 rounded-full bg-stone-200"></div>
          </div>
        </div>
      </div>
    {/each}
  </div>
{:else}
  <div class="grid gap-3">
    {#each items as object (object.id)}
      <ObjectCard
        {object}
        selected={selectedObjectId === object.id}
        {onHover}
      />
    {/each}
  </div>
  {#if meta.last_page > 1}
    <Pagination
      currentPage={meta.current_page}
      lastPage={meta.last_page}
      {onPageChange}
    />
  {/if}
{/if}
