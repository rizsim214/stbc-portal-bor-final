import type { AppRoute } from "../../types";

export const patientRoutes: AppRoute[] = [
  {
    path: "patients/list",
    name: "patientList",
    component: () => import("@/features/patient/pages/PatientListPage.vue"),
    meta: { roles: ["staff", "admin"] },
  },
  {
    path: "patients/records",
    name: "patientRecords",
    component: () => import("@/features/patient/pages/PatientRecordsPage.vue"),
    meta: { roles: ["staff", "admin"] },
  },
];
