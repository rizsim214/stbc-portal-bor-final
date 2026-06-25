import { render, screen } from "@testing-library/vue";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import MainNavigation from "../MainNavigation.vue";

const push = vi.fn();
const resolve = vi.fn();
const logout = vi.fn();

type MockAuthState = {
  isAuthenticated: boolean;
  user: { email?: string } | null;
  getDashboardPath: () => string;
  logout: () => Promise<void>;
};

let mockAuthState: MockAuthState;

vi.mock("vue-router", async () => {
  const actual =
    await vi.importActual<typeof import("vue-router")>("vue-router");
  return {
    ...actual,
    useRouter: () => ({
      push,
      resolve,
    }),
  };
});

vi.mock("@/features/auth/stores/useAuthStore", () => ({
  useAuthStore: () => mockAuthState,
}));

function renderMainNavigation() {
  return render(MainNavigation, {
    global: {
      stubs: {
        RouterLink: { template: "<a><slot /></a>" },
        NavigationMenuRoot: { template: "<div><slot /></div>" },
        NavigationMenuList: { template: "<ul><slot /></ul>" },
        NavigationMenuItem: { template: "<li><slot /></li>" },
        NavigationMenuLink: { template: "<span><slot /></span>" },
      },
    },
  });
}

describe("MainNavigation behavior", () => {
  beforeEach(() => {
    push.mockReset();
    resolve.mockReset();
    logout.mockReset();
    vi.stubGlobal("alert", vi.fn());
  });

  it("shows unauthenticated action and routes to login", async () => {
    mockAuthState = {
      isAuthenticated: false,
      user: null,
      getDashboardPath: () => "/dashboard/patient",
      logout,
    };
    renderMainNavigation();

    await userEvent.click(screen.getByRole("button", { name: /open navigation menu/i }));
    await userEvent.click(screen.getByRole("button", { name: /login/i }));

    expect(push).toHaveBeenCalledWith("/login");
  });

  it("shows appointment CTA and routes to the appointment page", async () => {
    mockAuthState = {
      isAuthenticated: false,
      user: null,
      getDashboardPath: () => "/dashboard/patient",
      logout,
    };
    renderMainNavigation();

    await userEvent.click(screen.getAllByRole("button", { name: /set appointment/i })[0]);

    expect(push).toHaveBeenCalledWith("/appointments");
  });

  it("logs out authenticated user and redirects to login", async () => {
    logout.mockResolvedValue(undefined);
    mockAuthState = {
      isAuthenticated: true,
      user: { email: "test@example.com" },
      getDashboardPath: () => "/dashboard/patient",
      logout,
    };
    renderMainNavigation();

    await userEvent.click(screen.getAllByRole("button", { name: /open user menu/i })[0]);
    await userEvent.click(screen.getByRole("menuitem", { name: /logout/i }));

    expect(logout).toHaveBeenCalledTimes(1);
    expect(push).toHaveBeenCalledWith("/login");
  });

  it("routes to the profile page from the user menu", async () => {
    resolve.mockReturnValue({ matched: [] });
    mockAuthState = {
      isAuthenticated: true,
      user: { email: "test@example.com" },
      getDashboardPath: () => "/dashboard/patient",
      logout,
    };
    renderMainNavigation();

    await userEvent.click(screen.getAllByRole("button", { name: /open user menu/i })[0]);
    await userEvent.click(screen.getByRole("menuitem", { name: /profile/i }));

    expect(push).toHaveBeenCalledWith({ name: "accountProfile" });
  });

  it("alerts when settings route does not exist", async () => {
    resolve.mockReturnValue({ matched: [] });
    mockAuthState = {
      isAuthenticated: true,
      user: { email: "test@example.com" },
      getDashboardPath: () => "/dashboard/patient",
      logout,
    };
    renderMainNavigation();

    await userEvent.click(screen.getAllByRole("button", { name: /open user menu/i })[0]);
    await userEvent.click(screen.getByRole("menuitem", { name: /settings/i }));

    expect(resolve).toHaveBeenCalledWith("/settings");
    expect(globalThis.alert).toHaveBeenCalledWith(
      "Settings page is not available yet.",
    );
  });
});
