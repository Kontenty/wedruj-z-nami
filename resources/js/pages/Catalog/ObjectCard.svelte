<script>
let { object, selected = false, onHover } = $props()
</script>

<article id={`object-card-${object.id}`} class="overflow-hidden rounded-3xl border bg-white shadow-sm transition" class:border-emerald-500={selected} class:border-stone-200={!selected} onmouseenter={() => onHover?.(object.id)} onmouseleave={() => onHover?.(null)}>
    <img class="h-44 w-full object-cover" src={object.thumbnail_url || '/images/placeholder-object-card.jpg'} alt={object.title} />
    <div class="flex flex-col gap-2 p-4">
        <div class="flex items-start justify-between gap-3">
            <h3 class="font-bold leading-tight">{object.title}</h3>
            {#if object.is_unesco}<span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-bold text-amber-900">UNESCO</span>{/if}
        </div>
        <p class="text-sm text-stone-600">{object.voivodeship?.name}</p>
        <p class="line-clamp-3 text-sm text-stone-700">{object.description}</p>
    </div>
</article>
