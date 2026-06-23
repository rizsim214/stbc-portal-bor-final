<script setup lang="ts">
const props = defineProps<{
  currentPage: number;
  lastPage: number;
}>();

const emit = defineEmits<{
  "update:page": [page: number];
}>();
</script>

<template>
  <div v-if="props.lastPage > 1" class="mt-4 flex items-center justify-between gap-3">
    <button
      type="button"
      class="rounded-md border border-brand-light/40 px-3 py-2 text-sm text-brand-darker transition hover:bg-brand-lighter/20 disabled:cursor-not-allowed disabled:opacity-50"
      :disabled="props.currentPage <= 1"
      @click="emit('update:page', props.currentPage - 1)">
      Previous
    </button>

    <p class="text-sm text-brand-dark/80">
      Page {{ props.currentPage }} of {{ props.lastPage }}
    </p>

    <button
      type="button"
      class="rounded-md border border-brand-light/40 px-3 py-2 text-sm text-brand-darker transition hover:bg-brand-lighter/20 disabled:cursor-not-allowed disabled:opacity-50"
      :disabled="props.currentPage >= props.lastPage"
      @click="emit('update:page', props.currentPage + 1)">
      Next
    </button>
  </div>
</template>
