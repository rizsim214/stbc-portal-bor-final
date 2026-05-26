import type { AppRoute } from "../../types";

export const resultRoutes: AppRoute[] = [
  {
    path: "results/my-results",
    name: "myResults",
    component: () => import("@/features/lab-results/pages/MyResultsPage.vue"),
  },
  {
    path: "results/releases",
    name: "resultReleases",
    component: () =>
      import("@/features/lab-results/pages/ResultReleasesPage.vue"),
    meta: { roles: ["staff", "admin"] },
  },
];
