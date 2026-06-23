import { computed, ref } from "vue";
import { useQuery } from "@tanstack/vue-query";
import { appointmentsApi } from "../api/appointmentsApi";
import type { AppointmentListItem, PaginationMeta } from "../types";

function getErrorMessage(error: unknown, fallback: string): string {
  if (error instanceof Error && error.message) {
    return error.message;
  }

  return fallback;
}

export function useAppointmentListData(mode: "mine" | "admin") {
  const errorDismissed = ref(false);
  const page = ref(1);
  const perPage = ref(10);

  const appointmentsQuery = useQuery({
    queryKey: ["appointments", "list", mode, page, perPage],
    queryFn: async () => {
      const { data } = mode === "mine"
        ? await appointmentsApi.listMyAppointments(page.value, perPage.value)
        : await appointmentsApi.listAllAppointments(page.value, perPage.value);

      return data;
    },
  });

  const appointments = computed<AppointmentListItem[]>(() => appointmentsQuery.data.value?.data ?? []);
  const meta = computed<PaginationMeta>(() => appointmentsQuery.data.value?.meta ?? {
    current_page: 1,
    last_page: 1,
    per_page: perPage.value,
    total: 0,
  });
  const isLoadingAppointments = computed(
    () => appointmentsQuery.isPending.value || appointmentsQuery.isFetching.value,
  );
  const dataError = computed(() => {
    if (!appointmentsQuery.isError.value || errorDismissed.value) {
      return "";
    }

    return getErrorMessage(
      appointmentsQuery.error.value,
      mode === "mine" ? "Failed to load your appointments." : "Failed to load appointments.",
    );
  });

  function clearDataError(): void {
    errorDismissed.value = true;
  }

  function setPage(nextPage: number): void {
    page.value = nextPage;
  }

  return {
    appointments,
    meta,
    page,
    perPage,
    isLoadingAppointments,
    dataError,
    clearDataError,
    setPage,
  };
}
