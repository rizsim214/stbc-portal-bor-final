import type { AppRoute } from "../../types";

export const appointmentRoutes: AppRoute[] = [
  {
    path: "appointments/patient/list",
    name: "MyAppointmentList",
    component: () =>
      import("@/features/appointment/pages/private/MyAppointmentList.vue"),
    meta: { roles: ["patient"] },
  },
  {
    path: "appointments/:appointmentId/details",
    name: "MyAppointmentView",
    component: () =>
      import("@/features/appointment/pages/private/MyAppointmentView.vue"),
    meta: { roles: ["patient"] },
  },
  {
    path: "appointments/admin/:appointmentId/details",
    name: "AdminAppointmentView",
    component: () =>
      import("@/features/appointment/pages/private/AdminAppointmentView.vue"),
    meta: { roles: ["admin"] },
  },
  {
    path: "appointments/admin/list",
    name: "AdminAppointmentList",
    component: () =>
      import("@/features/appointment/pages/private/AdminAppointmentList.vue"),
    meta: { roles: ["admin"] },
  },
];
