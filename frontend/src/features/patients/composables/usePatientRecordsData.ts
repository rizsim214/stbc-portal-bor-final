import { computed, ref } from "vue";
import { useQuery } from "@tanstack/vue-query";
import { patientsApi } from "../api/patientsApi";
import type { PatientRecordRow } from "../types";

function formatDate(value?: string | null): string {
  if (!value) return "Unknown";
  return value.slice(0, 10);
}

function getErrorMessage(error: unknown): string {
  if (error instanceof Error && error.message) {
    return error.message;
  }

  return "Failed to load patient records.";
}

export function usePatientRecordsData(userId: string | number) {
  const errorDismissed = ref(false);

  const recordsQuery = useQuery({
    queryKey: ["patients", "records", String(userId)],
    queryFn: async () => {
      const { data } = await patientsApi.getPatientMedicalHistory(userId);
      return data.data;
    },
    enabled: computed(() => Boolean(userId)),
  });

  const patient = computed(() => recordsQuery.data.value?.user ?? null);

  const records = computed<PatientRecordRow[]>(() => {
    const items = recordsQuery.data.value?.medical_history ?? [];
    return items.map((item) => ({
      id: item.id,
      appointmentId: item.appointment?.id ?? null,
      labResultId: item.kind === "lab_result" && typeof item.id === "number" ? item.id : null,
      date: formatDate(item.date),
      title: item.title,
      summary: item.summary,
      kind: item.kind,
      releasedAt: item.released_at,
      filePath: item.file_path ?? null,
    }));
  });

  const isLoading = computed(
    () => recordsQuery.isPending.value || recordsQuery.isFetching.value,
  );

  const dataError = computed(() => {
    if (!recordsQuery.isError.value || errorDismissed.value) {
      return "";
    }

    return getErrorMessage(recordsQuery.error.value);
  });

  function clearDataError(): void {
    errorDismissed.value = true;
  }

  async function loadRecords(): Promise<void> {
    errorDismissed.value = false;
    await recordsQuery.refetch();
  }

  return {
    patient,
    records,
    isLoading,
    dataError,
    clearDataError,
    loadRecords,
  };
}
