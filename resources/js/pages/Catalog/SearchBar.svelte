<script>
let { value = '', onSearch } = $props()
let query = $state('')
let timer

$effect(() => {
    query = value || ''
})

function handleInput() {
    clearTimeout(timer)
    timer = setTimeout(() => onSearch?.(query), 350)
}
</script>

<label class="flex flex-col gap-2 text-sm font-medium text-stone-700">
    Szukaj
    <input class="rounded-2xl border border-stone-200 bg-white px-4 py-3 text-stone-950 outline-none ring-emerald-600 transition focus:ring-2" placeholder="Wpisz nazwę obiektu" bind:value={query} oninput={handleInput} />
</label>
