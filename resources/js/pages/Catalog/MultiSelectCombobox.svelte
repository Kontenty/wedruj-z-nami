<script>
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import ChevronUp from 'lucide-svelte/icons/chevron-up';

    let {
        label,
        options = [],
        selected = [],
        placeholder = 'Wybierz',
        onChange,
    } = $props();

    let open = $state(false);
    let search = $state('');

    const selectedValues = $derived((selected ?? []).map(String));
    const selectedOptions = $derived(
        options.filter((option) =>
            selectedValues.includes(String(option.value)),
        ),
    );
    const filteredOptions = $derived(
        options.filter((option) =>
            option.label.toLowerCase().includes(search.toLowerCase()),
        ),
    );

    function toggle(value) {
        const stringValue = String(value);
        const next = selectedValues.includes(stringValue)
            ? selectedValues.filter((item) => item !== stringValue)
            : [...selectedValues, stringValue];

        onChange?.(next);
    }

    function clear() {
        onChange?.([]);
    }
</script>

<div class="relative">
    <span
        class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-stone-500"
        >{label}</span
    >
    <button
        type="button"
        class="flex min-h-12 w-full items-center justify-between gap-3 rounded-2xl border border-stone-200 bg-white px-4 py-3 text-left shadow-sm transition hover:border-emerald-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
        aria-expanded={open}
        onclick={() => (open = !open)}
    >
        <span class="flex min-w-0 flex-1 flex-wrap gap-1.5">
            {#if selectedOptions.length}
                {#each selectedOptions.slice(0, 2) as option (option.value)}
                    <span
                        class="max-w-36 truncate rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-900"
                        >{option.label}</span
                    >
                {/each}
                {#if selectedOptions.length > 2}
                    <span
                        class="rounded-full bg-stone-100 px-2.5 py-1 text-xs font-semibold text-stone-700"
                        >+{selectedOptions.length - 2}</span
                    >
                {/if}
            {:else}
                <span class="text-sm text-stone-500">{placeholder}</span>
            {/if}
        </span>
        {#if open}
            <ChevronUp class="size-5 shrink-0 text-stone-500" />
        {:else}
            <ChevronDown class="size-5 shrink-0 text-stone-500" />
        {/if}
    </button>

    {#if open}
        <div
            class="absolute z-50 mt-2 w-full overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-2xl"
        >
            <div class="border-b border-stone-100 p-3">
                <input
                    class="w-full rounded-2xl border border-stone-200 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600"
                    bind:value={search}
                    placeholder="Szukaj…"
                />
            </div>
            <div class="max-h-64 overflow-auto p-2">
                {#each filteredOptions as option (option.value)}
                    <button
                        type="button"
                        class="flex w-full items-center gap-3 rounded-2xl px-3 py-2 text-left text-sm hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600"
                        onclick={() => toggle(option.value)}
                    >
                        <span
                            class={(selectedValues.includes(
                                String(option.value),
                            )
                                ? 'border-emerald-700 bg-emerald-700'
                                : 'border-stone-300 bg-white') +
                                ' grid h-5 w-5 place-items-center rounded-md border text-white'}
                            >{selectedValues.includes(String(option.value))
                                ? '✓'
                                : ''}</span
                        >
                        <span>{option.label}</span>
                    </button>
                {/each}
                {#if filteredOptions.length === 0}
                    <p class="px-3 py-6 text-center text-sm text-stone-500">
                        Brak wyników
                    </p>
                {/if}
            </div>
            <div
                class="flex items-center justify-between border-t border-stone-100 p-3"
            >
                <button
                    type="button"
                    class="text-sm font-semibold text-stone-600 underline"
                    onclick={clear}>Wyczyść</button
                >
                <button
                    type="button"
                    class="rounded-full bg-emerald-700 px-4 py-2 text-sm font-semibold text-white"
                    onclick={() => (open = false)}>Gotowe</button
                >
            </div>
        </div>
    {/if}
</div>
