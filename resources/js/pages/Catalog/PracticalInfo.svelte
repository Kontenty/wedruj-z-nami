<script>
  import ExternalLink from 'lucide-svelte/icons/external-link';
  import Info from 'lucide-svelte/icons/info';

  let { openingHours, ticketPrices, accessibility, website } = $props();

  let hasData = $derived(
    openingHours || ticketPrices || accessibility || website,
  );
</script>

{#if hasData}
  <section
    class="relative overflow-hidden rounded-[1.75rem] bg-emerald-800 p-7 text-white shadow-lg print-keep-together"
  >
    <div class="absolute -right-6 -top-8 text-white/10">
      <Info class="size-32" stroke-width={1.25} />
    </div>

    <div class="relative">
      <h2 class="mb-6 flex items-center gap-2 font-heading text-2xl font-bold">
        <Info class="size-5" />
        Informacje praktyczne
      </h2>

      <dl class="space-y-5">
        {#if openingHours}
          <div>
            <dt
              class="mb-2 text-xs font-bold uppercase tracking-[0.22em] text-emerald-100/80"
            >
              Godziny otwarcia
            </dt>
            <dd class="whitespace-pre-line text-base leading-7 text-white">
              {openingHours}
            </dd>
          </div>
        {/if}

        {#if ticketPrices}
          <div class="border-t border-white/15 pt-5">
            <dt
              class="mb-2 text-xs font-bold uppercase tracking-[0.22em] text-emerald-100/80"
            >
              Ceny biletów
            </dt>
            <dd class="whitespace-pre-line text-base leading-7 text-white">
              {ticketPrices}
            </dd>
          </div>
        {/if}

        {#if accessibility}
          <div class="border-t border-white/15 pt-5">
            <dt
              class="mb-2 text-xs font-bold uppercase tracking-[0.22em] text-emerald-100/80"
            >
              Dostępność
            </dt>
            <dd class="whitespace-pre-line text-base leading-7 text-white">
              {accessibility}
            </dd>
          </div>
        {/if}

        {#if website}
          <div class="border-t border-white/15 pt-5">
            <dt
              class="mb-2 text-xs font-bold uppercase tracking-[0.22em] text-emerald-100/80"
            >
              Strona internetowa
            </dt>
            <dd>
              <a
                href={website}
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-bold text-emerald-800 transition hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-emerald-800"
              >
                Odwiedź stronę
                <ExternalLink class="size-4" />
              </a>
            </dd>
          </div>
        {/if}
      </dl>
    </div>
  </section>
{/if}
