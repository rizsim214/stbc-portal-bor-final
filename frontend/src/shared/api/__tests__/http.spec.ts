import { beforeEach, describe, expect, it, vi } from "vitest";
import { AUTH_STORAGE_KEYS } from "@/features/auth/constants";
import { http } from "../http";

describe("http interceptors", () => {
  beforeEach(() => {
    localStorage.clear();
  });

  it("adds bearer token to request headers when token exists", () => {
    localStorage.setItem(AUTH_STORAGE_KEYS.token, "token-abc");
    const requestHandler = (http.interceptors.request as any).handlers[0]
      .fulfilled as (config: Record<string, any>) => Record<string, any>;

    const config = requestHandler({ headers: {} });

    expect(config.headers.Authorization).toBe("Bearer token-abc");
  });

  it("clears session and redirects to login on 401 response", async () => {
    localStorage.setItem(AUTH_STORAGE_KEYS.token, "token-abc");
    localStorage.setItem(AUTH_STORAGE_KEYS.user, '{"id":1}');

    const responseRejectedHandler = (http.interceptors.response as any)
      .handlers[0].rejected as (error: unknown) => Promise<never>;

    const locationMock = { pathname: "/dashboard", href: "/dashboard" };
    vi.stubGlobal("location", locationMock);

    await expect(
      responseRejectedHandler({ response: { status: 401 } }),
    ).rejects.toEqual({ response: { status: 401 } });

    expect(localStorage.getItem(AUTH_STORAGE_KEYS.token)).toBeNull();
    expect(localStorage.getItem(AUTH_STORAGE_KEYS.user)).toBeNull();
    expect(locationMock.href).toBe("/login");
  });

  it("does not redirect when already on login page", async () => {
    const responseRejectedHandler = (http.interceptors.response as any)
      .handlers[0].rejected as (error: unknown) => Promise<never>;

    const locationMock = { pathname: "/login", href: "/login" };
    vi.stubGlobal("location", locationMock);

    await expect(
      responseRejectedHandler({ response: { status: 401 } }),
    ).rejects.toEqual({ response: { status: 401 } });

    expect(locationMock.href).toBe("/login");
  });

  it("passes through non-401 errors without clearing session", async () => {
    localStorage.setItem(AUTH_STORAGE_KEYS.token, "token-abc");
    localStorage.setItem(AUTH_STORAGE_KEYS.user, '{"id":1}');
    const responseRejectedHandler = (http.interceptors.response as any)
      .handlers[0].rejected as (error: unknown) => Promise<never>;

    const locationMock = { pathname: "/dashboard", href: "/dashboard" };
    vi.stubGlobal("location", locationMock);
    const error = { response: { status: 500 }, message: "Server error" };

    await expect(responseRejectedHandler(error)).rejects.toBe(error);

    expect(localStorage.getItem(AUTH_STORAGE_KEYS.token)).toBe("token-abc");
    expect(localStorage.getItem(AUTH_STORAGE_KEYS.user)).toBe('{"id":1}');
    expect(locationMock.href).toBe("/dashboard");
  });
});
