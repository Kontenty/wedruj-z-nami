<script>
  import { Link } from '@inertiajs/svelte';
  import MapPin from 'lucide-svelte/icons/map-pin';
  import UNESCOIcon from '@/components/UNESCOIcon.svelte';
  import { index as catalogIndex } from '@/routes/catalog';

  let { nearby = [] } = $props();

  function locationLabel(nearbyObject) {
    return [
      nearbyObject.locality?.name,
      nearbyObject.locality?.voivodeship?.name,
    ]
      .filter(Boolean)
      .join(', ');
  }
</script>

<section aria-labelledby="nearby-objects-heading">
  <div class="mb-5">
    <h2
      id="nearby-objects-heading"
      class="font-heading text-2xl font-bold text-stone-950"
    >
      W pobliżu
    </h2>
    <p class="mt-1 text-sm text-stone-500">
      Do 3 najbliższych opublikowanych obiektów w promieniu 20 km.
    </p>
  </div>

  {#if nearby.length > 0}
    <div class="space-y-4">
      {#each nearby as nearbyObject (nearbyObject.id)}
        <Link
          href={nearbyObject.url}
          class="group flex gap-4 rounded-[1.5rem] border border-stone-200 bg-white p-3 shadow-sm transition hover:border-emerald-300 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
        >
          <div
            class="h-20 w-20 shrink-0 overflow-hidden rounded-[1rem] bg-stone-100"
          >
            <picture>
              {#if nearbyObject.thumbnail_webp_url}
                <source
                  srcset={nearbyObject.thumbnail_webp_url}
                  type="image/webp"
                />
              {/if}
              <img
                src={nearbyObject.thumbnail_url ||
                  '/images/placeholder-object-thumb.jpg'}
                alt={nearbyObject.title}
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                loading="lazy"
              />
            </picture>
          </div>

          <div class="flex min-w-0 flex-1 flex-col justify-center">
            <div class="flex items-start justify-between gap-3">
              <h3
                class="text-sm font-bold leading-5 text-stone-950 transition-colors group-hover:text-emerald-800"
              >
                {nearbyObject.title}
              </h3>

              {#if nearbyObject.is_unesco}
                <span
                  class="inline-flex shrink-0 rounded-full bg-amber-100 p-2 text-amber-700 ring-1 ring-amber-300"
                >
                  <UNESCOIcon class="size-3.5" title="Obiekt UNESCO" />
                </span>
              {/if}
            </div>

            {#if locationLabel(nearbyObject)}
              <p class="mt-2 flex items-center gap-1.5 text-sm text-stone-500">
                <MapPin class="size-3.5 shrink-0 text-emerald-700" />
                {locationLabel(nearbyObject)}
              </p>
            {/if}
          </div>
        </Link>
      {/each}
    </div>
  {:else}
    <div
      class="rounded-[1.5rem] border border-dashed border-stone-300 bg-white p-5 text-sm text-stone-600"
    >
      <p>Nie znaleźliśmy innych opublikowanych obiektów w pobliżu.</p>
      <Link
        href={catalogIndex.url()}
        class="mt-3 inline-flex items-center gap-1 font-semibold text-emerald-700 hover:underline focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
      >
        Przejdź do katalogu →
      </Link>
    </div>
  {/if}
</section>
