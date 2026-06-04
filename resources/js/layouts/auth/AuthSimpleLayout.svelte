<script lang="ts">
    import type { Snippet } from 'svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import { home } from '@/routes';

    let {
        title = '',
        description = '',
        children,
    }: {
        title?: string;
        description?: string;
        children?: Snippet;
    } = $props();
</script>

<div
    class="bg-gradient-mist flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10"
>
    <div class="w-full max-w-sm">
        <div class="glass-panel flex flex-col gap-8 rounded-2xl p-8">
            <div class="flex flex-col items-center gap-4">
                <a
                    href={home.url()}
                    class="flex flex-col items-center gap-2 font-medium"
                >
                    <div
                        class="mb-1 flex h-9 w-9 items-center justify-center rounded-md"
                    >
                        <AppLogoIcon
                            class="size-9 fill-current text-(--foreground) dark:text-white"
                        />
                    </div>
                    <span class="sr-only">{title}</span>
                </a>
                <div class="space-y-2 text-center">
                    <h1 class="text-xl font-medium">{title}</h1>
                    <p class="text-center text-sm text-muted-foreground">
                        {description}
                    </p>
                </div>
            </div>
            {@render children?.()}
        </div>
    </div>
</div>
