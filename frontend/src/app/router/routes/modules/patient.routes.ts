import type { AppRoute } from "../../types";

export const patientRoutes: AppRoute[] = [
  {
    path: "users/list",
    name: "userList",
    component: () => import("@/features/patients/pages/PatientListPage.vue"),
    meta: { roles: ["admin"] },
  },
  {
    path: "users/:userId/detail",
    name: "userDetailView",
    component: () => import("@/features/patients/pages/PatientProfilePage.vue"),
    props: true,
    meta: { roles: ["admin", "patient"], selfOnly: true, ownerParam: "userId" },
  },
  {
    path: "users/:userId/records",
    name: "userRecordsView",
    component: () => import("@/features/patients/pages/PatientProfilePage.vue"),
    props: true,
    meta: { roles: ["admin", "patient"], selfOnly: true, ownerParam: "userId" },
  },
  {
    path: "users/record",
    name: "userMedicalRecord",
    component: () =>
      import("@/features/patients/pages/PatientMedicalRecord.vue"),
    meta: { roles: ["patient"] },
  },
];
