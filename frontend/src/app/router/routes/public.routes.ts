import type { AppRoute } from "../types";

export const publicRoutes: AppRoute[] = [
  {
    path: "/",
    name: "home",
    component: () => import("@/pages/HomePage.vue"),
  },
  {
    path: "/about",
    name: "about",
    component: () => import("@/pages/AboutPage.vue"),
  },
  {
    path: "/services",
    name: "services",
    component: () => import("@/pages/ServicesPage.vue"),
  },
  {
    path: "/appointments",
    name: "appointments",
    component: () => import("@/pages/AppointmentPage.vue"),
  },
];
