<script setup lang="ts">
import { cn } from "@/shared/lib/utils";

defineProps<{
  class?: string
}>()
</script>

<template>
  <input :class="cn(
    'flex h-10 w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black',
    $props.class
  )" />
</template>
