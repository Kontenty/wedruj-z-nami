<script module lang="ts">
  import { index } from '@/routes/deployment';

  export const layout = {
    breadcrumbs: [
      {
        title: 'Wdrożenie',
        href: index(),
      },
    ],
  };
</script>

<script lang="ts">
  import { Form, page } from '@inertiajs/svelte';
  import DeploymentController from '@/actions/App/Http/Controllers/DeploymentController';
  import AppHead from '@/components/AppHead.svelte';
  import Heading from '@/components/Heading.svelte';
  import InputError from '@/components/InputError.svelte';
  import { Button } from '@/components/ui/button';

  interface DeploymentAction {
    title: string;
    description: string;
    destructive: boolean;
  }

  interface DeploymentResult {
    success: boolean;
    output: string;
  }

  let {
    actions,
    diagnostics,
  }: {
    actions: Record<string, DeploymentAction>;
    diagnostics: string;
  } = $props();

  const result = $derived(
    (page.props.flash as { deploymentResult?: DeploymentResult } | undefined)
      ?.deploymentResult,
  );

  function confirmDestructive(title: string): boolean {
    return confirm(
      `Czy na pewno uruchomić akcję „${title}"? Operacji nie można cofnąć z tego panelu.`,
    );
  }
</script>

<AppHead title="Wdrożenie" />

<h1 class="sr-only">Wdrożenie</h1>

<div class="flex flex-col space-y-6">
  <Heading
    variant="small"
    title="Wdrożenie"
    description="Zadania wdrożeniowe dla administratora. Akcje destrukcyjne wymagają potwierdzenia i ponownego uwierzytelnienia hasłem."
  />

  {#if result}
    <div
      class="rounded-xl border p-4 {result.success
        ? 'border-green-200 bg-green-50 text-green-900 dark:border-green-900 dark:bg-green-950 dark:text-green-100'
        : 'border-red-200 bg-red-50 text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-100'}"
    >
      <strong class="mb-2 block">
        {result.success ? 'Sukces' : 'Błąd'}
      </strong>
      <pre
        class="overflow-x-auto font-mono text-xs whitespace-pre-wrap">{result.output}</pre>
    </div>
  {/if}

  <div class="grid gap-4">
    {#each Object.entries(actions) as [action, details] (action)}
      <section
        class="glass-panel glow-level-1 rounded-xl p-4"
        aria-label={details.title}
      >
        <Form
          {...DeploymentController.store.form()}
          options={{ preserveScroll: true }}
          onBefore={() =>
            details.destructive ? confirmDestructive(details.title) : true}
        >
          {#snippet children({ errors, processing })}
            <div
              class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
              <div>
                <h2 class="font-semibold">
                  {details.title}
                  {#if details.destructive}
                    <span
                      class="ml-2 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-100"
                    >
                      ostrożnie
                    </span>
                  {/if}
                </h2>
                <p class="text-sm text-muted-foreground">
                  {details.description}
                </p>
              </div>
              <input type="hidden" name="action" value={action} />
              <Button type="submit" disabled={processing}>
                {processing ? 'Wykonywanie…' : 'Uruchom'}
              </Button>
            </div>
            <InputError class="mt-2" message={errors.action} />
          {/snippet}
        </Form>
      </section>
    {/each}
  </div>

  <details class="glass-panel glow-level-1 rounded-xl p-4">
    <summary class="cursor-pointer font-semibold">
      Diagnostyka systemu (tylko do odczytu)
    </summary>
    <pre
      class="mt-3 overflow-x-auto font-mono text-xs whitespace-pre-wrap">{diagnostics}</pre>
  </details>
</div>
