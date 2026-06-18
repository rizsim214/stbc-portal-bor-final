import { describe, it, expect, vi, beforeEach } from "vitest";

type MockRole = "admin" | "user";

type MockStore = {
  isAuthenticated: boolean;
  user: { id: number; role?: { name: string } | null } | null;
  initializeAuth: () => Promise<void>;
  getDashboardPath: () => string;
};

let mockStore: MockStore;

vi.mock("@/features/auth/stores/useAuthStore", () => ({
  useAuthStore: () => mockStore,
}));

async function loadRouter() {
  const module = await import("../index");
  return module.router;
}

function setStore(role: MockRole, authenticated = true, userId = role === "admin" ? 1 : 42) {
  const dashboardPath = `/dashboard/${role}`;
  mockStore = {
    isAuthenticated: authenticated,
    user: authenticated ? { id: userId, role: { name: role } } : null,
    initializeAuth: vi.fn().mockResolvedValue(undefined),
    getDashboardPath: vi.fn(() => dashboardPath),
  };
}

describe("router guards", () => {
  beforeEach(() => {
    vi.resetModules();
  });

  it(
    "redirects unauthenticated users to login with redirect query",
    async () => {
      setStore("user", false);
      const router = await loadRouter();

      await router.push("/dashboard/users/record");

      expect(router.currentRoute.value.path).toBe("/login");
      expect(router.currentRoute.value.query.redirect).toBe(
        "/dashboard/users/record",
      );
    },
    15000,
  );

  it("allows user to access user record route", async () => {
    setStore("user", true);
    const router = await loadRouter();

    await router.push("/dashboard/users/record");

    expect(router.currentRoute.value.name).toBe("userMedicalRecord");
  });

  it("redirects a user to their own profile route when targeting another patient", async () => {
    setStore("user", true, 42);
    const router = await loadRouter();

    await router.push("/dashboard/users/101/detail");

    expect(router.currentRoute.value.name).toBe("userDetailView");
    expect(router.currentRoute.value.params.userId).toBe("42");
  });

  it("allows an admin to access another patient's record route", async () => {
    setStore("admin", true, 1);
    const router = await loadRouter();

    await router.push("/dashboard/users/101/records");

    expect(router.currentRoute.value.name).toBe("userRecordsView");
    expect(router.currentRoute.value.params.userId).toBe("101");
  });

  it("redirects user away from restricted admin route", async () => {
    setStore("user", true);
    const router = await loadRouter();

    await router.push("/dashboard/users/list");

    expect(router.currentRoute.value.path).toBe("/dashboard/user");
  });

  it("redirects authenticated user away from guest-only route", async () => {
    setStore("user", true);
    const router = await loadRouter();

    await router.push("/login");

    expect(router.currentRoute.value.path).toBe("/dashboard/user");
  });

  it("redirects /dashboard to role dashboard path when authenticated", async () => {
    setStore("admin", true);
    const router = await loadRouter();

    await router.push("/dashboard");

    expect(router.currentRoute.value.path).toBe("/dashboard/admin");
  });

  it("redirects / to role dashboard path when authenticated", async () => {
    setStore("user", true);
    const router = await loadRouter();

    await router.push("/");

    expect(router.currentRoute.value.path).toBe("/dashboard/user");
  });

  it("redirects non-admin away from admin-only route", async () => {
    setStore("user", true);
    const router = await loadRouter();

    await router.push("/dashboard/users/manage");

    expect(router.currentRoute.value.path).toBe("/dashboard/user");
  });
});
