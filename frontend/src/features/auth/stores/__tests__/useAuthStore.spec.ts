import { beforeEach, describe, expect, it, vi } from "vitest";
import { createPinia, setActivePinia } from "pinia";
import { useAuthStore } from "../useAuthStore";
import { AUTH_STORAGE_KEYS } from "../../constants";

const loginMock = vi.fn();
const logoutMock = vi.fn();

vi.mock("../../api/authApi", () => ({
  authApi: {
    login: (...args: unknown[]) => loginMock(...args),
    logout: (...args: unknown[]) => logoutMock(...args),
  },
}));

describe("useAuthStore", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    localStorage.clear();
    loginMock.mockReset();
    logoutMock.mockReset();
  });

  it("stores session on successful login", async () => {
    loginMock.mockResolvedValue({
      data: {
        data: {
          token: "token-1",
          user: { id: 1, name: "John", email: "john@example.com", role: null },
        },
      },
    });
    const store = useAuthStore();

    await store.login({ email: "john@example.com", password: "password123" });

    expect(loginMock).toHaveBeenCalledWith({
      email: "john@example.com",
      password: "password123",
      device_name: "web",
    });
    expect(store.token).toBe("token-1");
    expect(store.isLoading).toBe(false);
    expect(localStorage.getItem(AUTH_STORAGE_KEYS.token)).toBe("token-1");
  });

  it("resets loading state when login fails", async () => {
    loginMock.mockRejectedValue(new Error("Unauthorized"));
    const store = useAuthStore();

    await expect(
      store.login({ email: "john@example.com", password: "bad-pass" }),
    ).rejects.toThrow("Unauthorized");
    expect(store.isLoading).toBe(false);
  });

  it("clears invalid persisted session during initializeAuth", async () => {
    localStorage.setItem(AUTH_STORAGE_KEYS.token, "token-2");
    localStorage.setItem(AUTH_STORAGE_KEYS.user, "{bad-json");
    const store = useAuthStore();

    await store.initializeAuth();

    expect(store.token).toBe("");
    expect(store.user).toBeNull();
    expect(store.isBootstrapping).toBe(false);
    expect(localStorage.getItem(AUTH_STORAGE_KEYS.token)).toBeNull();
    expect(localStorage.getItem(AUTH_STORAGE_KEYS.user)).toBeNull();
  });

  it("clears session even when logout API fails", async () => {
    logoutMock.mockRejectedValue(new Error("Network failed"));
    const store = useAuthStore();
    store.setSession("token-3", {
      id: 1,
      name: "User",
      email: "user@example.com",
      role: null,
    });

    await expect(store.logout()).rejects.toThrow("Network failed");

    expect(store.token).toBe("");
    expect(store.user).toBeNull();
    expect(localStorage.getItem(AUTH_STORAGE_KEYS.token)).toBeNull();
  });
});
