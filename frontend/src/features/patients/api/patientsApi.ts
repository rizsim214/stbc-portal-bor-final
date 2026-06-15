import { http } from "@/shared/api/http";
import type { BackendPatientUser } from "../types";

interface ApiResponse<T> {
  data: T;
  message?: string;
}

export const patientsApi = {
  listPatients() {
    return http.get<ApiResponse<BackendPatientUser[]>>("/users", {
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },
};
