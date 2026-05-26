export interface LoginForm {
  email: string;
  password: string;
}

export interface LoginPayload extends LoginForm {
  device_name?: string;
}

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  role?: {
    id: number;
    name: string;
  } | null;
}

export interface LoginResponse {
  message: string;
  data: {
    token: string;
    user: AuthUser;
  };
}

export interface RegisterForm {
  name: string;
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
