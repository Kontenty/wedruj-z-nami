<script>
  import { Link } from '@inertiajs/svelte';
  import MapPin from 'lucide-svelte/icons/map-pin';
  import UNESCOIcon from '@/components/UNESCOIcon.svelte';
  import { show as catalogShow } from '@/routes/catalog';

  let { object, selected = false, onHover } = $props();
  const locationLabel = $derived(
    [object.locality, object.voivodeship?.name].filter(Boolean).join(', '),
  );
</script>

<Link
  href={catalogShow.url(object.slug)}
  id={`object-card-${object.id}`}
  class="group glass-hover glow-level-1 block overflow-hidden rounded-3xl border bg-white transition hover:-translate-y-0.5 hover:shadow-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 {selected
    ? 'border-emerald-500 bg-emerald-50/40 ring-2 ring-emerald-200'
    : 'border-stone-200'}"
  onmouseenter={() => onHover?.(object.id)}
  onmouseleave={() => onHover?.(null)}
>
  <div class="flex items-stretch gap-3 p-3 sm:gap-4 sm:p-4">
    <div
      class="relative h-24 w-24 shrink-0 overflow-hidden rounded-[1.125rem] bg-stone-100 sm:h-28 sm:w-28"
    >
      <img
        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
        src={object.primary_image_url ||
          object.thumbnail_url ||
          '/images/placeholder-object-card.jpg'}
        alt={object.title}
        loading="lazy"
      />
      <div
        class="absolute inset-x-0 bottom-0 h-10 bg-linear-to-t from-[#1a4a26]/15 to-transparent"
      ></div>
    </div>

    <div class="flex flex-col ftitlmin-w-0 flex-1">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <h3
            class="text-base font-black leading-tight tracking-tight text-stone-950 transition-colors group-hover:text-emerald-900 sm:text-lg"
          >
            {object.title}
          </h3>
          {#if locationLabel}
            <p
              class="mt-3 flex items-center gap-1 text-sm font-semibold text-emerald-800"
            >
              <MapPin class="size-4 shrink-0" />
              {locationLabel}
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
          class="inline-flex items-center gap-2 text-sm font-black text-emerald-700 transition-colors group-hover:text-emerald-800"
        >
          Zobacz szczegóły <span aria-hidden="true">→</span>
        </span>
      </div>
    </div>
  </div>
</Link>
