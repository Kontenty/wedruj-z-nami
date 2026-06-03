<script>
let { object } = $props()
</script>

<div class="w-56 overflow-hidden rounded-xl bg-white">
    <img class="h-28 w-full object-cover" src={object.thumbnail_url || '/images/placeholder-object-thumb.jpg'} alt={object.title} />
    <div class="p-3">
        <h3 class="font-bold leading-tight">{object.title}</h3>
        <p class="mt-1 text-sm text-stone-600">{object.voivodeship?.name}</p>
    </div>
</div>
