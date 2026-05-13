import axios from "axios";
import { AUTH_STORAGE_KEYS } from "@/features/auth/constants";

export const http = axios.create({
  baseURL: "/api",
  headers: {
    Accept: "application/json",
  },
});

http.interceptors.request.use((config) => {
  const token = localStorage.getItem(AUTH_STORAGE_KEYS.token);
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

http.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error?.response?.status === 401) {
      localStorage.removeItem(AUTH_STORAGE_KEYS.token);
      localStorage.removeItem(AUTH_STORAGE_KEYS.user);

      if (globalThis.location.pathname !== "/login") {
        globalThis.location.href = "/login";
      }
    }

    return Promise.reject(error);
  },
);
