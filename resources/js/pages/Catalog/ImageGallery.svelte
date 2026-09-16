<script>
  import ChevronLeft from '@lucide/svelte/icons/chevron-left';
  import ChevronRight from '@lucide/svelte/icons/chevron-right';
  import Images from '@lucide/svelte/icons/images';
  import X from '@lucide/svelte/icons/x';

  let { images, title } = $props();
  let lightboxOpen = $state(false);
  let lightboxIndex = $state(0);

  const previewImages = $derived(images.slice(1, 4));
  const remainingImages = $derived(Math.max(images.length - 4, 0));

  function getWebpSrc(image, variant) {
    if (variant === 'gallery') {
      return image.gallery_webp_url || null;
    }

    if (variant === 'card') {
      return (
        image.card_webp_url ||
        image.gallery_webp_url ||
        image.thumbnail_webp_url ||
        null
      );
    }

    return (
      image.thumbnail_webp_url ||
      image.card_webp_url ||
      image.gallery_webp_url ||
      null
    );
  }

  function getFallbackSrc(image, variant) {
    if (variant === 'gallery') {
      return image.gallery_url || image.url;
    }

    if (variant === 'card') {
      return (
        image.card_url || image.gallery_url || image.thumbnail_url || image.url
      );
    }

    return (
      image.thumbnail_url || image.card_url || image.gallery_url || image.url
    );
  }

  function hasWebp(image, variant) {
    return Boolean(getWebpSrc(image, variant));
  }

  function openLightbox(index) {
    lightboxIndex = index;
    lightboxOpen = true;
  }

  function closeLightbox() {
    lightboxOpen = false;
  }

  function nextImage() {
    if (images.length <= 1) {
      return;
    }

    lightboxIndex = (lightboxIndex + 1) % images.length;
  }

  function prevImage() {
    if (images.length <= 1) {
      return;
    }

    lightboxIndex = (lightboxIndex - 1 + images.length) % images.length;
  }

  function onKeydown(e) {
    if (!lightboxOpen) {
      return;
    }

    if (e.key === 'Escape') {
      closeLightbox();
    }

    if (e.key === 'ArrowRight') {
      nextImage();
    }

    if (e.key === 'ArrowLeft') {
      prevImage();
    }
  }
</script>

<svelte:window onkeydown={onKeydown} />

