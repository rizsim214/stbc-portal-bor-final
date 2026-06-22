export interface ManagedRole {
  id: number;
  name: string;
}

export interface ManagedUser {
  id: number;
  name: string;
  email: string;
  account_status?: string | null;
  created_at?: string | null;
  role?: ManagedRole | null;
}

export interface ManagedUserRow {
  id: number;
  name: string;
  email: string;
  role: string;
  status: "active" | "inactive";
}

export type UserManagementSearchField = "name" | "email" | "role";
export type UserManagementStatusFilter = "all" | "active" | "inactive";

export type UserManagementFormField =
  | "name"
  | "email"
  | "password"
  | "password_confirmation"
  | "role_id";

export type UserManagementFormErrors = Partial<Record<UserManagementFormField, string>>;

export interface UserManagementFormState {
  name: string;
  email: string;
  password: string;
  passwordConfirmation: string;
  roleId: string;
}

export interface UserFormPayload {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  role_id: number;
}
