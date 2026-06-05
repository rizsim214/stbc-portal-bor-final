<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    shownCount: number;
    totalCount: number;
    label: string;
    isLoading?: boolean;
    loadingLabel?: string;
  }>(),
  {
    isLoading: false,
    loadingLabel: "Loading...",
  },
);
</script>

<template>
  <p class="mb-3 text-xs text-brand-dark/70" aria-live="polite">
    <span v-if="props.isLoading">{{ props.loadingLabel }}</span>
    <span v-else>Showing {{ props.shownCount }} of {{ props.totalCount }} {{ props.label }}</span>
  </p>
</template>
