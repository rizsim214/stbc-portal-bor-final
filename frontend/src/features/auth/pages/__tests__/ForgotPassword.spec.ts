import { render, screen } from "@testing-library/vue";
import userEvent from "@testing-library/user-event";
import { vi, describe, it, expect, beforeEach } from "vitest";
import type { AxiosError } from "axios";
import ForgotPassword from "../ForgotPassword.vue";

const push = vi.fn();

vi.mock("vue-router", async () => {
  const actual =
    await vi.importActual<typeof import("vue-router")>("vue-router");

  return {
    ...actual,
    useRouter: () => ({ push }),
  };
});

const forgotPasswordMock = vi.fn();

vi.mock("../../api/authApi", () => ({
  authApi: {
    forgotPassword: (...args: unknown[]) => forgotPasswordMock(...args),
  },
}));

describe("ForgotPassword integration", () => {
  beforeEach(() => {
    push.mockReset();
    forgotPasswordMock.mockReset();
  });

  it("submits email and shows success message", async () => {
    forgotPasswordMock.mockResolvedValue({ data: {} });

    render(ForgotPassword);

    await userEvent.type(screen.getByLabelText(/retrieval email/i), "john@example.com");
    await userEvent.click(screen.getByRole("button", { name: /submit/i }));

    expect(forgotPasswordMock).toHaveBeenCalledWith({ email: "john@example.com" });
    expect(
      await screen.findByText(/password reset instructions were sent to your email/i),
    ).toBeInTheDocument();
  });

  it("navigates back to login on cancel", async () => {
    render(ForgotPassword);

    await userEvent.click(screen.getByRole("button", { name: /cancel/i }));

    expect(push).toHaveBeenCalledWith("/login");
  });

  it("maps API field errors to forgot password form", async () => {
    const apiError = {
      isAxiosError: true,
      response: {
        data: {
          message: "Validation failed.",
          errors: {
            email: ["No account found with this email."],
          },
        },
      },
    } as AxiosError;
    forgotPasswordMock.mockRejectedValue(apiError);

    render(ForgotPassword);

    await userEvent.type(screen.getByLabelText(/retrieval email/i), "john@example.com");
    await userEvent.click(screen.getByRole("button", { name: /submit/i }));

    expect(
      await screen.findByText("No account found with this email."),
    ).toBeInTheDocument();
    expect(screen.queryByText("Validation failed.")).not.toBeInTheDocument();
  });

  it("shows fallback submit error when request fails without field errors", async () => {
    const apiError = {
      isAxiosError: true,
      response: {
        data: {},
      },
    } as AxiosError;
    forgotPasswordMock.mockRejectedValue(apiError);

    render(ForgotPassword);

    await userEvent.type(screen.getByLabelText(/retrieval email/i), "john@example.com");
    await userEvent.click(screen.getByRole("button", { name: /submit/i }));

    expect(forgotPasswordMock).toHaveBeenCalledWith({ email: "john@example.com" });
    expect(
      await screen.findByText("Failed to submit forgot password request."),
    ).toBeInTheDocument();
  });
});
