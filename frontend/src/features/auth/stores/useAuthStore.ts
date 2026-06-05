import { defineStore } from "pinia";
import { authApi } from "../api/authApi";
import type { AuthUser, LoginForm } from "../types";
import {
  AUTH_STORAGE_KEYS,
  getDashboardPathFromRole,
} from "../constants";

function safeParseUser(raw: string | null): AuthUser | null {
  if (!raw) return null;
  try {
    return JSON.parse(raw) as AuthUser;
  } catch {
    return null;
  }
}

export const useAuthStore = defineStore("auth", {
  state: () => ({
    token: localStorage.getItem(AUTH_STORAGE_KEYS.token) ?? "",
    user: safeParseUser(localStorage.getItem(AUTH_STORAGE_KEYS.user)),
    isLoading: false,
    isBootstrapping: false,
    isLoggingOut: false,
  }),

  getters: {
    isAuthenticated: (state) => Boolean(state.token),
  },

  actions: {
    getDashboardPath(): string {
      return getDashboardPathFromRole(this.user?.role?.name);
    },

    setSession(token: string, user: AuthUser): void {
      this.token = token;
      this.user = user;
      localStorage.setItem(AUTH_STORAGE_KEYS.token, token);
      localStorage.setItem(AUTH_STORAGE_KEYS.user, JSON.stringify(user));
    },

    clearSession(): void {
      this.token = "";
      this.user = null;
      localStorage.removeItem(AUTH_STORAGE_KEYS.token);
      localStorage.removeItem(AUTH_STORAGE_KEYS.user);
    },

    async login(payload: LoginForm): Promise<void> {
      this.isLoading = true;
      try {
        const { data } = await authApi.login({
          ...payload,
          device_name: "web",
        });
        this.setSession(data.data.token, data.data.user);
      } finally {
        this.isLoading = false;
      }
    },

    async initializeAuth(): Promise<void> {
      this.isBootstrapping = true;
      try {
        if (!this.token) {
          return;
        }

        // Keep session on refresh using persisted user data.
        if (!this.user) {
          this.clearSession();
        }
      } finally {
        this.isBootstrapping = false;
      }
    },

    async logout(): Promise<void> {
      this.isLoggingOut = true;
      try {
        if (this.token) await authApi.logout();
      } finally {
        this.clearSession();
        this.isLoggingOut = false;
      }
    },
  },
});
