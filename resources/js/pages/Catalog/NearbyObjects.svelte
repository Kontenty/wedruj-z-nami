<script>
  import { Link } from '@inertiajs/svelte';
  import { index as catalogIndex } from '@/routes/catalog';

  let { nearby = [] } = $props();
</script>

<section
  id="nearby-objects"
  class="mt-12 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm"
  aria-labelledby="nearby-objects-heading"
>
  <div class="mb-4 flex items-start justify-between gap-4">
    <div>
      <h2
        id="nearby-objects-heading"
        class="font-heading text-xl font-semibold"
      >
        Obiekty w pobliżu
      </h2>
      <p class="mt-1 text-sm text-stone-500">
        Do 3 najbliższych opublikowanych obiektów w promieniu 20 km.
      </p>
    </div>
  </div>

  {#if nearby.length > 0}
    <div class="grid gap-4 md:grid-cols-3">
      {#each nearby as nearbyObject (nearbyObject.id)}
        <Link
          href={nearbyObject.url}
          class="group overflow-hidden rounded-2xl border border-stone-200 bg-stone-50 transition hover:border-emerald-300 hover:bg-white hover:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
        >
          <img
            src={nearbyObject.thumbnail_url ||
              '/images/placeholder-object-thumb.jpg'}
            alt={nearbyObject.title}
            class="h-40 w-full object-cover"
            loading="lazy"
          />
          <div class="flex flex-col gap-2 p-4">
            <div class="flex items-start justify-between gap-3">
              <h3
                class="font-semibold leading-tight text-stone-900 group-hover:text-emerald-800"
              >
                {nearbyObject.title}
              </h3>
              {#if nearbyObject.is_unesco}
                <span
                  class="rounded-full bg-amber-100 px-2 py-1 text-[11px] font-bold text-amber-900"
                  >UNESCO</span
                >
              {/if}
            </div>

            {#if nearbyObject.voivodeship?.name}
              <p class="text-sm text-stone-600">
                {nearbyObject.voivodeship.name}
              </p>
            {/if}
          </div>
        </Link>
      {/each}
    </div>
  {:else}
    <div class="rounded-2xl bg-stone-50 p-5 text-sm text-stone-600">
      <p>Nie znaleźliśmy innych opublikowanych obiektów w pobliżu.</p>
      <Link
        href={catalogIndex.url()}
        class="mt-3 inline-flex items-center gap-1 font-medium text-emerald-700 hover:underline focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
      >
        Przejdź do katalogu →
      </Link>
    </div>
  {/if}
</section>
