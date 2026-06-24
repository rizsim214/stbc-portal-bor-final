import DashboardLayout from "@/app/layouts/DashboardLayout.vue";
import type { AppRoute } from "../types";
import { patientRoutes } from "./modules/patient.routes";
import { userRoutes } from "./modules/users.routes";
import { appointmentRoutes } from "./modules/appointment.route";

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
        path: "admin",
        name: "adminDashboard",
        component: () => import("@/pages/dashboard/DashboardOverviewPage.vue"),
        meta: { roles: ["admin"] },
      },
      {
        path: "staff",
        name: "staffDashboard",
        component: () => import("@/pages/dashboard/DashboardOverviewPage.vue"),
        meta: { roles: ["staff"] },
      },
      ...patientRoutes,
      ...userRoutes,
      ...appointmentRoutes,
    ],
  },
];
