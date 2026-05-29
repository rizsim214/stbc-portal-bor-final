import type { AppRoute } from "../../types";

export const userRoutes: AppRoute[] = [
  {
    path: "users/manage",
    name: "userManagement",
    component: () =>
      import("@/features/user-management/pages/UserManagementPage.vue"),
    meta: { roles: ["admin"] },
  },
];
