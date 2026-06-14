<script>
  let { filters, voivodeships, objectTypes, onChange, onClear } = $props();

  const flatTypes = $derived(objectTypes.data ?? objectTypes);
  const chips = $derived(
    [
      filters.q
        ? { key: 'q', value: filters.q, label: `Szukaj: ${filters.q}` }
        : null,
      ...(filters.voivodeships ?? []).map((slug) => ({
        key: 'voivodeships',
        value: slug,
        label: voivodeships.find((v) => v.slug === slug)?.name ?? slug,
      })),
      ...(filters.objectTypes ?? []).map((id) => ({
        key: 'objectTypes',
        value: id,
        label:
          flatTypes.find((t) => String(t.id) === String(id))?.name ??
          'Typ obiektu',
      })),
      filters.unesco ? { key: 'unesco', value: true, label: 'UNESCO' } : null,
    ].filter(Boolean),
  );

  function remove(chip) {
    if (chip.key === 'unesco') {
      onChange?.({ ...filters, unesco: false });

      return;
    }

    if (chip.key === 'q') {
      onChange?.({ ...filters, q: '' });

      return;
    }

    onChange?.({
      ...filters,
      [chip.key]: (filters[chip.key] ?? []).filter(
        (value) => String(value) !== String(chip.value),
      ),
    });
  }
</script>

{#if chips.length}
  <div class="mb-4 flex flex-wrap items-center gap-2">
    {#each chips as chip (`${chip.key}-${chip.value}`)}
      <button
        class={(chip.key === 'unesco'
          ? 'bg-amber-100 text-amber-950'
          : 'bg-emerald-100 text-emerald-900') +
          ' rounded-full px-3 py-1 text-sm font-medium'}
        onclick={() => remove(chip)}>{chip.label} ×</button
      >
    {/each}
    <button
      class="rounded-full px-3 py-1 text-sm font-semibold text-stone-600 underline"
      onclick={onClear}>Wyczyść filtry</button
    >
  </div>
{/if}
