import { http } from "@/shared/api/http";
import type {
  LoginPayload,
  LoginResponse,
} from "../types";

interface LogoutResponse {
  message: string;
}

export const authApi = {
  login(payload: LoginPayload) {
    return http.post<LoginResponse>("/auth/login", payload, {
      headers: { "X-Skip-Global-Loading": "true" },
    });
  },

  logout() {
    return http.post<LogoutResponse>("/auth/logout", undefined, {
      headers: { "X-Skip-Global-Loading": "true" },
    });
  },
};
