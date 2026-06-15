<script setup lang="ts">
import { LoaderCircle } from "lucide-vue-next";

const props = withDefaults(
  defineProps<{
    shownCount: number;
    totalCount: number;
    label: string;
    isLoading?: boolean;
  }>(),
  {
    isLoading: false,
  },
);
</script>

<template>
  <p class="mb-3 text-xs text-brand-dark/70" aria-live="polite">
    <span v-if="props.isLoading" class="inline-flex items-center gap-2 justify-center">
      <LoaderCircle class="h-3.5 w-3.5 animate-spin text-brand-dark/70" />
      <span class="sr-only">Loading</span>
    </span>
    <span v-else>Showing {{ props.shownCount }} of {{ props.totalCount }} {{ props.label }}</span>
  </p>
</template>
