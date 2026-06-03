<script>
    import { Link } from '@inertiajs/svelte';
    import { show as catalogShow } from '@/routes/catalog';

    let { object, selected = false, onHover } = $props();
</script>

<Link
    href={catalogShow.url(object.slug)}
    class="block overflow-hidden rounded-3xl border bg-white shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 {selected ? 'border-emerald-500' : 'border-stone-200'}"
    onmouseenter={() => onHover?.(object.id)}
    onmouseleave={() => onHover?.(null)}
>
    <img class="h-44 w-full object-cover" src={object.thumbnail_url || '/images/placeholder-object-card.jpg'} alt={object.title} loading="lazy" />
    <div class="flex flex-col gap-2 p-4">
        <div class="flex items-start justify-between gap-3">
            <h3 class="font-bold leading-tight">{object.title}</h3>
            {#if object.is_unesco}<span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-bold text-amber-900">UNESCO</span>{/if}
        </div>
        <p class="text-sm text-stone-600">{object.voivodeship?.name}</p>
        <p class="line-clamp-3 text-sm text-stone-700">{object.description}</p>
    </div>
</Link>
