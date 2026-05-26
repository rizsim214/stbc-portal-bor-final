import { describe, it, expect } from "vitest";
import { useAuthForms } from "../useAuthForm";

describe("useAuthForms.validateRegister", () => {
  it("returns errors for invalid input", () => {
    const { registerForm, registerErrors, validateRegister } = useAuthForms();

    registerForm.name = "";
    registerForm.email = "bad-email";
    registerForm.password = "123";
    registerForm.passwordConfirm = "456";

    const ok = validateRegister();

    expect(ok).toBe(false);
    expect(registerErrors.name).toBe("Please enter your name.");
    expect(registerErrors.email).toBe("Email address is invalid.");
    expect(registerErrors.password).toBe(
      "Minimum of 8 characters is required.",
    );
    expect(registerErrors.passwordConfirm).toBe("Passwords do not match");
  });
});
