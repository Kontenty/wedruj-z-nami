<script module lang="ts">
  import { dashboard } from '@/routes';

  export const layout = {
    breadcrumbs: [
      {
        title: 'Dashboard',
        href: dashboard(),
      },
    ],
  };
</script>

<script lang="ts">
  import AppHead from '@/components/AppHead.svelte';
  import PlaceholderPattern from '@/components/PlaceholderPattern.svelte';
</script>

<AppHead title="Dashboard" />

<div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
  <div class="grid auto-rows-min gap-4 md:grid-cols-3">
    <div
      class="glass-panel glow-level-1 relative aspect-video overflow-hidden rounded-xl"
    >
      <PlaceholderPattern />
    </div>
    <div
      class="glass-panel glow-level-1 relative aspect-video overflow-hidden rounded-xl"
    >
      <PlaceholderPattern />
    </div>
    <div
      class="glass-panel glow-level-1 relative aspect-video overflow-hidden rounded-xl"
    >
      <PlaceholderPattern />
    </div>
  </div>
  <div
    class="glass-panel glow-level-1 relative min-h-screen flex-1 rounded-xl md:min-h-min"
  >
    <PlaceholderPattern />
  </div>
</div>
