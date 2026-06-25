export const AUTH_STORAGE_KEYS = {
  token: "auth_token",
  user: "auth_user",
} as const;

export type NormalizedAuthRole = "admin" | "patient" | "staff";

export const DASHBOARD_PATHS = {
  admin: "/dashboard/admin",
  patient: "/dashboard/patient",
  staff: "/dashboard/staff",
} as const;

export function normalizeAuthRole(
  roleName?: string | null,
): NormalizedAuthRole {
  const normalized = roleName?.trim().toLowerCase();

  if (normalized === "admin") return "admin";
  if (normalized === "staff") return "staff";
  return "patient";
}

export function getDashboardPathFromRole(roleName?: string | null): string {
  return DASHBOARD_PATHS[normalizeAuthRole(roleName)];
}
