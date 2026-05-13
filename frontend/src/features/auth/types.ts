export interface LoginForm {
  email: string;
  password: string;
}

export interface RegisterForm {
  email: string;
  password: string;
  passwordConfirm: string;
}

export interface ForgotForm {
  email: string;
}

export type LoginErrors = Partial<Record<keyof LoginForm, string>>;
export type RegisterErrors = Partial<Record<keyof RegisterForm, string>>;
export type ForgotErrors = Partial<Record<keyof ForgotForm, string>>;
