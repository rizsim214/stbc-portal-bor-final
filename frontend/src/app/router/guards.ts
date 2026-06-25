import type { RouteLocationNormalized, Router } from "vue-router";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import { normalizeAuthRole } from "@/features/auth/constants";

let authInitialized = false;

function getCurrentRoleName(
  authStore: ReturnType<typeof useAuthStore>,
): "admin" | "patient" | "staff" {
  return normalizeAuthRole(authStore.user?.role?.name);
}

function redirectForAuthState(
  to: RouteLocationNormalized,
  isAuthed: boolean,
  authStore: ReturnType<typeof useAuthStore>,
) {
  if (to.meta.requiresAuth && !isAuthed) {
    return { path: "/login", query: { redirect: to.fullPath } };
  }

  if (to.path === "/" && isAuthed) {
    return { path: authStore.getDashboardPath() };
  }

  if (to.path === "/dashboard" && isAuthed) {
    return { path: authStore.getDashboardPath() };
  }

  if (isAuthed && Array.isArray(to.meta.roles)) {
    const currentRole = getCurrentRoleName(authStore);

    if (!to.meta.roles.includes(currentRole)) {
      return { path: authStore.getDashboardPath() };
    }
  }

  return null;
}

function redirectForSelfOnlyRoute(
  to: RouteLocationNormalized,
  isAuthed: boolean,
  authStore: ReturnType<typeof useAuthStore>,
) {
  if (!isAuthed || !to.meta.selfOnly) {
    return null;
  }

  const currentRole = getCurrentRoleName(authStore);
  const ownerParam =
    typeof to.meta.ownerParam === "string" ? to.meta.ownerParam : "userId";
  const targetUserId = (to.params as Record<string, unknown>)[ownerParam];
  const currentUserId = authStore.user?.id;

  if (
    currentRole === "patient" &&
    currentUserId != null &&
    targetUserId != null &&
    targetUserId !== String(currentUserId)
  ) {
    return {
      name: to.name ?? "userMedicalRecord",
      params: { [ownerParam]: String(currentUserId) },
    };
  }

  return null;
}

function redirectForGuestOnlyRoute(
  to: RouteLocationNormalized,
  isAuthed: boolean,
  authStore: ReturnType<typeof useAuthStore>,
) {
  if (to.meta.guestOnly && isAuthed) {
    return { path: authStore.getDashboardPath() };
  }

  return null;
}

export function installAuthGuards(router: Router) {
  router.beforeEach(async (to) => {
    const authStore = useAuthStore();

    if (!authInitialized) {
      authInitialized = true;
      await authStore.initializeAuth();
    }

    const isAuthed = authStore.isAuthenticated;

    const authRedirect = redirectForAuthState(to, isAuthed, authStore);
    if (authRedirect) {
      return authRedirect;
    }

    const selfOnlyRedirect = redirectForSelfOnlyRoute(to, isAuthed, authStore);
    if (selfOnlyRedirect) {
      return selfOnlyRedirect;
    }

    const guestOnlyRedirect = redirectForGuestOnlyRoute(
      to,
      isAuthed,
      authStore,
    );
    if (guestOnlyRedirect) {
      return guestOnlyRedirect;
    }

    return true;
  });
}
