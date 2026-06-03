<script>
    import { Link } from '@inertiajs/svelte';
    import ObjectCard from './ObjectCard.svelte';

    let { objects, isLoading = false, selectedObjectId = null, onHover } = $props();

    const items = $derived(objects.data ?? []);
</script>

{#if isLoading}
    <div class="grid gap-4">
        {#each Array(6) as _, index (index)}
            <div class="h-72 animate-pulse rounded-3xl bg-stone-200"></div>
        {/each}
    </div>
{:else}
    <div class="grid gap-4">
        {#each items as object (object.id)}
            <ObjectCard {object} selected={selectedObjectId === object.id} {onHover} />
        {/each}
    </div>
    {#if objects.links}
        <nav class="mt-6 flex flex-wrap justify-center gap-2">
            {#each objects.links as link, index (link.url ?? link.label ?? index)}
                {#if link.url}
                    <!-- eslint-disable-next-line svelte/no-at-html-tags -->
                    <Link href={link.url} preserve-scroll class={'rounded-xl border px-3 py-2 text-sm ' + (link.active ? 'bg-emerald-700 text-white' : '')}>{@html link.label}</Link>
                {:else}
                    <!-- eslint-disable-next-line svelte/no-at-html-tags -->
                    <span class="rounded-xl border px-3 py-2 text-sm opacity-40">{@html link.label}</span>
                {/if}
            {/each}
        </nav>
    {/if}
{/if}
