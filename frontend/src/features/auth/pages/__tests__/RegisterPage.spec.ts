import { render, screen } from "@testing-library/vue";
import userEvent from "@testing-library/user-event";
import { createRouter, createMemoryHistory } from "vue-router";
import { createPinia, setActivePinia } from "pinia";
import { vi, describe, it, expect, beforeEach } from "vitest";
import type { AxiosError } from "axios";
import RegisterPage from "../RegisterPage.vue";

const push = vi.fn();

vi.mock("vue-router", async () => {
  const actual =
    await vi.importActual<typeof import("vue-router")>("vue-router");
  return { ...actual, useRouter: () => ({ push }) };
});

const registerMock = vi.fn();
vi.mock("../../api/authApi", () => ({
  authApi: { register: (...args: unknown[]) => registerMock(...args) },
}));

const PlaceholderPage = { template: "<div />" };

const renderRegisterPage = async () => {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: "/", component: PlaceholderPage },
      { path: "/register", component: RegisterPage },
      { path: "/login", component: PlaceholderPage },
    ],
  });

  await router.push("/register");
  await router.isReady();

  render(RegisterPage, { global: { plugins: [createPinia(), router] } });
};

describe("RegisterPage integration", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    push.mockReset();
    registerMock.mockReset();
  });

  it("submits valid form and redirects", async () => {
    registerMock.mockResolvedValue({
      data: {
        data: {
          token: "t1",
          user: { id: 1, email: "a@b.com", role: { name: "patient" } },
        },
      },
    });

    await renderRegisterPage();

    await userEvent.type(screen.getByLabelText(/name/i), "John Doe");
    await userEvent.type(screen.getByLabelText(/^email$/i), "john@example.com");
    await userEvent.type(screen.getByLabelText(/^password$/i), "password123");
    await userEvent.type(
      screen.getByLabelText(/confirm password/i),
      "password123",
    );
    await userEvent.click(screen.getByRole("button", { name: /register/i }));

    expect(registerMock).toHaveBeenCalled();
    expect(push).toHaveBeenCalled();
  });

  it("maps API field errors to form fields", async () => {
    const apiError = {
      isAxiosError: true,
      response: {
        data: {
          message: "Validation failed.",
          errors: {
            email: ["Email is already taken."],
            password_confirmation: ["Password confirmation does not match."],
          },
        },
      },
    } as AxiosError;
    registerMock.mockRejectedValue(apiError);

    await renderRegisterPage();

    await userEvent.type(screen.getByLabelText(/name/i), "John Doe");
    await userEvent.type(screen.getByLabelText(/^email$/i), "john@example.com");
    await userEvent.type(screen.getByLabelText(/^password$/i), "password123");
    await userEvent.type(
      screen.getByLabelText(/confirm password/i),
      "password123",
    );
    await userEvent.click(screen.getByRole("button", { name: /register/i }));

    expect(
      await screen.findByText("Email is already taken."),
    ).toBeInTheDocument();
    expect(
      await screen.findByText("Password confirmation does not match."),
    ).toBeInTheDocument();
    expect(screen.queryByText("Validation failed.")).not.toBeInTheDocument();
  });

  it("shows fallback submit error when API has no field errors", async () => {
    const apiError = {
      isAxiosError: true,
      response: {
        data: {},
      },
    } as AxiosError;
    registerMock.mockRejectedValue(apiError);

    await renderRegisterPage();

    await userEvent.type(screen.getByLabelText(/name/i), "John Doe");
    await userEvent.type(screen.getByLabelText(/^email$/i), "john@example.com");
    await userEvent.type(screen.getByLabelText(/^password$/i), "password123");
    await userEvent.type(
      screen.getByLabelText(/confirm password/i),
      "password123",
    );
    await userEvent.click(screen.getByRole("button", { name: /register/i }));

    expect(
      await screen.findByText("Registration failed. Please try again."),
    ).toBeInTheDocument();
  });
});
