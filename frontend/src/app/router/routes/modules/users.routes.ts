import type { AppRoute } from "../../types";

export const userRoutes: AppRoute[] = [
  {
    path: "users/manage",
    name: "userManagement",
    component: () =>
      import("@/features/user-management/pages/UserManagementPage.vue"),
    meta: { roles: ["admin"] },
  },
  {
    path: "users/:userId/staff-schedule",
    name: "userStaffSchedule",
    component: () =>
      import("@/features/staff/pages/AdminStaffSchedulePage.vue"),
    props: true,
    meta: { roles: ["admin"] },
  },
];
