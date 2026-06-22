<script setup lang="ts">
import { X } from "lucide-vue-next";

withDefaults(defineProps<{
  isOpen: boolean;
  title?: string;
  description?: string;
  closeLabel?: string;
  maxWidthClass?: string;
}>(), {
  title: "",
  description: "",
  closeLabel: "Close modal",
  maxWidthClass: "max-w-2xl",
});

const emit = defineEmits<{
  (e: "close"): void;
}>();
</script>

<template>
  <Transition
    appear
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/45 p-4"
      @click.self="emit('close')"
    >
      <Transition
        appear
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-2 scale-95 opacity-0"
        enter-to-class="translate-y-0 scale-100 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 scale-100 opacity-100"
        leave-to-class="translate-y-2 scale-95 opacity-0"
      >
        <div
          v-if="isOpen"
          :class="maxWidthClass"
          class="w-full rounded-xl border border-brand-light/30 bg-white p-5 shadow-2xl"
          role="dialog"
          aria-modal="true"
          :aria-labelledby="title ? 'app-modal-title' : undefined"
          :aria-describedby="description ? 'app-modal-description' : undefined"
          tabindex="-1"
        >
          <div class="mb-4 flex items-start justify-between gap-4">
            <div>
              <slot name="header">
                <h3
                  v-if="title"
                  id="app-modal-title"
                  class="text-base font-semibold text-brand-darker"
                >
                  {{ title }}
                </h3>
                <p
                  v-if="description"
                  id="app-modal-description"
                  class="text-sm text-brand-dark/80"
                >
                  {{ description }}
                </p>
              </slot>
            </div>
            <button
              type="button"
              class="rounded-md px-2 py-1 text-sm text-brand-dark/70 transition hover:bg-brand-lighter/30 hover:text-brand-darker"
              :aria-label="closeLabel"
              @click="emit('close')"
            >
              <X class="h-4 w-4" aria-hidden="true" />
            </button>
          </div>

          <slot />
        </div>
      </Transition>
    </div>
  </Transition>
</template>
