<script>
  import { Link } from '@inertiajs/svelte';
  import ArrowUpRight from '@lucide/svelte/icons/arrow-up-right';
  import ChevronRight from '@lucide/svelte/icons/chevron-right';
  import MapPin from '@lucide/svelte/icons/map-pin';
  import { index as catalogIndex, show as catalogShow } from '@/routes/catalog';

  let { collection } = $props();

  const objects = $derived(collection.sightseeingObjects ?? []);
</script>

<svelte:head>
  <title>{collection.name} — Wędruj z Nami</title>
  <meta name="description" content={collection.description} />
</svelte:head>

<main id="main-content" class="min-h-screen bg-[#fcfaf5] text-stone-950">
  <article class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
    <nav
      aria-label="Okruszki"
      class="mb-10 flex flex-wrap items-center gap-2 text-sm text-stone-500"
    >
      <Link
        href={catalogIndex.url()}
        class="rounded-sm font-medium text-stone-600 transition-colors hover:text-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
      >
        Katalog
      </Link>
      <ChevronRight class="size-4 shrink-0 text-stone-400" aria-hidden="true" />
      <span class="font-medium text-emerald-800" aria-current="page">
        {collection.name}
      </span>
    </nav>

    <header
      class="relative mb-14 overflow-hidden rounded-[2rem] border border-emerald-950/10 bg-[#e8efe2] px-6 py-9 sm:px-10 sm:py-12 lg:px-14 lg:py-16"
    >
      <div
        class="pointer-events-none absolute -right-12 -top-20 size-72 rounded-full border-[36px] border-white/35"
        aria-hidden="true"
      ></div>
      <div
        class="relative grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,0.8fr)] lg:items-center lg:gap-12"
      >
        <div>
          <p
            class="mb-5 text-xs font-bold uppercase tracking-[0.24em] text-emerald-800"
          >
            Kolekcja krajoznawcza
          </p>
          <h1
            class="font-heading text-4xl leading-[1.08] tracking-tight text-stone-950 sm:text-5xl lg:text-6xl"
          >
            {collection.name}
          </h1>
          {#if collection.description}
            <div
              class="prose prose-stone mt-6 max-w-2xl prose-headings:font-heading prose-headings:text-stone-950 prose-p:leading-8 prose-a:text-emerald-800 sm:prose-lg"
            >
              <!-- eslint-disable-next-line svelte/no-at-html-tags -->
              {@html collection.description}
            </div>
          {/if}
        </div>
        <img
          class="aspect-[4/3] w-full rounded-[1.5rem] object-cover shadow-lg shadow-emerald-950/10"
          src={collection.primary_image_url ||
            '/images/placeholder-object.webp'}
          alt={collection.name}
          loading="eager"
        />
      </div>
      <p
        class="relative mt-9 inline-flex items-center gap-2 border-t border-emerald-950/15 pt-4 text-sm font-semibold text-emerald-950 lg:col-span-2"
      >
        <span class="tabular-nums">{objects.length}</span>
        <span>obiektów do odkrycia</span>
      </p>
    </header>

    <section aria-labelledby="collection-objects-heading">
      <div
        class="mb-6 flex flex-col gap-2 border-b border-stone-200 pb-5 sm:flex-row sm:items-end sm:justify-between"
      >
        <div>
          <p
            class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-emerald-800"
          >
            Warto zobaczyć
          </p>
          <h2
            id="collection-objects-heading"
            class="font-heading text-2xl tracking-tight text-stone-950 sm:text-3xl"
          >
            Miejsca w tej kolekcji
          </h2>
        </div>
        <p class="text-sm text-stone-500">
          Kolejność zgodna z planem zwiedzania
        </p>
      </div>

      {#if objects.length}
        <ol class="divide-y divide-stone-200">
          {#each objects as object, index (object.id)}
            <li>
              <Link
                href={catalogShow.url(object.slug)}
                class="group grid min-h-32 grid-cols-[2.5rem_minmax(0,1fr)] items-center gap-4 py-5 focus-visible:rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-4 sm:grid-cols-[3rem_8rem_minmax(0,1fr)_auto] sm:gap-6 sm:py-6"
              >
                <span
                  class="font-heading text-xl tabular-nums text-emerald-800/70 sm:text-2xl"
                  aria-label={`Miejsce ${index + 1}`}
                >
                  {String(index + 1).padStart(2, '0')}
                </span>
                <img
                  class="col-start-2 row-span-3 h-24 w-full rounded-2xl object-cover sm:col-start-auto sm:row-span-1 sm:h-24"
                  src={object.thumbnail_url ||
                    '/images/placeholder-object-thumb.webp'}
                  alt={object.title}
                  loading="lazy"
                />
                <div class="col-span-2 min-w-0 sm:col-span-1">
                  {#if object.locality?.name}
                    <p
                      class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-emerald-800"
                    >
                      <MapPin class="size-3.5 shrink-0" aria-hidden="true" />
                      <span>
                        {object.locality
                          .name}{#if object.locality.voivodeship?.name}, {object
                            .locality.voivodeship.name}{/if}
                      </span>
                    </p>
                  {/if}
                  <h3
                    class="font-heading text-lg leading-snug text-stone-950 transition-colors group-hover:text-emerald-800 sm:text-xl"
                  >
                    {object.title}
                  </h3>
                  {#if object.description}
                    <p
                      class="mt-2 line-clamp-2 text-sm leading-6 text-stone-600"
                    >
                      {object.description}
                    </p>
                  {/if}
                </div>
                <ArrowUpRight
                  class="hidden size-5 text-stone-400 transition group-hover:-translate-y-0.5 group-hover:translate-x-0.5 group-hover:text-emerald-800 sm:block"
                  aria-hidden="true"
                />
              </Link>
            </li>
          {/each}
        </ol>
      {:else}
        <div
          class="rounded-2xl border border-dashed border-stone-300 px-6 py-12 text-center"
        >
          <p class="font-semibold text-stone-800">
            Ta kolekcja nie ma jeszcze przypisanych obiektów.
          </p>
          <Link
            href={catalogIndex.url()}
            class="mt-4 inline-flex min-h-11 items-center justify-center rounded-full bg-emerald-800 px-5 text-sm font-semibold text-white transition hover:bg-emerald-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 focus-visible:ring-offset-2"
          >
            Wróć do katalogu
          </Link>
        </div>
      {/if}
    </section>
  </article>
</main>
