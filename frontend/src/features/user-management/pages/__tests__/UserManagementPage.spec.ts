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
  },
}));

const listUsersMock = vi.mocked(usersApi.listUsers);
const listRolesMock = vi.mocked(usersApi.listRoles);

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
            role: { id: 2, name: "user" },
          },
          {
            id: 2,
            name: "John Reyes",
            email: "john.reyes@example.com",
            role: { id: 1, name: "admin" },
          },
        ],
      },
    } as never);

    listRolesMock.mockResolvedValue({
      data: {
        data: [
          { id: 1, name: "admin" },
          { id: 2, name: "user" },
        ],
      },
    } as never);
  });

  it("loads users from the backend and filters the table", async () => {
    renderUserManagementPage();

    expect(await screen.findByText("Maria Dela Cruz")).toBeInTheDocument();
    expect(screen.getByText("John Reyes")).toBeInTheDocument();
    expect(screen.getByText("maria.delacruz@example.com")).toBeInTheDocument();

    await userEvent.type(screen.getByLabelText(/^search$/i), "john");

    expect(await screen.findByText("John Reyes")).toBeInTheDocument();
    expect(screen.queryByText("Maria Dela Cruz")).not.toBeInTheDocument();

    await userEvent.clear(screen.getByLabelText(/^search$/i));
    await userEvent.selectOptions(screen.getByLabelText(/^role$/i), "user");

    expect(await screen.findByText("Maria Dela Cruz")).toBeInTheDocument();
    expect(screen.queryByText("John Reyes")).not.toBeInTheDocument();
  });
});
