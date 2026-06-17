<script>
  import SearchBar from './SearchBar.svelte';
  let {
    filters,
    objectTypes,
    voivodeships,
    onApply,
    onClear,
    class: className = '',
  } = $props();

  function apply(patch) {
    onApply?.({ ...filters, ...patch });
  }
</script>

<aside
  class={className +
    ' rounded-3xl border border-stone-200 bg-white p-4 shadow-sm'}
>
  <div class="flex flex-col gap-5">
    <SearchBar value={filters.q} onSearch={(q) => apply({ q })} />

    <label class="flex flex-col gap-2 text-sm font-medium text-stone-700">
      Województwo
      <select
        class="rounded-2xl border border-stone-200 bg-white px-4 py-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
        value={filters.wojewodztwo}
        aria-label="Filtruj według województwa"
        onchange={(event) => apply({ wojewodztwo: event.currentTarget.value })}
      >
        <option value="">Wszystkie</option>
        {#each voivodeships as voivodeship (voivodeship.id)}
          <option value={voivodeship.slug}>{voivodeship.name}</option>
        {/each}
      </select>
    </label>

    <fieldset class="flex flex-col gap-2">
      <legend class="text-sm font-medium text-stone-700">Typ obiektu</legend>
      <div
        class="flex max-h-80 flex-col gap-1 overflow-auto rounded-2xl border border-stone-200 p-2"
      >
        {#each objectTypes.data ?? objectTypes as type (type.id)}
          <button
            type="button"
            class="rounded-lg px-2 py-1 text-left text-sm hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
            class:bg-emerald-100={String(filters.objectType) ===
              String(type.id)}
            aria-pressed={String(filters.objectType) === String(type.id)}
            onclick={() => apply({ objectType: type.id })}>{type.name}</button
          >
        {/each}
      </div>
    </fieldset>

    <label
      class="flex items-center gap-3 rounded-2xl border border-stone-200 p-3 text-sm font-medium text-stone-700"
    >
      <input
        type="checkbox"
        checked={filters.unesco}
        aria-label="Pokaż tylko obiekty UNESCO"
        onchange={(event) => apply({ unesco: event.currentTarget.checked })}
      />
      Tylko UNESCO
    </label>

    <button
      type="button"
      class="rounded-2xl border border-stone-300 px-4 py-3 text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
      onclick={onClear}>Wyczyść filtry</button
    >
  </div>
</aside>
