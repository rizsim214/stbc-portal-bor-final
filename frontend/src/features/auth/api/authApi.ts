import { http } from "@/shared/api/http";
import type {
  LoginPayload,
  LoginResponse,
  RegisterForm,
  ForgotForm,
} from "../types";

interface LogoutResponse {
  message: string;
}

interface RegisterPayload {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  device_name?: string;
}

interface ForgotPayload {
  email: string;
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

  register(form: RegisterForm) {
    const payload: RegisterPayload = {
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.passwordConfirm,
      device_name: "web",
    };
    return http.post<LoginResponse>("/auth/register", payload, {
      headers: { "X-Skip-Global-Loading": "true" },
    });
  },

  forgotPassword(form: ForgotForm) {
    const payload: ForgotPayload = { email: form.email };
    return http.post("/auth/forgot-password", payload, {
      headers: { "X-Skip-Global-Loading": "true" },
    });
  },
};
