<script setup lang="ts">
withDefaults(
  defineProps<{
    title: string;
    subtitle: string;
    headingTag?: "h1" | "h2" | "h3";
  }>(),
  {
    headingTag: "h2",
  },
);

defineSlots<{
  actions?: () => unknown;
}>();

const headingClasses = {
  h1: "text-2xl",
  h2: "text-lg",
  h3: "text-base",
} as const;
</script>

<template>
  <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
      <component :is="headingTag"
        :class="['font-semibold text-brand-darker', headingClasses[headingTag]]">
        {{ title }}
      </component>
      <p class="text-xs text-brand-dark/80" :class="headingTag === 'h1' ? 'mt-2 text-sm' : ''">
        {{ subtitle }}
      </p>
    </div>
    <div v-if="$slots.actions" class="shrink-0">
      <slot name="actions" />
    </div>
  </div>
</template>
