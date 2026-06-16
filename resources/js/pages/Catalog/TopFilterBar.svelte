<script>
  import MultiSelectCombobox from './MultiSelectCombobox.svelte';
  import SearchBar from './SearchBar.svelte';

  let {
    filters,
    objectTypes,
    voivodeships,
    onApply,
    onClear,
    class: className = '',
  } = $props();

  const typeOptions = $derived(
    (objectTypes.data ?? objectTypes).map((type) => ({
      value: type.id,
      label: type.name,
    })),
  );
  const voivodeshipOptions = $derived(
    (voivodeships ?? []).map((voivodeship) => ({
      value: voivodeship.slug,
      label: voivodeship.name,
    })),
  );

  function apply(patch) {
    onApply?.({ ...filters, ...patch });
  }
</script>

<section
  class={className +
    ' relative z-20 rounded-4xl border border-stone-200 bg-white/95 p-4 shadow-sm backdrop-blur'}
  aria-label="Filtry katalogu"
>
  <div
    class="grid gap-3 lg:grid-cols-[minmax(18rem,1.4fr)_minmax(14rem,1fr)_minmax(14rem,1fr)_auto_auto] lg:items-end"
  >
    <SearchBar value={filters.q} onSearch={(q) => apply({ q })} />

    <MultiSelectCombobox
      label="Kategoria obiektu"
      options={typeOptions}
      selected={filters.objectTypes}
      placeholder="Wybierz kategorię"
      onChange={(objectTypes) => apply({ objectTypes })}
    />

    <MultiSelectCombobox
      label="Województwo"
      options={voivodeshipOptions}
      selected={filters.voivodeships}
      placeholder="Wybierz województwa"
      onChange={(voivodeships) => apply({ voivodeships })}
    />

    <button
      type="button"
      class={(filters.unesco
        ? 'border-amber-400 bg-amber-100 text-amber-950'
        : 'border-stone-200 bg-white text-stone-700') +
        ' min-h-12 rounded-2xl border px-4 py-3 text-sm font-bold shadow-sm transition hover:border-amber-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2'}
      aria-pressed={filters.unesco}
      onclick={() => apply({ unesco: !filters.unesco })}>UNESCO</button
    >

    <button
      type="button"
      class="min-h-12 rounded-2xl border border-stone-300 px-4 py-3 text-sm font-bold text-stone-700 transition hover:bg-stone-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
      onclick={onClear}>Wyczyść</button
    >
  </div>
</section>
