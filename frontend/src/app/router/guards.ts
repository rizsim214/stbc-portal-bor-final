import type { Router } from "vue-router";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";

let authInitialized = false;

export function installAuthGuards(router: Router) {
  router.beforeEach(async (to) => {
    const authStore = useAuthStore();

    if (!authInitialized) {
      authInitialized = true;
      await authStore.initializeAuth();
    }

    const isAuthed = authStore.isAuthenticated;

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
      const currentRole = authStore.user?.role?.name?.toLowerCase() ?? "patient";

      if (!to.meta.roles.includes(currentRole)) {
        return { path: authStore.getDashboardPath() };
      }
    }

    if (to.meta.guestOnly && isAuthed) {
      return { path: authStore.getDashboardPath() };
    }

    return true;
  });
}
