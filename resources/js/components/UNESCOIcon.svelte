<script lang="ts">
    const titleId = `unesco-icon-${Math.random().toString(36).slice(2, 9)}`;

    let {
        class: className = '',
        title,
        ...rest
    }: {
        class?: string;
        title?: string;
        [key: string]: unknown;
    } = $props();
</script>

<svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 450 450"
    class={className}
    role={title ? 'img' : undefined}
    aria-hidden={title ? undefined : 'true'}
    aria-labelledby={title ? titleId : undefined}
    focusable="false"
    {...rest}
>
    {#if title}
        <title id={titleId}>{title}</title>
    {/if}

    <path
        fill="currentColor"
        d="M257.464 411.865c1.285.003 2.578-.12 3.86-.37a190 190 0 1 0-72.649 0 19.99 19.99 0 0 0 16.543-4.195 20 20 0 0 0 7.282-15.436v-94.85L140.487 225 225 140.488 309.513 225 237.5 297.013v94.85a20 20 0 0 0 19.964 20zm15.736-39.387V311.8L360 225 225 90 90 225l86.8 86.8v60.677C113.694 351.906 69.824 292.798 69.8 225a155.2 155.2 0 1 1 310.4 0c-.024 67.798-43.894 126.906-107 147.478"
    />
</svg>
