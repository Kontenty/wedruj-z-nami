<script>
  import { Link } from '@inertiajs/svelte';
  import MapPin from '@lucide/svelte/icons/map-pin';
  import UNESCOIcon from '@/components/UNESCOIcon.svelte';
  import { show as catalogShow } from '@/routes/catalog';
  import { show as collectionShow } from '@/routes/catalog/collection';

  let { object, selected = false, onHover } = $props();
  const isCollection = $derived(object.type === 'collection');
  const href = $derived(
    isCollection
      ? collectionShow.url(object.slug)
      : catalogShow.url(object.slug),
  );
  const locationLabel = $derived(
    [object.locality?.name, object.locality?.voivodeship?.name]
      .filter(Boolean)
      .join(', '),
  );
</script>

<Link
  {href}
  id={`catalog-card-${object.type}-${object.id}`}
  class="group glass-hover glow-level-1 block overflow-hidden rounded-3xl border bg-white transition hover:-translate-y-0.5 hover:shadow-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 {selected
    ? 'border-emerald-500 bg-emerald-50/40 ring-2 ring-emerald-200'
    : 'border-stone-200'}"
  onmouseenter={() => !isCollection && onHover?.(object.id)}
  onmouseleave={() => !isCollection && onHover?.(null)}
>
  <div class="flex items-stretch gap-3 p-3 sm:gap-4 sm:p-4">
    <div
      class="relative h-24 w-24 shrink-0 overflow-hidden rounded-[1.125rem] bg-stone-100 sm:h-28 sm:w-28"
    >
      <picture>
        {#if object.thumbnail_webp_url}
          <source srcset={object.thumbnail_webp_url} type="image/webp" />
        {/if}
        <img
          class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
          src={object.thumbnail_url || '/images/placeholder-object-thumb.webp'}
          alt={object.title}
          loading="lazy"
        />
      </picture>
      <div
        class="absolute inset-x-0 bottom-0 h-10 bg-linear-to-t from-[#1a4a26]/15 to-transparent"
      ></div>
    </div>

    <div class="flex min-w-0 flex-1 flex-col">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          {#if isCollection}
            <p
              class="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-amber-800"
            >
              Kolekcja tematyczna
            </p>
          {:else if locationLabel}
            <p
              class="mb-2 flex items-center gap-1 text-sm font-semibold text-emerald-800"
            >
              <MapPin class="size-4 shrink-0" />
              {locationLabel}
            </p>
          {/if}
          <h3
            class="text-base leading-tight tracking-tight text-stone-950 transition-colors group-hover:text-emerald-900 sm:text-lg"
          >
            {object.title}
          </h3>
          {#if isCollection && object.description}
            <p class="mt-2 line-clamp-2 text-sm leading-5 text-stone-600">
              {object.description}
            </p>
          {/if}
        </div>

        {#if object.is_unesco}
          <span
            class="inline-flex shrink-0 rounded-full bg-amber-100 p-2 text-amber-700 ring-1 ring-amber-400 transition-colors group-hover:bg-amber-200/80 group-hover:text-amber-800 {selected
              ? 'bg-amber-200 text-amber-900 ring-amber-300'
              : ''}"
          >
            <UNESCOIcon class="size-4 sm:size-5" />
          </span>
        {/if}
      </div>

      <div class="flex justify-end mt-auto">
        <span
          class="inline-flex items-center gap-2 text-sm text-emerald-700 transition-colors group-hover:text-emerald-800"
        >
          {isCollection ? 'Odkryj kolekcję' : 'Zobacz szczegóły'}
          <span aria-hidden="true">→</span>
        </span>
      </div>
    </div>
  </div>
</Link>
