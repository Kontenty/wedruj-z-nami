<script>
    import { onDestroy } from 'svelte';

    const debounceDelay = 500;

    let { value = $bindable(''), onSearch } = $props();
    let timer;

    onDestroy(() => {
        clearTimeout(timer);
    });

    function handleInput() {
        clearTimeout(timer);
        timer = setTimeout(() => onSearch?.(value), debounceDelay);
    }
</script>

<label class="flex flex-col gap-2 text-sm font-medium text-stone-700">
    Szukaj
    <input
        class="rounded-2xl border border-stone-200 bg-white px-4 py-3 text-stone-950 outline-none ring-emerald-600 transition focus:ring-2 focus-visible:ring-2"
        placeholder="Wpisz nazwę obiektu"
        aria-label="Szukaj obiektu po nazwie"
        bind:value
        oninput={handleInput}
    />
</label>
