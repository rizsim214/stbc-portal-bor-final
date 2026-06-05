<script setup lang="ts">
import { computed } from "vue";
import { useApiLoading } from "@/shared/lib/apiLoading";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";

const { isLoading: isApiLoading } = useApiLoading();
const authStore = useAuthStore();

const isLoading = computed(() => isApiLoading.value || authStore.isLoggingOut);
const loadingLabel = computed(() =>
  authStore.isLoggingOut ? "Signing out..." : "Loading...",
);
</script>

<template>
  <div v-if="isLoading" class="fixed inset-0 z-9999 flex items-center justify-center bg-white/65 backdrop-blur-sm">
    <output class="flex flex-col items-center gap-3" aria-live="polite" aria-label="Loading">
      <div class="h-11 w-11 animate-spin rounded-full border-4 border-brand-light/35 border-t-brand-dark" />
      <p class="text-sm font-medium text-brand-darker">{{ loadingLabel }}</p>
    </output>
  </div>
</template>
