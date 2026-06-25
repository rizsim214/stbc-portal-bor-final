import type { AppRoute } from "../../types";

export const accountRoutes: AppRoute[] = [
  {
    path: "profile",
    name: "accountProfile",
    component: () =>
      import("@/features/account/pages/AccountProfilePage.vue"),
    meta: { roles: ["admin", "patient", "staff"] },
  },
];
