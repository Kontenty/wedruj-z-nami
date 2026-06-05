<script>
  let { openingHours, ticketPrices, accessibility, website } = $props();

  let hasData = $derived(
    openingHours || ticketPrices || accessibility || website,
  );
</script>

{#if hasData}
  <section class="glass-panel mb-8 rounded-2xl p-6 print-keep-together">
    <h2 class="mb-4 font-heading text-xl font-semibold">
      Informacje praktyczne
    </h2>
    <dl class="space-y-4">
      {#if openingHours}
        <div>
          <dt class="text-sm font-medium text-stone-500">Godziny otwarcia</dt>
          <dd class="mt-1 text-stone-700">{openingHours}</dd>
        </div>
      {/if}
      {#if ticketPrices}
        <div>
          <dt class="text-sm font-medium text-stone-500">Ceny biletów</dt>
          <dd class="mt-1 text-stone-700">{ticketPrices}</dd>
        </div>
      {/if}
      {#if accessibility}
        <div>
          <dt class="text-sm font-medium text-stone-500">Dostępność</dt>
          <dd class="mt-1 text-stone-700">{accessibility}</dd>
        </div>
      {/if}
      {#if website}
        <div>
          <dt class="text-sm font-medium text-stone-500">Strona internetowa</dt>
          <dd class="mt-1">
            <a
              href={website}
              target="_blank"
              rel="noopener"
              class="inline-flex items-center gap-1 text-emerald-700 hover:underline focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
            >
              {website} ↗
            </a>
          </dd>
        </div>
      {/if}
    </dl>
  </section>
{/if}
