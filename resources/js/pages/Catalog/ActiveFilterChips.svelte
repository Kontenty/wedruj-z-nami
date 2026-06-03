<script>
let { filters, voivodeships, objectTypes, onChange, onClear } = $props()
const flatTypes = $derived((objectTypes.data ?? objectTypes).flatMap((t) => [t, ...(t.children ?? []), ...(t.children ?? []).flatMap((c) => c.children ?? [])]))
const chips = $derived([
    filters.q ? { key: 'q', label: `Szukaj: ${filters.q}` } : null,
    filters.wojewodztwo ? { key: 'wojewodztwo', label: voivodeships.find((v) => v.slug === filters.wojewodztwo)?.name ?? filters.wojewodztwo } : null,
    filters.objectType ? { key: 'objectType', label: flatTypes.find((t) => String(t.id) === String(filters.objectType))?.name ?? 'Typ obiektu' } : null,
    filters.unesco ? { key: 'unesco', label: 'Tylko UNESCO' } : null,
].filter(Boolean))
function remove(key) {
 onChange?.({ ...filters, [key]: key === 'unesco' ? false : '' }) 
}
</script>

{#if chips.length}
    <div class="mb-4 flex flex-wrap items-center gap-2">
        {#each chips as chip (chip.key)}
            <button class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-900" onclick={() => remove(chip.key)}>{chip.label} ×</button>
        {/each}
        <button class="rounded-full px-3 py-1 text-sm font-semibold text-stone-600 underline" onclick={onClear}>Wyczyść filtry</button>
    </div>
{/if}
