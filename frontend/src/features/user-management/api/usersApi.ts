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
    return http.get<ApiResponse<ManagedUser[]>>("/users");
  },

  listRoles() {
    return http.get<ApiResponse<ManagedRole[]>>("/roles");
  },

  createUser(payload: UserFormPayload) {
    return http.post<ApiResponse<ManagedUser>>("/users", payload);
  },
};
