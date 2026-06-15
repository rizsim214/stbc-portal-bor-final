import { http } from "@/shared/api/http";
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
};
