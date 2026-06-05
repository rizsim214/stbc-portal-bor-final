<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from "vue";

const props = withDefaults(
  defineProps<{
    message: string;
    tone?: "success" | "error";
    durationMs?: number;
  }>(),
  {
    tone: "success",
    durationMs: 5000,
  },
);

const emit = defineEmits<{
  (e: "dismiss"): void;
}>();

const isVisible = ref(true);
let dismissTimer: ReturnType<typeof setTimeout> | undefined;

function clearTimer(): void {
  if (dismissTimer) {
    clearTimeout(dismissTimer);
    dismissTimer = undefined;
  }
}

function scheduleDismiss(): void {
  clearTimer();
  isVisible.value = true;
  dismissTimer = setTimeout(() => {
    isVisible.value = false;
    emit("dismiss");
  }, props.durationMs);
}

watch(
  () => props.message,
  (message) => {
    if (message) {
      scheduleDismiss();
      return;
    }

    clearTimer();
    isVisible.value = false;
  },
  { immediate: true },
);

onBeforeUnmount(() => {
  clearTimer();
});

const toneClasses = {
  success: "border-emerald-200 bg-emerald-50 text-emerald-700",
  error: "border-red-200 bg-red-50 text-red-700",
} as const;
</script>

<template>
  <transition enter-active-class="transition duration-200 ease-out" enter-from-class="translate-y-2 opacity-0"
    enter-to-class="translate-y-0 opacity-100" leave-active-class="transition duration-200 ease-in"
    leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-2 opacity-0">
    <output v-if="isVisible && props.message"
      class="fixed bottom-4 right-4 z-50 w-[min(24rem,calc(100vw-2rem))] rounded-lg border px-4 py-3 text-sm shadow-lg"
      :class="toneClasses[props.tone]" aria-live="polite">
      {{ props.message }}
    </output>
  </transition>
</template>
