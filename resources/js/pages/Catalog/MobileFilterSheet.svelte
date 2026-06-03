<script>
import TopFilterBar from './TopFilterBar.svelte'
let { open = false, filters, objectTypes, voivodeships, onClose, onApply, onClear } = $props()
let draft = $state({ q: '', voivodeships: [], objectTypes: [], unesco: false })

$effect(() => {
    if (open) {
        draft = { ...filters }
    }
})
</script>

{#if open}
    <button type="button" aria-label="Zamknij filtry" class="fixed inset-0 z-50 bg-black/40 lg:hidden" onclick={onClose}></button>
    <div class="fixed inset-x-0 bottom-0 z-50 max-h-[88vh] overflow-auto rounded-t-3xl bg-white p-4 shadow-2xl lg:hidden" role="dialog" aria-modal="true" aria-labelledby="mobile-filters-heading">
        <div class="mb-3 flex items-center justify-between">
            <h2 id="mobile-filters-heading" class="text-lg font-bold">Filtry</h2>
            <button type="button" class="rounded-full px-3 py-1 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2" onclick={onClose}>Zamknij</button>
        </div>
        <TopFilterBar filters={draft} {objectTypes} {voivodeships} onApply={(next) => (draft = next)} onClear={() => (draft = { q: '', voivodeships: [], objectTypes: [], unesco: false })} />
        <div class="sticky bottom-0 mt-4 grid grid-cols-2 gap-3 bg-white pt-3">
            <button type="button" class="rounded-2xl border border-stone-300 px-4 py-3 font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2" onclick={onClear}>Wyczyść</button>
            <button type="button" class="rounded-2xl bg-emerald-700 px-4 py-3 font-semibold text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2" onclick={() => onApply?.(draft)}>Zastosuj</button>
        </div>
    </div>
{/if}
