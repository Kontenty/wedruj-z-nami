<script>
let { onClear } = $props()
</script>

<div class="rounded-3xl border border-dashed border-stone-300 bg-white p-10 text-center">
    <h2 class="text-xl font-bold">Nie znaleziono obiektów...</h2>
    <p class="mt-2 text-stone-600">Zmień kryteria wyszukiwania albo wyczyść filtry.</p>
    <button class="mt-5 rounded-2xl bg-emerald-700 px-5 py-3 font-semibold text-white" onclick={onClear}>Wyczyść filtry</button>
</div>
