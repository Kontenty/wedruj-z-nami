<script>
let { activeView = $bindable('map') } = $props()
</script>

<div class="grid grid-cols-2 rounded-2xl bg-stone-200 p-1 md:hidden">
    <button class="rounded-xl px-4 py-2 text-sm font-bold" class:bg-white={activeView === 'map'} onclick={() => (activeView = 'map')}>Mapa</button>
    <button class="rounded-xl px-4 py-2 text-sm font-bold" class:bg-white={activeView === 'list'} onclick={() => (activeView = 'list')}>Lista</button>
</div>
