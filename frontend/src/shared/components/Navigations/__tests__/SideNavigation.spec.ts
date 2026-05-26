import { render, screen } from "@testing-library/vue";
import { createMemoryHistory, createRouter } from "vue-router";
import { createPinia, setActivePinia } from "pinia";
import { describe, it, expect } from "vitest";
import SideNavigation from "../SideNavigation.vue";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";

const DummyPage = { template: "<div />" };

function buildRouter() {
  return createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: "/dashboard", name: "dashboard", component: DummyPage },
      {
        path: "/dashboard/patient",
        name: "patientDashboard",
        component: DummyPage,
      },
      {
        path: "/dashboard/staff",
        name: "staffDashboard",
        component: DummyPage,
      },
      {
        path: "/dashboard/admin",
        name: "adminDashboard",
        component: DummyPage,
      },
      {
        path: "/dashboard/patients/list",
        name: "patientList",
        component: DummyPage,
      },
      {
        path: "/dashboard/patients/record",
        name: "patientMedicalRecord",
        component: DummyPage,
      },
      {
        path: "/dashboard/appointments/calendar",
        name: "appointmentCalendar",
        component: DummyPage,
      },
      {
        path: "/dashboard/appointments/requests",
        name: "appointmentRequests",
        component: DummyPage,
      },
      {
        path: "/dashboard/appointments/history",
        name: "appointmentHistory",
        component: DummyPage,
      },
      {
        path: "/dashboard/results/my-results",
        name: "myResults",
        component: DummyPage,
      },
      {
        path: "/dashboard/results/releases",
        name: "resultReleases",
        component: DummyPage,
      },
      {
        path: "/dashboard/users/manage",
        name: "userManagement",
        component: DummyPage,
      },
    ],
  });
}

async function renderForRole(role: "admin" | "staff" | "patient") {
  const pinia = createPinia();
  setActivePinia(pinia);

  const store = useAuthStore();
  store.token = "token";
  store.user = {
    id: 1,
    name: "Test User",
    email: "test@example.com",
    role: { id: 1, name: role },
  };

  const router = buildRouter();
  await router.push("/dashboard");
  await router.isReady();

  render(SideNavigation, {
    global: {
      plugins: [pinia, router],
    },
  });
}

describe("SideNavigation role visibility", () => {
  it("hides Patients and Users groups for patient role", async () => {
    await renderForRole("patient");

    expect(screen.getByText("Dashboard")).toBeInTheDocument();
    expect(screen.getByAltText("STBC Clinic Logo")).toBeInTheDocument();
    expect(screen.queryByText("Patients")).not.toBeInTheDocument();
    expect(screen.queryByText("Users")).not.toBeInTheDocument();
    expect(screen.getByText("Appointments")).toBeInTheDocument();
    expect(screen.getByText("Lab Results")).toBeInTheDocument();
  });

  it("shows Users group for admin role", async () => {
    await renderForRole("admin");

    expect(screen.getByText("Dashboard")).toBeInTheDocument();
    expect(screen.getByAltText("STBC Clinic Logo")).toBeInTheDocument();
    expect(screen.getByText("Patient Related")).toBeInTheDocument();
    expect(screen.getByText("Users")).toBeInTheDocument();
  });
});
