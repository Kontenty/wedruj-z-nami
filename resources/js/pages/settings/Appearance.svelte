<script module lang="ts">
  import { edit as editAppearance } from '@/routes/appearance';

  export const layout = {
    breadcrumbs: [
      {
        title: 'Appearance settings',
        href: editAppearance(),
      },
    ],
  };
</script>

<script lang="ts">
  import AppHead from '@/components/AppHead.svelte';
  import Heading from '@/components/Heading.svelte';
</script>

<AppHead title="Appearance settings" />

<h1 class="sr-only">Appearance settings</h1>

<div class="space-y-6">
  <Heading
    variant="small"
    title="Appearance settings"
    description="The app is currently fixed to light mode."
  />

  <div
    class="rounded-lg border border-border bg-muted/40 p-4 text-sm text-muted-foreground"
  >
    Dark mode is disabled for now to keep the interface consistent.
  </div>
</div>
