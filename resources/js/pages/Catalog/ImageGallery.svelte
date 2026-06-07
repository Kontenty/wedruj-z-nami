<script>
  import Images from 'lucide-svelte/icons/images';

  let { images, title } = $props();
  let lightboxOpen = $state(false);
  let lightboxImage = $state('');
  let lightboxIndex = $state(0);

  const previewImages = $derived(images.slice(1, 4));
  const remainingImages = $derived(Math.max(images.length - 4, 0));

  function openLightbox(url, index) {
    lightboxImage = url;
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
    lightboxImage = images[lightboxIndex].url;
  }

  function prevImage() {
    if (images.length <= 1) {
      return;
    }

    lightboxIndex = (lightboxIndex - 1 + images.length) % images.length;
    lightboxImage = images[lightboxIndex].url;
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
      <div class="grid grid-cols-1 gap-4 lg:grid-cols-4 lg:[grid-auto-rows:minmax(0,1fr)]">
        <button
          type="button"
          onclick={() => openLightbox(images[0].url, 0)}
          class="group relative overflow-hidden rounded-[1.75rem] bg-stone-100 text-left shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2 lg:col-span-3 lg:min-h-[32rem]"
        >
          <img
            src={images[0].url}
            alt={images[0].alt || title}
            class="h-80 w-full object-cover transition duration-700 group-hover:scale-105 lg:h-full"
          />
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
          <div class="hidden gap-4 lg:grid">
            {#each previewImages as image, index (image.url ?? index)}
              <button
                type="button"
                onclick={() => openLightbox(image.url, index + 1)}
                class="group relative min-h-0 overflow-hidden rounded-[1.5rem] border border-stone-200 bg-stone-100 text-left shadow-sm transition hover:border-emerald-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
              >
                <img
                  src={image.card_url || image.thumbnail_url}
                  alt={image.alt || title}
                  class="h-full min-h-40 w-full object-cover transition duration-500 group-hover:scale-110"
                  loading="lazy"
                />

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

      {#if images[0].author || images[0].source}
        <figcaption class="mt-3 text-sm text-stone-500">
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
            onclick={() => openLightbox(image.url, index + 1)}
            class="group relative overflow-hidden rounded-2xl border border-stone-200 bg-stone-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2"
          >
            <img
              src={image.thumbnail_url}
              alt={image.alt || title}
              class="h-28 w-full object-cover transition duration-500 group-hover:scale-105 sm:h-36"
              loading="lazy"
            />
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
        onclick={(e) => {
          e.stopPropagation();
          nextImage();
        }}
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
      <div
        class="absolute bottom-4 left-1/2 -translate-x-1/2 text-sm text-white/80"
      >
        {lightboxIndex + 1} / {images.length}
      </div>
    {/if}
  </div>
{/if}
