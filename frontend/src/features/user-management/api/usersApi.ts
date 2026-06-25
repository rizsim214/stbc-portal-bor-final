import { http } from "@/shared/api/http";
import type { StaffScheduleDay, StaffScheduleResponseData } from "@/features/staff/types";
import type {
  ManagedRole,
  ManagedUser,
  UserFormPayload,
} from "../types";

interface ApiResponse<T> {
  data: T;
  message?: string;
}

export const usersApi = {
  listUsers() {
    return http.get<ApiResponse<ManagedUser[]>>("/users", {
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },

  listRoles() {
    return http.get<ApiResponse<ManagedRole[]>>("/roles", {
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },

  createUser(payload: UserFormPayload) {
    return http.post<ApiResponse<ManagedUser>>("/users", payload);
  },

  getUserStaffSchedule(userId: string | number) {
    return http.get<ApiResponse<StaffScheduleResponseData>>(`/users/${userId}/staff-schedule`, {
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },

  updateUserStaffSchedule(userId: string | number, days: StaffScheduleDay[]) {
    return http.patch<ApiResponse<StaffScheduleResponseData>>(`/users/${userId}/staff-schedule`, {
      days,
    });
  },

  toggleUserStatus(userId: string | number) {
    return http.patch<ApiResponse<ManagedUser>>(`/users/${userId}/toggle-status`);
  },
};
