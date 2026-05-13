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

    async fetchMe(): Promise<void> {
      const { data } = await authApi.me();
      this.user = data.data;
      localStorage.setItem(AUTH_STORAGE_KEYS.user, JSON.stringify(this.user));
    },

    async initializeAuth(): Promise<void> {
      if (!this.token) return;

      this.isBootstrapping = true;
      try {
        await this.fetchMe();
      } catch {
        this.clearSession();
      } finally {
        this.isBootstrapping = false;
      }
    },

    async logout(): Promise<void> {
      try {
        if (this.token) await authApi.logout();
      } finally {
        this.clearSession();
      }
    },
  },
});
