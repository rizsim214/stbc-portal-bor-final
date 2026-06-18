import { ref } from "vue";
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

export function usePatientListData() {
  const patients = ref<PatientRow[]>([]);
  const isLoadingPatients = ref(false);
  const dataError = ref("");

  async function loadPatients(): Promise<void> {
    isLoadingPatients.value = true;
    dataError.value = "";

    try {
      const { data } = await patientsApi.listPatients();
      patients.value = unwrapUsersPayload(data)
        .filter(isPatientUser)
        .map(toPatientRow);
    } catch {
      dataError.value = "Failed to load patients.";
    } finally {
      isLoadingPatients.value = false;
    }
  }

  function clearDataError(): void {
    dataError.value = "";
  }

  return {
    patients,
    isLoadingPatients,
    dataError,
    loadPatients,
    clearDataError,
  };
}
