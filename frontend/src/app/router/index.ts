import { createRouter, createWebHistory } from "vue-router";
import HomePage from "@/pages/HomePage.vue";
import AppointmentPage from "@/pages/AppointmentPage.vue";
import ServicesPage from "@/pages/ServicesPage.vue";
import AboutPage from "@/pages/AboutPage.vue";
import LoginPage from "@/features/auth/pages/LoginPage.vue";
import RegisterPage from "@/features/auth/pages/RegisterPage.vue";
import ForgotPassword from "@/features/auth/pages/ForgotPassword.vue";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import DashboardLayout from "@/app/layouts/DashboardLayout.vue";
import DashboardOverviewPage from "@/pages/dashboard/DashboardOverviewPage.vue";
import PatientListPage from "@/pages/dashboard/PatientListPage.vue";
import PatientRecordsPage from "@/pages/dashboard/PatientRecordsPage.vue";
import CalendarAvailabilityPage from "@/pages/dashboard/CalendarAvailabilityPage.vue";
import AppointmentRequestsPage from "@/pages/dashboard/AppointmentRequestsPage.vue";
import AppointmentHistoryPage from "@/pages/dashboard/AppointmentHistoryPage.vue";
import MyResultsPage from "@/pages/dashboard/MyResultsPage.vue";
import ResultReleasesPage from "@/pages/dashboard/ResultReleasesPage.vue";
import UserManagementPage from "@/pages/dashboard/UserManagementPage.vue";

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
    name: "dashboard",
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: "",
        name: "dashboardOverview",
        component: DashboardOverviewPage,
      },
      {
        path: "patient",
        name: "patientDashboard",
        component: DashboardOverviewPage,
        meta: { role: "patient" },
      },
      {
        path: "staff",
        name: "staffDashboard",
        component: DashboardOverviewPage,
        meta: { role: "staff" },
      },
      {
        path: "admin",
        name: "adminDashboard",
        component: DashboardOverviewPage,
        meta: { role: "admin" },
      },
      {
        path: "patients/list",
        name: "patientList",
        component: PatientListPage,
        meta: { roles: ["staff", "admin"] },
      },
      {
        path: "patients/records",
        name: "patientRecords",
        component: PatientRecordsPage,
        meta: { roles: ["staff", "admin"] },
      },
      {
        path: "appointments/calendar",
        name: "appointmentCalendar",
        component: CalendarAvailabilityPage,
      },
      {
        path: "appointments/requests",
        name: "appointmentRequests",
        component: AppointmentRequestsPage,
        meta: { roles: ["staff", "admin"] },
      },
      {
        path: "appointments/history",
        name: "appointmentHistory",
        component: AppointmentHistoryPage,
      },
      {
        path: "results/my-results",
        name: "myResults",
        component: MyResultsPage,
      },
      {
        path: "results/releases",
        name: "resultReleases",
        component: ResultReleasesPage,
        meta: { roles: ["staff", "admin"] },
      },
      {
        path: "users/manage",
        name: "userManagement",
        component: UserManagementPage,
        meta: { role: "admin" },
      },
    ],
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

  if (isAuthed && (typeof to.meta.role === "string" || Array.isArray(to.meta.roles))) {
    const currentRole = authStore.user?.role?.name?.toLowerCase() ?? "patient";

    if (typeof to.meta.role === "string" && to.meta.role !== currentRole) {
      return { path: authStore.getDashboardPath() };
    }

    if (Array.isArray(to.meta.roles) && !to.meta.roles.includes(currentRole)) {
      return { path: authStore.getDashboardPath() };
    }
  }

  if (to.meta.guestOnly && isAuthed) {
    return { path: authStore.getDashboardPath() };
  }

  return true;
});
