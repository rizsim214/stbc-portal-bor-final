import type { AppRoute } from "../types";

export const guestRoutes: AppRoute[] = [
  {
    path: "/login",
    name: "login",
    component: () => import("@/features/auth/pages/LoginPage.vue"),
    meta: { guestOnly: true },
  },
];
