import { ref } from "vue";
import { patientsApi } from "../api/patientsApi";
import type { BackendPatientUser, PatientRow } from "../types";

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
      patients.value = data.data
        .filter((user) => user.role?.name === "user")
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
