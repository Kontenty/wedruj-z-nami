<script>
  import { Link } from '@inertiajs/svelte';
  import ArrowUpRight from 'lucide-svelte/icons/arrow-up-right';
  import Info from 'lucide-svelte/icons/info';
  import MapPin from 'lucide-svelte/icons/map-pin';
  import X from 'lucide-svelte/icons/x';
  import { show as catalogShow } from '@/routes/catalog';

  let { object, onClose } = $props();

  const imageUrl = $derived(
    object.primary_image_url ||
      object.thumbnail_url ||
      '/images/placeholder-object-thumb.jpg',
  );
  const locationLabel = $derived(
    [object.locality, object.voivodeship?.name].filter(Boolean).join(', '),
  );
  const infoLabel = $derived(
    object.objectTypes?.[0]?.name || 'Obiekt turystyczny',
  );
</script>

<article
  class="w-60 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-2xl shadow-emerald-950/20 ring-1 ring-white/70"
>
  <div class="relative">
    <Link
      href={catalogShow.url(object.slug)}
      aria-label={`Zobacz szczegóły: ${object.title}`}
      class="group block focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
    >
      <img class="h-36 w-full object-cover" src={imageUrl} alt={object.title} />

      <div
        class="absolute inset-0 bg-linear-to-t from-stone-950/80 via-stone-950/30 to-transparent"
      ></div>

      <div
        class="absolute inset-0 flex items-center justify-center bg-stone-950/65 opacity-100 transition duration-200 sm:opacity-0 sm:group-hover:opacity-100 sm:group-focus-visible:opacity-100"
      >
        <span
          class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-black text-emerald-900 shadow-lg shadow-stone-950/30"
        >
          <ArrowUpRight class="size-4" />
          Zobacz więcej
        </span>
      </div>
    </Link>

    <button
      type="button"
      aria-label="Zamknij popup"
      onclick={() => onClose?.()}
      class="absolute right-1 top-1 flex size-6 items-center justify-center rounded-full bg-white/95 text-stone-700 shadow-lg shadow-stone-950/20 ring-1 ring-stone-900/10 transition hover:bg-stone-100 hover:text-stone-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-950/30"
    >
      <X class="size-4 hover:size-5" />
    </button>
  </div>

  <div class="space-y-3 p-4">
    <div class="flex items-center gap-2">
      <span
        class="inline-flex min-w-0 items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-800"
      >
        <Info class="size-3.5 shrink-0" />
        <span class="truncate font-heading">{infoLabel}</span>
      </span>
    </div>

    <div class="space-y-2">
      <h3 class="text-base font-semibold leading-tight text-stone-900">
        {object.title}
      </h3>

      {#if locationLabel}
        <p class="flex items-start gap-1.5 text-sm font-semibold">
          <MapPin class="mt-0.5 size-4 shrink-0 text-emerald-700" />
          <span class="text-emerald-600">{locationLabel}</span>
        </p>
      {/if}
    </div>
  </div>
</article>
