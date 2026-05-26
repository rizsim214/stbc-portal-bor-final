import type { RouteMeta, RouteRecordRaw } from "vue-router";

export type AppRole = "patient" | "staff" | "admin";

export type AppRouteMeta = RouteMeta & {
  public?: boolean;
  guestOnly?: boolean;
  requiresAuth?: boolean;
  roles?: AppRole[];
};

export type AppRoute = RouteRecordRaw & { meta?: AppRouteMeta };
