import { fireEvent, render, screen } from "@testing-library/vue";
import { describe, expect, it } from "vitest";
import DashboardLayout from "../DashboardLayout.vue";

describe("DashboardLayout", () => {
  it("renders side navigation and routed page content", () => {
    render(DashboardLayout, {
      global: {
        stubs: {
          SideNavigation: { template: '<aside data-testid="side-nav">Side Nav</aside>' },
          RouterView: { template: '<section data-testid="routed-content">Page Content</section>' },
        },
      },
    });

    expect(screen.getByTestId("side-nav")).toBeInTheDocument();
    expect(screen.getByTestId("routed-content")).toBeInTheDocument();
  });

  it("keeps a main content wrapper for dashboard pages", () => {
    const { container } = render(DashboardLayout, {
      global: {
        stubs: {
          SideNavigation: { template: '<aside data-testid="side-nav">Side Nav</aside>' },
          RouterView: { template: '<section data-testid="routed-content">Page Content</section>' },
        },
      },
    });

    expect(container.querySelector("main")).toBeTruthy();
  });

  it("updates the desktop grid width when the side navigation is collapsed", async () => {
    const { container } = render(DashboardLayout, {
      global: {
        stubs: {
          SideNavigation: {
            props: ["collapsed"],
            emits: ["update:collapsed"],
            template:
              '<button aria-label="Collapse sidebar" @click="$emit(\'update:collapsed\', true)">Collapse</button>',
          },
          RouterView: { template: '<section data-testid="routed-content">Page Content</section>' },
        },
      },
    });

    expect(container.firstElementChild).toHaveStyle({
      "--dashboard-sidebar-width": "300px",
    });

    await fireEvent.click(screen.getByLabelText("Collapse sidebar"));

    expect(container.firstElementChild).toHaveStyle({
      "--dashboard-sidebar-width": "88px",
    });
  });
});
