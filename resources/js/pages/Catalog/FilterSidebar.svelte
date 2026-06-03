<script>
import SearchBar from './SearchBar.svelte'
let { filters, objectTypes, voivodeships, onApply, onClear, class: className = '' } = $props()

function apply(patch) {
    onApply?.({ ...filters, ...patch })
}
</script>

<aside class={className + ' rounded-3xl border border-stone-200 bg-white p-4 shadow-sm'}>
    <div class="flex flex-col gap-5">
        <SearchBar value={filters.q} onSearch={(q) => apply({ q })} />

        <label class="flex flex-col gap-2 text-sm font-medium text-stone-700">
            Województwo
            <select class="rounded-2xl border border-stone-200 bg-white px-4 py-3" value={filters.wojewodztwo} onchange={(event) => apply({ wojewodztwo: event.currentTarget.value })}>
                <option value="">Wszystkie</option>
                {#each voivodeships as voivodeship (voivodeship.id)}
                    <option value={voivodeship.slug}>{voivodeship.name}</option>
                {/each}
            </select>
        </label>

        <div class="flex flex-col gap-2">
            <p class="text-sm font-medium text-stone-700">Typ obiektu</p>
            <div class="flex max-h-80 flex-col gap-1 overflow-auto rounded-2xl border border-stone-200 p-2">
                {#each objectTypes.data ?? objectTypes as type (type.id)}
                    <details open>
                        <summary class="cursor-pointer rounded-xl px-2 py-1 font-medium hover:bg-stone-100">{type.name}</summary>
                        <button class="ml-3 rounded-lg px-2 py-1 text-left text-sm hover:bg-emerald-50" class:bg-emerald-100={String(filters.objectType) === String(type.id)} onclick={() => apply({ objectType: type.id })}>{type.name}</button>
                        {#each type.children ?? [] as child (child.id)}
                            <details class="ml-3" open>
                                <summary class="cursor-pointer rounded-xl px-2 py-1 text-sm hover:bg-stone-100">{child.name}</summary>
                                <button class="ml-3 rounded-lg px-2 py-1 text-left text-sm hover:bg-emerald-50" class:bg-emerald-100={String(filters.objectType) === String(child.id)} onclick={() => apply({ objectType: child.id })}>{child.name}</button>
                                {#each child.children ?? [] as grandchild (grandchild.id)}
                                    <button class="ml-6 rounded-lg px-2 py-1 text-left text-sm hover:bg-emerald-50" class:bg-emerald-100={String(filters.objectType) === String(grandchild.id)} onclick={() => apply({ objectType: grandchild.id })}>{grandchild.name}</button>
                                {/each}
                            </details>
                        {/each}
                    </details>
                {/each}
            </div>
        </div>

        <label class="flex items-center gap-3 rounded-2xl border border-stone-200 p-3 text-sm font-medium text-stone-700">
            <input type="checkbox" checked={filters.unesco} onchange={(event) => apply({ unesco: event.currentTarget.checked })} />
            Tylko UNESCO
        </label>

        <button class="rounded-2xl border border-stone-300 px-4 py-3 text-sm font-semibold" onclick={onClear}>Wyczyść filtry</button>
    </div>
</aside>
