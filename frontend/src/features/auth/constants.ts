export const AUTH_STORAGE_KEYS = {
  token: "auth_token",
  user: "auth_user",
} as const;

export const DASHBOARD_PATHS = {
  admin: "/dashboard/admin",
  user: "/dashboard/user",
} as const;

export function getDashboardPathFromRole(roleName?: string | null): string {
  const normalized = roleName?.trim().toLowerCase();

  if (normalized === "admin") return DASHBOARD_PATHS.admin;
  return DASHBOARD_PATHS.user;
}