{#if images.length > 0}
  <section class="mb-12">
    <figure>
      <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
        <button
          type="button"
          onclick={() => openLightbox(0)}
          class="group relative block h-72 overflow-hidden rounded-[1.75rem] bg-stone-100 text-left shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2 sm:h-96 lg:col-span-3 lg:h-125"
        >
          <picture class="block w-full">
            {#if hasWebp(images[0], 'gallery')}
              <source
                srcset={getWebpSrc(images[0], 'gallery')}
                type="image/webp"
              />
            {/if}
            <img
              src={getFallbackSrc(images[0], 'gallery')}
              alt={images[0].alt || title}
              fetchpriority="high"
              decoding="async"
              class="block size-full object-cover transition duration-700 group-hover:scale-105"
            />
          </picture>
          <div
            class="pointer-events-none absolute inset-x-0 bottom-0 h-28 bg-linear-to-t from-black/65 via-black/15 to-transparent"
          ></div>
          <div
            class="absolute bottom-5 left-5 inline-flex items-center gap-2 rounded-2xl bg-black/35 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm"
          >
            <Images class="size-4" />
            {images[0].alt || title}
          </div>
        </button>

        {#if previewImages.length > 0}
          <div class="hidden gap-4 lg:flex lg:h-125 lg:flex-col">
            {#each previewImages as image, index (image.url ?? index)}
              <button
                type="button"
                onclick={() => openLightbox(index + 1)}
                class="group relative min-h-0 flex-1 overflow-hidden rounded-3xl border border-stone-200 bg-stone-100 text-left shadow-sm transition hover:border-emerald-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
              >
                <picture class="block size-full">
                  {#if hasWebp(image, 'card')}
                    <source
                      srcset={getWebpSrc(image, 'card')}
                      type="image/webp"
                    />
                  {/if}
                  <img
                    src={getFallbackSrc(image, 'card')}
                    alt={image.alt || title}
                    class="block size-full min-h-0 object-cover transition duration-500 group-hover:scale-110"
                    loading="lazy"
                    decoding="async"
                  />
                </picture>

                {#if remainingImages > 0 && index === previewImages.length - 1}
                  <div
                    class="absolute inset-0 flex items-center justify-center bg-black/55 text-white"
                  >
                    <span class="font-heading text-3xl font-bold">
                      +{remainingImages}
                    </span>
                  </div>
                {/if}
              </button>
            {/each}
          </div>
        {/if}
      </div>

      {#if images[0].description || images[0].author || images[0].source}
        <figcaption class="mt-3 text-sm text-stone-500">
          {#if images[0].description}<span>{images[0].description}</span>{/if}
          {#if images[0].description && (images[0].author || images[0].source)}<br
            />{/if}
          {#if images[0].author}Foto: {images[0].author}{/if}
          {#if images[0].author && images[0].source}
            ·
          {/if}
          {#if images[0].source}Źródło: {images[0].source}{/if}
        </figcaption>
      {/if}
    </figure>

    {#if images.length > 1}
      <div class="mt-4 grid grid-cols-2 gap-3 lg:hidden">
        {#each images.slice(1) as image, index (image.url ?? index)}
          <button
            type="button"
            onclick={() => openLightbox(index + 1)}
            class="group relative overflow-hidden rounded-2xl border border-stone-200 bg-stone-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
          >
            <picture class="block">
              {#if hasWebp(image, 'thumbnail')}
                <source
                  srcset={getWebpSrc(image, 'thumbnail')}
                  type="image/webp"
                />
              {/if}
              <img
                src={getFallbackSrc(image, 'thumbnail')}
                alt={image.alt || title}
                class="block h-28 w-full object-cover transition duration-500 group-hover:scale-105 sm:h-36"
                loading="lazy"
                decoding="async"
              />
            </picture>
          </button>
        {/each}
      </div>
    {/if}
  </section>
{/if}

{#if lightboxOpen}
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
        onclick={(e) => {
          e.stopPropagation();
          prevImage();
        }}
        class="absolute left-4 top-1/2 -translate-y-1/2 flex size-10 items-center justify-center rounded-full bg-white/20 text-white transition hover:bg-white/30"
        aria-label="Poprzednie zdjęcie"
      >
        <ChevronLeft class="size-5" />
      </button>
    {/if}

    <button
      type="button"
      class="contents"
      onclick={(e) => e.stopPropagation()}
      aria-label="Aktualnie wyświetlane zdjęcie"
    >
      <picture class="block">
        {#if hasWebp(images[lightboxIndex], 'gallery')}
          <source
            srcset={getWebpSrc(images[lightboxIndex], 'gallery')}
            type="image/webp"
          />
        {/if}
        <img
          src={getFallbackSrc(images[lightboxIndex], 'gallery')}
          alt={images[lightboxIndex].alt || title}
          decoding="async"
          class="h-auto max-h-[calc(100vh-12rem)] w-auto max-w-[90vw] rounded-lg object-contain"
        />
      </picture>
    </button>

    {#if images[lightboxIndex].description || images[lightboxIndex].author || images[lightboxIndex].source}
      <div
        class="absolute bottom-10 left-1/2 max-w-[min(90vw,48rem)] -translate-x-1/2 text-center text-sm text-white"
      >
        {#if images[lightboxIndex].description}<p class="mt-1">
            {images[lightboxIndex].description}
          </p>{/if}
        {#if images[lightboxIndex].author || images[lightboxIndex].source}
          <p class="mt-1 text-white/75">
            {#if images[lightboxIndex].author}Foto: {images[lightboxIndex]
                .author}{/if}
            {#if images[lightboxIndex].author && images[lightboxIndex].source}
              ·
            {/if}
            {#if images[lightboxIndex].source}Źródło: {images[lightboxIndex]
                .source}{/if}
          </p>
        {/if}
      </div>
    {/if}

    {#if images.length > 1}
      <button
        onclick={(e) => {
          e.stopPropagation();
          nextImage();
        }}
        class="absolute right-4 top-1/2 -translate-y-1/2 flex size-10 items-center justify-center rounded-full bg-white/20 text-white transition hover:bg-white/30"
        aria-label="Następne zdjęcie"
      >
        <ChevronRight class="size-5" />
      </button>
    {/if}

    <button
      onclick={closeLightbox}
      class="absolute right-4 top-4 flex size-10 items-center justify-center rounded-full bg-white/20 text-white transition hover:bg-white/30"
      aria-label="Zamknij"
    >
      <X class="size-5" />
    </button>

    {#if images.length > 1}
      <div
        class="absolute bottom-4 left-1/2 -translate-x-1/2 text-sm text-white/80"
      >
        {lightboxIndex + 1} / {images.length}
      </div>
    {/if}
  </div>
{/if}
