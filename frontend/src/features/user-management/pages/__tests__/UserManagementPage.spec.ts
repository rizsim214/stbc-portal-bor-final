import { render, screen } from "@testing-library/vue";
import userEvent from "@testing-library/user-event";
import { QueryClient, VueQueryPlugin } from "@tanstack/vue-query";
import { beforeEach, describe, expect, it, vi } from "vitest";
import UserManagementPage from "../UserManagementPage.vue";
import { usersApi } from "../../api/usersApi";

vi.mock("../../api/usersApi", () => ({
  usersApi: {
    listUsers: vi.fn(),
    listRoles: vi.fn(),
    createUser: vi.fn(),
    toggleUserStatus: vi.fn(),
  },
}));

const listUsersMock = vi.mocked(usersApi.listUsers);
const listRolesMock = vi.mocked(usersApi.listRoles);
const toggleUserStatusMock = vi.mocked(usersApi.toggleUserStatus);

function renderUserManagementPage() {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        retry: false,
      },
    },
  });

  return render(UserManagementPage, {
    global: {
      plugins: [[VueQueryPlugin, { queryClient }]],
    },
  });
}

describe("UserManagementPage", () => {
  beforeEach(() => {
    listUsersMock.mockResolvedValue({
      data: {
        data: [
          {
            id: 1,
            name: "Maria Dela Cruz",
            email: "maria.delacruz@example.com",
            role: { id: 2, name: "patient" },
            created_at: "2026-05-12T00:00:00.000Z",
            account_status: "active",
          },
          {
            id: 2,
            name: "John Reyes",
            email: "john.reyes@example.com",
            role: { id: 1, name: "admin" },
            created_at: "2026-05-09T00:00:00.000Z",
            account_status: "inactive",
          },
        ],
      },
    } as never);

    listRolesMock.mockResolvedValue({
      data: {
        data: [
          { id: 1, name: "admin" },
          { id: 2, name: "patient" },
        ],
      },
    } as never);
  });

  it("loads users from the backend and filters the table", async () => {
    renderUserManagementPage();

    expect(await screen.findByText("Maria Dela Cruz")).toBeInTheDocument();
    expect(screen.getByText("John Reyes")).toBeInTheDocument();
    expect(screen.getByText("maria.delacruz@example.com")).toBeInTheDocument();
    expect(screen.getAllByText("Active").length).toBeGreaterThan(0);
    expect(screen.getAllByText("Inactive").length).toBeGreaterThan(0);

    await userEvent.type(screen.getByPlaceholderText("Search by name..."), "john");

    expect(await screen.findByText("John Reyes")).toBeInTheDocument();
    expect(screen.queryByText("Maria Dela Cruz")).not.toBeInTheDocument();

    await userEvent.clear(screen.getByPlaceholderText("Search by name..."));
    await userEvent.selectOptions(screen.getByLabelText(/^status$/i), "inactive");

    expect(await screen.findByText("John Reyes")).toBeInTheDocument();
    expect(screen.queryByText("Maria Dela Cruz")).not.toBeInTheDocument();

    await userEvent.selectOptions(screen.getByLabelText(/^status$/i), "all");
    await userEvent.selectOptions(screen.getByLabelText(/^filter field$/i), "role");
    await userEvent.type(screen.getByPlaceholderText("Search by role..."), "patient");

    expect(await screen.findByText("Maria Dela Cruz")).toBeInTheDocument();
    expect(screen.queryByText("John Reyes")).not.toBeInTheDocument();
  });

  it("deactivates a user from the table action menu", async () => {
    listUsersMock.mockResolvedValueOnce({
      data: {
        data: [
          {
            id: 1,
            name: "Maria Dela Cruz",
            email: "maria.delacruz@example.com",
            role: { id: 2, name: "patient" },
            created_at: "2026-05-12T00:00:00.000Z",
            account_status: "active",
          },
        ],
      },
    } as never);

    toggleUserStatusMock.mockResolvedValueOnce({
      data: {
        data: {
          id: 1,
          name: "Maria Dela Cruz",
          email: "maria.delacruz@example.com",
          role: { id: 2, name: "patient" },
          created_at: "2026-05-12T00:00:00.000Z",
          account_status: "inactive",
        },
      },
    } as never);

    renderUserManagementPage();

    await screen.findByText("Maria Dela Cruz");
    await userEvent.click(screen.getByRole("button", { name: /open actions menu/i }));
    await userEvent.click(screen.getByText("Deactivate"));

    expect(usersApi.toggleUserStatus).toHaveBeenCalledWith(1);
    expect((await screen.findAllByText("Inactive")).length).toBeGreaterThan(0);
  });
});
