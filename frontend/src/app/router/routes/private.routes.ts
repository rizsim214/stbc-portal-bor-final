import DashboardLayout from "@/app/layouts/DashboardLayout.vue";
import type { AppRoute } from "../types";
import { appointmentRoutes } from "./modules/appointments.routes";
import { patientRoutes } from "./modules/patient.routes";
import { resultRoutes } from "./modules/results.routes";
import { userRoutes } from "./modules/users.routes";

export const privateRoutes: AppRoute[] = [
  {
    path: "/dashboard",
    name: "dashboard",
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: "",
        name: "dashboardOverview",
        component: () => import("@/pages/dashboard/DashboardOverviewPage.vue"),
      },
      {
        path: "patient",
        name: "patientDashboard",
        component: () => import("@/pages/dashboard/DashboardOverviewPage.vue"),
        meta: { roles: ["patient"] },
      },
      {
        path: "staff",
        name: "staffDashboard",
        component: () => import("@/pages/dashboard/DashboardOverviewPage.vue"),
        meta: { roles: ["staff"] },
      },
      {
        path: "admin",
        name: "adminDashboard",
        component: () => import("@/pages/dashboard/DashboardOverviewPage.vue"),
        meta: { roles: ["admin"] },
      },
      ...patientRoutes,
      ...appointmentRoutes,
      ...resultRoutes,
      ...userRoutes,
    ],
  },
];
