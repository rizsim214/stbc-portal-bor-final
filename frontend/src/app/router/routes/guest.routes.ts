import type { AppRoute } from "../types";

export const guestRoutes: AppRoute[] = [
  {
    path: "/login",
    name: "login",
    component: () => import("@/features/auth/pages/LoginPage.vue"),
    meta: { guestOnly: true },
  },
  {
    path: "/register",
    name: "register",
    component: () => import("@/features/auth/pages/RegisterPage.vue"),
    meta: { guestOnly: true },
  },
  {
    path: "/forgot-password",
    name: "forgotPassword",
    component: () => import("@/features/auth/pages/ForgotPassword.vue"),
    meta: { guestOnly: true },
  },
];
