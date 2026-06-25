import { describe, it, expect, vi, beforeEach } from "vitest";

type MockRole = "admin" | "patient";

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

function setStore(
  role: MockRole,
  authenticated = true,
  userId = role === "admin" ? 1 : 42,
) {
  const dashboardPath =
    role === "admin" ? "/dashboard/admin" : "/dashboard/patient";
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
      setStore("patient", false);
      const router = await loadRouter();

      await router.push("/dashboard/users/record");

      expect(router.currentRoute.value.path).toBe("/login");
      expect(router.currentRoute.value.query.redirect).toBe(
        "/dashboard/users/record",
      );
    },
    15000,
  );

  it("allows patient to access their record route", async () => {
    setStore("patient", true);
    const router = await loadRouter();

    await router.push("/dashboard/users/record");

    expect(router.currentRoute.value.name).toBe("userMedicalRecord");
  });

  it("redirects a patient to their own profile route when targeting another patient", async () => {
    setStore("patient", true, 42);
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

  it("redirects patient away from restricted admin route", async () => {
    setStore("patient", true);
    const router = await loadRouter();

    await router.push("/dashboard/users/list");

    expect(router.currentRoute.value.path).toBe("/dashboard/patient");
  });

  it("redirects authenticated patient away from guest-only route", async () => {
    setStore("patient", true);
    const router = await loadRouter();

    await router.push("/login");

    expect(router.currentRoute.value.path).toBe("/dashboard/patient");
  });

  it("redirects /dashboard to role dashboard path when authenticated", async () => {
    setStore("admin", true);
    const router = await loadRouter();

    await router.push("/dashboard");

    expect(router.currentRoute.value.path).toBe("/dashboard/admin");
  });

  it("redirects / to role dashboard path when authenticated", async () => {
    setStore("patient", true);
    const router = await loadRouter();

    await router.push("/");

    expect(router.currentRoute.value.path).toBe("/dashboard/patient");
  });

  it("redirects non-admin away from admin-only route", async () => {
    setStore("patient", true);
    const router = await loadRouter();

    await router.push("/dashboard/users/manage");

    expect(router.currentRoute.value.path).toBe("/dashboard/patient");
  });

  it("allows a patient role to access the patient dashboard without redirect looping", async () => {
    setStore("patient", true);
    const router = await loadRouter();

    await router.push("/dashboard/patient");

    expect(router.currentRoute.value.path).toBe("/dashboard/patient");
  });
});
