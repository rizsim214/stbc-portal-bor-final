import type { AppRoute } from "../../types";

export const patientRoutes: AppRoute[] = [
  {
    path: "patients/list",
    name: "patientList",
    component: () => import("@/features/patients/pages/PatientListPage.vue"),
    meta: { roles: ["staff", "admin"] },
  },
  {
    path: "patients/record",
    name: "patientMedicalRecord",
    component: () =>
      import("@/features/patients/pages/PatientMedicalRecord.vue"),
    meta: { roles: ["patient"] },
  },
];
