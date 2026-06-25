import { fireEvent, render, screen } from "@testing-library/vue";
import { createMemoryHistory, createRouter } from "vue-router";
import { createPinia, setActivePinia } from "pinia";
import { describe, it, expect } from "vitest";
import { defineComponent, ref } from "vue";
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
        path: "/dashboard/admin",
        name: "adminDashboard",
        component: DummyPage,
      },
      {
        path: "/dashboard/users/list",
        name: "userList",
        component: DummyPage,
      },
      {
        path: "/dashboard/users/record",
        name: "userMedicalRecord",
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

async function renderForRole(role: "admin" | "patient") {
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

  const SideNavigationHarness = defineComponent({
    components: { SideNavigation },
    setup() {
      const collapsed = ref(false);
      return { collapsed };
    },
    template: '<SideNavigation v-model:collapsed="collapsed" />',
  });

  render(SideNavigationHarness, {
    global: {
      plugins: [pinia, router],
    },
  });
}

describe("SideNavigation role visibility", () => {
  it("shows only dashboard and patient related group for patient role", async () => {
    await renderForRole("patient");

    expect(screen.getByText("Dashboard")).toBeInTheDocument();
    expect(screen.getByAltText("STBC Clinic Logo")).toBeInTheDocument();
    expect(screen.getByText("Records")).toBeInTheDocument();
  });

  it("shows user management group for admin role", async () => {
    await renderForRole("admin");

    expect(screen.getByText("Dashboard")).toBeInTheDocument();
    expect(screen.getByAltText("STBC Clinic Logo")).toBeInTheDocument();
    expect(screen.getByText("Records")).toBeInTheDocument();
    expect(screen.getByText("Administration")).toBeInTheDocument();
  });

  it("renders icon-only desktop content when collapsed", async () => {
    await renderForRole("admin");

    const collapseButton = screen.getByLabelText("Collapse sidebar");
    await fireEvent.click(collapseButton);

    expect(screen.getByLabelText("Expand sidebar")).toBeInTheDocument();
    expect(screen.queryByText("Navigation")).not.toBeInTheDocument();
    expect(screen.queryByText("Dashboard")).not.toBeInTheDocument();
    expect(screen.queryByText("Administration")).not.toBeInTheDocument();
    expect(screen.getByTitle("Dashboard")).toBeInTheDocument();
    expect(screen.getByTitle("User Administration")).toBeInTheDocument();
  });
});
