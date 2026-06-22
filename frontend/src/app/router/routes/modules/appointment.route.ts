import type { AppRoute } from "../../types";

export const appointmentRoutes: AppRoute[] = [
  {
    path: "appointments/myappointmentlist",
    name: "MyAppointmentList",
    component: () =>
      import("@/features/appointment/pages/MyAppointmentList.vue"),
    meta: { roles: ["patient"] },
  },
  {
    path: "appointments/list",
    name: "AdminAppointmentList",
    component: () =>
      import("@/features/appointment/pages/MyAppointmentList.vue"),
    meta: { roles: ["patient"] },
  },
  {
    path: "appointments/:appointmentId/details",
    name: "MyAppointmentView",
    component: () =>
      import("@/features/appointment/pages/MyAppointmentView.vue"),
    meta: { roles: ["patient"] },
  },
];
