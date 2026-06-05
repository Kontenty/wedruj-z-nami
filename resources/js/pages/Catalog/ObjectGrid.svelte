<script>
    import { Link } from '@inertiajs/svelte';
    import ObjectCard from './ObjectCard.svelte';

    let {
        objects,
        isLoading = false,
        selectedObjectId = null,
        onHover,
    } = $props();

    const items = $derived(objects.data ?? []);
</script>

{#if isLoading}
    <div class="grid gap-3">
        {#each Array(6) as _, index (index)}
            <div
                class="flex animate-pulse items-center gap-3 rounded-[1.5rem] border border-stone-200 bg-white p-3 sm:gap-4 sm:p-4"
            >
                <div
                    class="h-24 w-24 shrink-0 rounded-[1.125rem] bg-stone-200 sm:h-28 sm:w-28"
                ></div>
                <div class="flex-1 space-y-3">
                    <div class="h-5 w-3/4 rounded-full bg-stone-200"></div>
                    <div class="h-4 w-1/2 rounded-full bg-stone-200"></div>
                    <div class="flex items-center justify-between gap-3 pt-2">
                        <div class="h-3 w-28 rounded-full bg-stone-200"></div>
                        <div class="h-4 w-24 rounded-full bg-stone-200"></div>
                    </div>
                </div>
            </div>
        {/each}
    </div>
{:else}
    <div class="grid gap-3">
        {#each items as object (object.id)}
            <ObjectCard
                {object}
                selected={selectedObjectId === object.id}
                {onHover}
            />
        {/each}
    </div>
    {#if objects.links}
        <nav class="mt-6 flex flex-wrap justify-center gap-2">
            {#each objects.links as link, index (link.url ?? link.label ?? index)}
                {#if link.url}
                    <!-- eslint-disable-next-line svelte/no-at-html-tags -->
                    <Link
                        href={link.url}
                        preserve-scroll
                        class={'rounded-xl border px-3 py-2 text-sm ' +
                            (link.active ? 'bg-emerald-700 text-white' : '')}
                        >{@html link.label}</Link
                    >
                {:else}
                    <!-- eslint-disable-next-line svelte/no-at-html-tags -->
                    <span class="rounded-xl border px-3 py-2 text-sm opacity-40"
                        >{@html link.label}</span
                    >
                {/if}
            {/each}
        </nav>
    {/if}
{/if}
