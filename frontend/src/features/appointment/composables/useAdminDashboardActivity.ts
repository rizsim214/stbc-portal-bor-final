import { computed, ref, toValue, type MaybeRefOrGetter } from "vue";
import { useQuery } from "@tanstack/vue-query";
import { appointmentsApi } from "../api/appointmentsApi";
import type { AppointmentListItem } from "../types";

function getErrorMessage(error: unknown): string {
  if (error instanceof Error && error.message) {
    return error.message;
  }

  return "Failed to load recent booking activity.";
}

export function useAdminDashboardActivity(limit = 8, enabled: MaybeRefOrGetter<boolean> = true) {
  const errorDismissed = ref(false);

  const activityQuery = useQuery({
    queryKey: ["appointments", "activity", limit],
    enabled: computed(() => toValue(enabled)),
    queryFn: async () => {
      const { data } = await appointmentsApi.listAdminActivity(limit);
      return data;
    },
  });

  const activities = computed<AppointmentListItem[]>(() => activityQuery.data.value?.data ?? []);
  const isLoading = computed(() => activityQuery.isPending.value || activityQuery.isFetching.value);
  const dataError = computed(() => {
    if (!activityQuery.isError.value || errorDismissed.value) {
      return "";
    }

    return getErrorMessage(activityQuery.error.value);
  });

  function clearDataError(): void {
    errorDismissed.value = true;
  }

  return {
    activities,
    isLoading,
    dataError,
    clearDataError,
  };
}
