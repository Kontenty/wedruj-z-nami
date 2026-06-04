<script>
    import { Link } from '@inertiajs/svelte';
    import { show as catalogShow } from '@/routes/catalog';

    let { object, selected = false, onHover } = $props();

    const objectTypes = $derived((object.objectTypes ?? []).slice(0, 3));
</script>

<Link
    href={catalogShow.url(object.slug)}
    class="group glass-hover glow-level-1 block overflow-hidden rounded-[1.75rem] border bg-white transition hover:-translate-y-0.5 hover:shadow-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 {selected ? 'border-emerald-500 ring-2 ring-emerald-200' : 'border-stone-200'}"
    onmouseenter={() => onHover?.(object.id)}
    onmouseleave={() => onHover?.(null)}
>
    <div class="relative">
        <img class="h-48 w-full object-cover transition duration-500 group-hover:scale-105" src={object.primary_image_url || object.thumbnail_url || '/images/placeholder-object-card.jpg'} alt={object.title} loading="lazy" />
        <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-[#1a4a26]/60 via-[#2d6b38]/30 to-transparent"></div>
        {#if object.is_unesco}<span class="absolute left-3 top-3 rounded-full bg-amber-300 px-3 py-1 text-xs font-black uppercase tracking-wide text-amber-950 shadow-sm">UNESCO</span>{/if}
    </div>
    <div class="flex flex-col gap-3 p-4">
        <div class="flex flex-col gap-1">
            <h3 class="text-lg font-black leading-tight tracking-tight text-stone-950">{object.title}</h3>
            <p class="text-sm font-semibold text-emerald-800">{object.locality ? `${object.locality}, ` : ''}{object.voivodeship?.name}</p>
        </div>
        <p class="line-clamp-3 text-sm leading-6 text-stone-700">{object.description}</p>
        {#if objectTypes.length}
            <div class="flex flex-wrap gap-1.5">
                {#each objectTypes as type (type.id)}
                    <span class="glass-panel-light rounded-full px-2.5 py-1 text-xs font-semibold text-stone-700">{type.name}</span>
                {/each}
            </div>
        {/if}
        <span class="mt-1 inline-flex items-center gap-2 text-sm font-black text-emerald-700">Zobacz szczegóły <span aria-hidden="true">→</span></span>
    </div>
</Link>
