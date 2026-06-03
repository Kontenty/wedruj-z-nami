<script>
    let { openingHours, ticketPrices, website } = $props();

    let hasData = $derived(openingHours || ticketPrices || website);
</script>

{#if hasData}
    <section class="mb-8 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 font-heading text-xl font-semibold">Informacje praktyczne</h2>
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
            {#if website}
                <div>
                    <dt class="text-sm font-medium text-stone-500">Strona internetowa</dt>
                    <dd class="mt-1">
                        <a
                            href={website}
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center gap-1 text-emerald-700 hover:underline"
                        >
                            {website} ↗
                        </a>
                    </dd>
                </div>
            {/if}
        </dl>
    </section>
{/if}
