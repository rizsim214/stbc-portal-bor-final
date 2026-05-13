import { createRouter, createWebHistory } from "vue-router";
import HomePage from "@/pages/HomePage.vue";
import AppointmentPage from "@/pages/AppointmentPage.vue";
import ServicesPage from "@/pages/ServicesPage.vue";
import AboutPage from "@/pages/AboutPage.vue";
import LoginPage from "@/features/auth/pages/LoginPage.vue";
import RegisterPage from "@/features/auth/pages/RegisterPage.vue";
import ForgotPassword from "@/features/auth/pages/ForgotPassword.vue";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import { DASHBOARD_PATHS } from "@/features/auth/constants";
import RoleDashboardPage from "@/pages/RoleDashboardPage.vue";

const routes = [
  {
    path: "/",
    name: "home",
    component: HomePage,
  },
  {
    path: "/about",
    name: "about",
    component: AboutPage,
  },
  {
    path: "/services",
    name: "services",
    component: ServicesPage,
  },
  {
    path: "/appointments",
    name: "appointments",
    component: AppointmentPage,
  },
  {
    path: "/login",
    name: "login",
    component: LoginPage,
    meta: { guestOnly: true },
  },
  {
    path: "/register",
    name: "register",
    component: RegisterPage,
    meta: { guestOnly: true },
  },
  {
    path: "/forgot-password",
    name: "forgotPassword",
    component: ForgotPassword,
    meta: { guestOnly: true },
  },
  {
    path: "/dashboard",
    redirect: DASHBOARD_PATHS.patient,
    meta: { requiresAuth: true },
  },
  {
    path: DASHBOARD_PATHS.admin,
    name: "adminDashboard",
    component: RoleDashboardPage,
    meta: { requiresAuth: true, role: "admin" },
  },
  {
    path: DASHBOARD_PATHS.staff,
    name: "staffDashboard",
    component: RoleDashboardPage,
    meta: { requiresAuth: true, role: "staff" },
  },
  {
    path: DASHBOARD_PATHS.patient,
    name: "patientDashboard",
    component: RoleDashboardPage,
    meta: { requiresAuth: true, role: "patient" },
  },
];

export const router = createRouter({
  history: createWebHistory(),
  routes,
});

let authInitialized = false;

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

  if (to.path === "/dashboard" && isAuthed) {
    return { path: authStore.getDashboardPath() };
  }

  if (isAuthed && typeof to.meta.role === "string") {
    const expectedPath = authStore.getDashboardPath();
    if (to.path !== expectedPath) {
      return { path: expectedPath };
    }
  }

  if (to.meta.guestOnly && isAuthed) {
    return { path: authStore.getDashboardPath() };
  }

  return true;
});
