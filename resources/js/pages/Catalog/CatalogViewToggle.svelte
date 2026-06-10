<script>
  let {
    activeView = $bindable('map'),
    onChange,
    class: className = '',
  } = $props();

  const options = [
    {
      value: 'map',
      label: 'Mapa',
      icon: 'M12 4.5 4 7.5v12l8-3 8 3v-12l-8-3Z M12 4.5v12',
    },
    {
      value: 'list',
      label: 'Lista',
      icon: 'M8 7h10 M8 12h10 M8 17h10 M4 7h.01 M4 12h.01 M4 17h.01',
    },
    {
      value: 'split',
      label: 'Obie',
      icon: 'M12 5v14 M5 6h14 M5 18h14',
    },
  ];

  function selectView(view) {
    if (view === activeView) {
      return;
    }

    onChange?.(view);
    activeView = view;
  }
</script>

<div
  class={className +
    ' inline-grid w-full grid-cols-3 rounded-full border border-stone-200 bg-stone-200/80 p-1 shadow-[inset_0_1px_0_rgba(255,255,255,0.65)]'}
>
  {#each options as option (option.value)}
    <button
      type="button"
      class={(activeView === option.value
        ? 'bg-white text-emerald-900 shadow-sm ring-1 ring-emerald-200'
        : 'text-stone-700 hover:text-stone-950') +
        ' flex min-h-11 items-center justify-center gap-2 rounded-full px-3 py-1 text-sm font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2'}
      aria-pressed={activeView === option.value}
      onclick={() => selectView(option.value)}
    >
      <svg
        class="hidden h-4 w-4 shrink-0 md:block"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.9"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
      >
        <path d={option.icon}></path>
      </svg>
      <span>{option.label}</span>
    </button>
  {/each}
</div>
