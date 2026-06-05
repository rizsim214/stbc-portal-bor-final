import type { RouteMeta, RouteRecordRaw } from "vue-router";

export type AppRole = "admin" | "user";

export type AppRouteMeta = RouteMeta & {
  public?: boolean;
  guestOnly?: boolean;
  requiresAuth?: boolean;
  roles?: AppRole[];
  selfOnly?: boolean;
  ownerParam?: string;
};

export type AppRoute = RouteRecordRaw & { meta?: AppRouteMeta };
