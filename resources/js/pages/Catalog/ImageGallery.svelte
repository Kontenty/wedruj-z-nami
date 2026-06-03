<script>
    let { images, title } = $props();
    let lightboxOpen = $state(false);
    let lightboxImage = $state('');
    let lightboxIndex = $state(0);

    function openLightbox(url, index) {
        lightboxImage = url;
        lightboxIndex = index;
        lightboxOpen = true;
    }

    function closeLightbox() {
        lightboxOpen = false;
    }

    function nextImage() {
        if (images.length <= 1) return;
        lightboxIndex = (lightboxIndex + 1) % images.length;
        lightboxImage = images[lightboxIndex].url;
    }

    function prevImage() {
        if (images.length <= 1) return;
        lightboxIndex = (lightboxIndex - 1 + images.length) % images.length;
        lightboxImage = images[lightboxIndex].url;
    }

    function onKeydown(e) {
        if (!lightboxOpen) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowRight') nextImage();
        if (e.key === 'ArrowLeft') prevImage();
    }
</script>

<svelte:window onkeydown={onKeydown} />

{#if images.length > 0}
    <figure class="mb-4">
        <img
            src={images[0].url}
            alt={images[0].alt || title}
            class="h-auto w-full rounded-2xl object-cover"
        />
        {#if images[0].author || images[0].source}
            <figcaption class="mt-2 text-xs text-stone-500">
                {#if images[0].author}Foto: {images[0].author}{/if}
                {#if images[0].author && images[0].source} · {/if}
                {#if images[0].source}Źródło: {images[0].source}{/if}
            </figcaption>
        {/if}
    </figure>
{/if}

{#if images.length > 1}
    <div class="mb-8 grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4">
        {#each images as image, index}
            <button
                type="button"
                onclick={() => openLightbox(image.url, index)}
                class="group overflow-hidden rounded-xl focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
            >
                <img
                    src={image.thumbnail_url}
                    alt={image.alt || title}
                    class="h-28 w-full object-cover transition group-hover:scale-105"
                    loading="lazy"
                />
            </button>
        {/each}
    </div>
{/if}

{#if lightboxOpen}
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80"
        onclick={closeLightbox}
        onkeydown={onKeydown}
        role="dialog"
        aria-modal="true"
        aria-label="Podgląd zdjęcia"
        tabindex="-1"
    >
        {#if images.length > 1}
            <button
                onclick={(e) => { e.stopPropagation(); prevImage(); }}
                class="absolute left-4 top-1/2 -translate-y-1/2 rounded-full bg-white/20 p-3 text-white transition hover:bg-white/30"
                aria-label="Poprzednie zdjęcie"
            >
                ←
            </button>
        {/if}

        <button
            type="button"
            class="contents"
            onclick={(e) => e.stopPropagation()}
            aria-label="Aktualnie wyświetlane zdjęcie"
        >
            <img
                src={lightboxImage}
                alt=""
                class="max-h-[85vh] max-w-[90vw] rounded-lg"
            />
        </button>

        {#if images.length > 1}
            <button
                onclick={(e) => { e.stopPropagation(); nextImage(); }}
                class="absolute right-4 top-1/2 -translate-y-1/2 rounded-full bg-white/20 p-3 text-white transition hover:bg-white/30"
                aria-label="Następne zdjęcie"
            >
                →
            </button>
        {/if}

        <button
            onclick={closeLightbox}
            class="absolute right-4 top-4 rounded-full bg-white/20 p-2 text-2xl text-white transition hover:bg-white/30"
            aria-label="Zamknij"
        >
            ✕
        </button>

        {#if images.length > 1}
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 text-sm text-white/80">
                {lightboxIndex + 1} / {images.length}
            </div>
        {/if}
    </div>
{/if}
