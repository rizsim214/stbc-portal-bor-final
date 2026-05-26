import axios from "axios";
import { AUTH_STORAGE_KEYS } from "@/features/auth/constants";
import { startApiLoading, stopApiLoading } from "@/shared/lib/apiLoading";

export const http = axios.create({
  baseURL: "/api",
  headers: {
    Accept: "application/json",
  },
});

http.interceptors.request.use((config) => {
  startApiLoading();
  const token = localStorage.getItem(AUTH_STORAGE_KEYS.token);
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, (error) => {
  stopApiLoading();
  return Promise.reject(error);
});

http.interceptors.response.use(
  (response) => {
    stopApiLoading();
    return response;
  },
  (error) => {
    stopApiLoading();
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
