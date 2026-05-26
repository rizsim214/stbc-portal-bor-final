import { computed, ref } from "vue";

const activeRequests = ref(0);

export function startApiLoading(): void {
  activeRequests.value += 1;
}

export function stopApiLoading(): void {
  activeRequests.value = Math.max(0, activeRequests.value - 1);
}

export function useApiLoading() {
  const isLoading = computed(() => activeRequests.value > 0);

  return {
    isLoading,
  };
}
