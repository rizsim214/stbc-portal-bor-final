import { reactive } from "vue";
import type {
  ForgotErrors,
  ForgotForm,
  LoginErrors,
  LoginForm,
  RegisterErrors,
  RegisterForm,
} from "../types";

const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export function useAuthForms() {
  const loginForm = reactive<LoginForm>({
    email: "",
    password: "",
  });

  const registerForm = reactive<RegisterForm>({
    name: "",
    email: "",
    password: "",
    passwordConfirm: "",
  });

  const forgotForm = reactive<ForgotForm>({
    email: "",
  });

  const loginErrors = reactive<LoginErrors>({});
  const registerErrors = reactive<RegisterErrors>({});
  const forgotErrors = reactive<ForgotErrors>({});

  const clearErrors = (errors: Record<string, string | undefined>) => {
    Object.keys(errors).forEach((k) => {
      errors[k] = "";
    });
  };

  const validateLogin = () => {
    clearErrors(loginErrors);
    if (!emailRegex.test(loginForm.email))
      loginErrors.email = "Email address is invalid.";
    if (!loginForm.password) loginErrors.password = "Password is required";
    return !loginErrors.email && !loginErrors.password;
  };

  const validateRegister = (): boolean => {
    clearErrors(registerErrors);
    if (!registerForm.name.trim())
      registerErrors.name = "Please enter your name.";
    if (!emailRegex.test(registerForm.email))
      registerErrors.email = "Email address is invalid.";
    if (registerForm.password.length < 8)
      registerErrors.password = "Minimum of 8 characters is required.";
    if (registerForm.passwordConfirm !== registerForm.password) {
      registerErrors.passwordConfirm = "Passwords do not match";
    }
    return (
      !registerErrors.name &&
      !registerErrors.email &&
      !registerErrors.password &&
      !registerErrors.passwordConfirm
    );
  };

  const validateForgot = (): boolean => {
    clearErrors(forgotErrors);

    const email = forgotForm.email.trim();

    if (!email.trim()) {
      forgotErrors.email = "Email address must not be empty.";
      return false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailRegex.test(email)) {
      forgotErrors.email = "Email address is invalid.";
      return false;
    }

    return true;
  };

  return {
    loginForm,
    registerForm,
    forgotForm,
    loginErrors,
    registerErrors,
    forgotErrors,
    validateLogin,
    validateRegister,
    validateForgot,
  };
}
