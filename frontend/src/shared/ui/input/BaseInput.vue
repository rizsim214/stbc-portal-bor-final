<script setup lang="ts">
import { cn } from "@/shared/lib/utils";

interface Props {
  modelValue?: string
  type?: string
  label?: string
  placeholder?: string
  error?: string
  disabled?: boolean
  name?: string
  id?: string
  class?: string
  autocomplete?: string
}

const props = withDefaults(defineProps<Props>(), {
  type: "text",
  disabled: false,
})

const emit = defineEmits<{
  (e: "update:modelValue", value: string): void
}>()

</script>

<template>
  <div class="w-full space-y-1">
    <!-- Label -->
    <label v-if="label" :for="id" class="text-sm font-medium text-brand-darker">
      {{ label }}
    </label>

    <!-- Input -->
    <input :id="id" :name="name" :type="type" :value="modelValue" :placeholder="placeholder" :disabled="disabled"
      :autocomplete="autocomplete" @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)" :class="cn(
        'flex h-10 w-full rounded-md border border-brand-light/50 px-3 py-2 text-sm text-brand-darker transition',
        'placeholder:text-brand-dark/60 focus:outline-none focus:ring-2 focus:ring-brand-highlight/40 focus:border-brand-highlight',
        'disabled:cursor-not-allowed disabled:opacity-50',
        error && 'border-red-500 focus:ring-red-500/30 focus:border-red-500',
        $props.class
      )
        " />

    <!-- Error -->
    <p v-if="error" class="text-sm text-red-500">
      {{ error }}
    </p>

  </div>
</template>
