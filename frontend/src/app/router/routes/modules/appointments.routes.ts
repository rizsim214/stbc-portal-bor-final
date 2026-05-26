import type { AppRoute } from "../../types";

export const appointmentRoutes: AppRoute[] = [
  {
    path: "appointments/calendar",
    name: "appointmentCalendar",
    component: () =>
      import("@/features/appointments/pages/CalendarAvailabilityPage.vue"),
  },
  {
    path: "appointments/requests",
    name: "appointmentRequests",
    component: () =>
      import("@/features/appointments/pages/AppointmentRequestsPage.vue"),
    meta: { roles: ["staff", "admin"] },
  },
  {
    path: "appointments/history",
    name: "appointmentHistory",
    component: () =>
      import("@/features/appointments/pages/AppointmentHistoryPage.vue"),
  },
];
