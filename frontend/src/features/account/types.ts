export interface UpdatePasswordPayload {
  old_password: string;
  new_password: string;
  new_password_confirmation: string;
}

export interface UpdatePasswordResponse {
  message: string;
}

export type PasswordFormErrors = Partial<
  Record<"old_password" | "new_password" | "new_password_confirmation", string>
>;
