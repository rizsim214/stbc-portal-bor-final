export const AUTH_STORAGE_KEYS = {
  token: "auth_token",
  user: "auth_user",
} as const;

export const DASHBOARD_PATHS = {
  admin: "/dashboard/admin",
  staff: "/dashboard/staff",
  patient: "/dashboard/patient",
} as const;

export function getDashboardPathFromRole(roleName?: string | null): string {
  const normalized = roleName?.trim().toLowerCase();

  if (normalized === "admin") return DASHBOARD_PATHS.admin;
  if (normalized === "staff") return DASHBOARD_PATHS.staff;
  return DASHBOARD_PATHS.patient;
}
