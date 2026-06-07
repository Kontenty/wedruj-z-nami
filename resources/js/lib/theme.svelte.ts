import type { Appearance, ResolvedAppearance } from '@/types';

export type { Appearance, ResolvedAppearance };

export type ThemeState = {
  appearance: {
    value: Appearance;
  };
  resolvedAppearance: () => ResolvedAppearance;
  updateAppearance: (value: Appearance) => void;
};

const appearance = $state<{ value: Appearance }>({ value: 'light' });

const applyLightTheme = (): void => {
  if (typeof document === 'undefined') {
    return;
  }

  document.documentElement.classList.remove('dark');
  document.documentElement.style.colorScheme = 'light';
};

const getResolvedAppearance = (): ResolvedAppearance => {
  return 'light';
};

export function initializeTheme(): () => void {
  if (typeof window === 'undefined') {
    return () => {};
  }

  appearance.value = 'light';
  applyLightTheme();

  return () => {};
}

export function updateAppearance(_value: Appearance): void {
  appearance.value = 'light';
  applyLightTheme();
}

export function themeState(): ThemeState {
  return {
    appearance,
    resolvedAppearance: getResolvedAppearance,
    updateAppearance,
  };
}
