import { render, screen } from "@testing-library/vue";
import userEvent from "@testing-library/user-event";
import { vi, describe, it, expect, beforeEach } from "vitest";
import type { AxiosError } from "axios";
import LoginPage from "../LoginPage.vue";

const push = vi.fn();

vi.mock("vue-router", async () => {
  const actual =
    await vi.importActual<typeof import("vue-router")>("vue-router");

  return {
    ...actual,
    useRouter: () => ({
      push,
      currentRoute: {
        value: {
          query: {},
        },
      },
    }),
  };
});

const loginMock = vi.fn();
const getDashboardPathMock = vi.fn(() => "/dashboard/patient");

vi.mock("../../stores/useAuthStore", () => ({
  useAuthStore: () => ({
    isLoading: false,
    login: (...args: unknown[]) => loginMock(...args),
    getDashboardPath: () => getDashboardPathMock(),
  }),
}));

describe("LoginPage integration", () => {
  beforeEach(() => {
    push.mockReset();
    loginMock.mockReset();
    getDashboardPathMock.mockClear();
  });

  const renderLoginPage = () =>
    render(LoginPage, {
      global: {
        stubs: {
          RouterLink: {
            template: "<a><slot /></a>",
          },
        },
      },
    });

  it("submits valid credentials and redirects to dashboard", async () => {
    loginMock.mockResolvedValue(undefined);

    renderLoginPage();

    await userEvent.type(screen.getByLabelText(/^email$/i), "john@example.com");
    await userEvent.type(screen.getByLabelText(/^password$/i), "password123");
    await userEvent.click(screen.getByRole("button", { name: /sign in/i }));

    expect(loginMock).toHaveBeenCalledWith({
      email: "john@example.com",
      password: "password123",
    });
    expect(push).toHaveBeenCalledWith("/dashboard/patient");
  });

  it("maps API field errors to login form fields", async () => {
    const apiError = {
      isAxiosError: true,
      response: {
        data: {
          message: "Validation failed.",
          errors: {
            email: ["Email is invalid."],
            password: ["Password is incorrect."],
          },
        },
      },
    } as AxiosError;
    loginMock.mockRejectedValue(apiError);

    renderLoginPage();

    await userEvent.type(screen.getByLabelText(/^email$/i), "john@example.com");
    await userEvent.type(screen.getByLabelText(/^password$/i), "password123");
    await userEvent.click(screen.getByRole("button", { name: /sign in/i }));

    expect(await screen.findByText("Email is invalid.")).toBeInTheDocument();
    expect(await screen.findByText("Password is incorrect.")).toBeInTheDocument();
    expect(screen.queryByText("Validation failed.")).not.toBeInTheDocument();
  });

  it("shows fallback submit error when login fails without field errors", async () => {
    const apiError = {
      isAxiosError: true,
      response: {
        data: {},
      },
    } as AxiosError;
    loginMock.mockRejectedValue(apiError);

    renderLoginPage();

    await userEvent.type(screen.getByLabelText(/^email$/i), "john@example.com");
    await userEvent.type(screen.getByLabelText(/^password$/i), "password123");
    await userEvent.click(screen.getByRole("button", { name: /sign in/i }));

    expect(await screen.findByText("Login failed. Please try again.")).toBeInTheDocument();
  });
});
