import type { RouteMeta, RouteRecordRaw } from "vue-router";

export type AppRole = "admin" | "patient" | "staff";

export type AppRouteMeta = RouteMeta & {
  public?: boolean;
  guestOnly?: boolean;
  requiresAuth?: boolean;
  roles?: AppRole[];
  selfOnly?: boolean;
  ownerParam?: string;
};

export type AppRoute = RouteRecordRaw & { meta?: AppRouteMeta };
