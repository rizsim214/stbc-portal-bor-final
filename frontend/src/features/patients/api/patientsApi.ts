import { http } from "@/shared/api/http";
import type { BackendPatientUser } from "../types";

interface ApiResponse<T> {
  data: T;
  message?: string;
}

export interface PatientMedicalHistoryItem {
  id: number | string;
  kind: "appointment" | "lab_result";
  date: string;
  title: string;
  summary: string;
  released_at: string | null;
  result_data: Record<string, unknown> | null;
  appointment?: {
    id: number;
    notes: string | null;
    start_time: string;
    end_time: string | null;
  } | null;
  file_path?: string | null;
}

export interface PatientMedicalHistoryResponse {
  user: BackendPatientUser;
  medical_history: PatientMedicalHistoryItem[];
}

export const patientsApi = {
  listPatients() {
    return http.get<ApiResponse<BackendPatientUser[]>>("/users", {
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },
  getPatientMedicalHistory(userId: string | number) {
    return http.get<ApiResponse<PatientMedicalHistoryResponse>>(
      `/users/${userId}/medical-history`,
      {
        headers: {
          "X-Skip-Global-Loading": "true",
        },
      },
    );
  },
};
