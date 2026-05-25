import { describe, it, expect, vi, beforeEach } from "vitest";

type MockRole = "admin" | "staff" | "patient";

type MockStore = {
  isAuthenticated: boolean;
  user: { role?: { name: string } | null } | null;
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

function setStore(role: MockRole, authenticated = true) {
  const dashboardPath = `/dashboard/${role}`;
  mockStore = {
    isAuthenticated: authenticated,
    user: authenticated ? { role: { name: role } } : null,
    initializeAuth: vi.fn().mockResolvedValue(undefined),
    getDashboardPath: vi.fn(() => dashboardPath),
  };
}

describe("router guards", () => {
  beforeEach(() => {
    vi.resetModules();
  });

  it("redirects unauthenticated users to login with redirect query", async () => {
    setStore("patient", false);
    const router = await loadRouter();

    await router.push("/dashboard/appointments/history");

    expect(router.currentRoute.value.path).toBe("/login");
    expect(router.currentRoute.value.query.redirect).toBe(
      "/dashboard/appointments/history",
    );
  });

  it("allows staff to access staff/admin shared routes", async () => {
    setStore("staff", true);
    const router = await loadRouter();

    await router.push("/dashboard/patients/list");

    expect(router.currentRoute.value.name).toBe("patientList");
  });

  it("redirects patient away from restricted staff route", async () => {
    setStore("patient", true);
    const router = await loadRouter();

    await router.push("/dashboard/patients/list");

    expect(router.currentRoute.value.path).toBe("/dashboard/patient");
  });
});
