import { computed, ref } from "vue";
import { useQuery } from "@tanstack/vue-query";
import { patientsApi } from "../api/patientsApi";
import type { BackendPatientUser, PatientRow } from "../types";

function unwrapUsersPayload(payload: unknown): BackendPatientUser[] {
  if (Array.isArray(payload)) {
    return payload as BackendPatientUser[];
  }

  if (!payload || typeof payload !== "object") {
    return [];
  }

  const data = (payload as { data?: unknown }).data;
  if (Array.isArray(data)) {
    return data as BackendPatientUser[];
  }

  if (data && typeof data === "object") {
    const nestedData = (data as { data?: unknown }).data;
    if (Array.isArray(nestedData)) {
      return nestedData as BackendPatientUser[];
    }
  }

  return [];
}

function isPatientUser(user: BackendPatientUser): boolean {
  const roleName = user.role?.name?.trim().toLowerCase();
  return roleName === "user" || roleName === "patient";
}

function formatDate(value?: string | null): string {
  if (!value) {
    return "Unknown";
  }

  return value.slice(0, 10);
}

function toPatientRow(user: BackendPatientUser): PatientRow {
  return {
    id: user.id,
    name: user.name,
    email: user.email,
    role: "Patient",
    registeredAt: formatDate(user.created_at ?? user.updated_at),
  };
}

function getErrorMessage(error: unknown): string {
  if (error instanceof Error && error.message) {
    return error.message;
  }

  return "Failed to load patients.";
}

export function usePatientListData() {
  const errorDismissed = ref(false);

  const patientsQuery = useQuery({
    queryKey: ["patients", "list"],
    queryFn: async () => {
      const { data } = await patientsApi.listPatients();
      return unwrapUsersPayload(data)
        .filter(isPatientUser)
        .map(toPatientRow);
    },
  });

  const patients = computed(() => patientsQuery.data.value ?? []);
  const isLoadingPatients = computed(() => patientsQuery.isFetching.value || patientsQuery.isPending.value);
  const dataError = computed(() => {
    if (!patientsQuery.isError.value || errorDismissed.value) {
      return "";
    }

    return getErrorMessage(patientsQuery.error.value);
  });

  async function loadPatients(): Promise<void> {
    errorDismissed.value = false;
    await patientsQuery.refetch();
  }

  function clearDataError(): void {
    errorDismissed.value = true;
  }

  return {
    patients,
    isLoadingPatients,
    dataError,
    loadPatients,
    clearDataError,
  };
}
