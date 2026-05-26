import type { AppRoute } from "../../types";

export const userRoutes: AppRoute[] = [
  {
    path: "users/manage",
    name: "userManagement",
    component: () => import("@/features/users/pages/UserManagementPage.vue"),
    meta: { roles: ["admin"] },
  },
];
