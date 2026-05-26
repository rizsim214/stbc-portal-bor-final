import { describe, expect, it, vi } from "vitest";
import { handleAuthApiError } from "../authError";

describe("handleAuthApiError", () => {
  it("maps API field errors using fieldMap", () => {
    const setFieldError = vi.fn();
    const error = {
      isAxiosError: true,
      response: {
        data: {
          message: "Validation failed.",
          errors: {
            email: ["Invalid email."],
            password_confirmation: ["Passwords do not match."],
          },
        },
      },
    };

    const message = handleAuthApiError<"email" | "passwordConfirm">({
      error,
      fallbackMessage: "Request failed.",
      fieldMap: {
        password_confirmation: "passwordConfirm",
      },
      setFieldError,
    });

    expect(setFieldError).toHaveBeenCalledWith("email", "Invalid email.");
    expect(setFieldError).toHaveBeenCalledWith(
      "passwordConfirm",
      "Passwords do not match.",
    );
    expect(message).toBe("Validation failed.");
  });

  it("returns fallback message for non-axios errors", () => {
    const message = handleAuthApiError({
      error: new Error("boom"),
      fallbackMessage: "Fallback error",
    });

    expect(message).toBe("Fallback error");
  });

  it("returns fallback when axios payload has no message", () => {
    const error = {
      isAxiosError: true,
      response: { data: {} },
    };

    const message = handleAuthApiError({
      error,
      fallbackMessage: "Fallback error",
    });

    expect(message).toBe("Fallback error");
  });
});
