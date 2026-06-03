<script>
import FilterSidebar from './FilterSidebar.svelte'
let { open = false, filters, objectTypes, voivodeships, onClose, onApply, onClear } = $props()
let draft = $state({ q: '', wojewodztwo: '', objectType: '', unesco: false })

$effect(() => {
    if (open) {
        draft = { ...filters }
    }
})
</script>

{#if open}
    <button type="button" aria-label="Zamknij filtry" class="fixed inset-0 z-50 bg-black/40 lg:hidden" onclick={onClose}></button>
    <div class="fixed inset-x-0 bottom-0 z-50 max-h-[88vh] overflow-auto rounded-t-3xl bg-white p-4 shadow-2xl lg:hidden">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-lg font-bold">Filtry</h2>
            <button class="rounded-full px-3 py-1 text-sm" onclick={onClose}>Zamknij</button>
        </div>
        <FilterSidebar filters={draft} {objectTypes} {voivodeships} onApply={(next) => (draft = next)} onClear={() => (draft = { q: '', wojewodztwo: '', objectType: '', unesco: false })} />
        <div class="sticky bottom-0 mt-4 grid grid-cols-2 gap-3 bg-white pt-3">
            <button class="rounded-2xl border border-stone-300 px-4 py-3 font-semibold" onclick={onClear}>Wyczyść</button>
            <button class="rounded-2xl bg-emerald-700 px-4 py-3 font-semibold text-white" onclick={() => onApply?.(draft)}>Zastosuj</button>
        </div>
    </div>
{/if}
