import { render, screen } from "@testing-library/vue";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import PatientListPage from "../PatientListPage.vue";
import { patientsApi } from "../../api/patientsApi";

vi.mock("../../api/patientsApi", () => ({
  patientsApi: {
    listPatients: vi.fn(),
  },
}));

const listPatientsMock = vi.mocked(patientsApi.listPatients);

describe("PatientListPage", () => {
  beforeEach(() => {
    listPatientsMock.mockResolvedValue({
      data: {
        data: [
          {
            id: 101,
            name: "Maria Dela Cruz",
            email: "maria.delacruz@example.com",
            role: { id: 2, name: "user" },
            created_at: "2026-05-12T00:00:00.000Z",
          },
          {
            id: 102,
            name: "John Reyes",
            email: "john.reyes@example.com",
            role: { id: 2, name: "user" },
            created_at: "2026-05-09T00:00:00.000Z",
          },
          {
            id: 200,
            name: "System Admin",
            email: "admin@stbc.local",
            role: { id: 1, name: "admin" },
            created_at: "2026-05-01T00:00:00.000Z",
          },
        ],
      },
    } as never);
  });

  it("loads patient users from the backend and filters the table", async () => {
    render(PatientListPage);

    expect(await screen.findByText("Maria Dela Cruz")).toBeInTheDocument();
    expect(screen.getByText("John Reyes")).toBeInTheDocument();
    expect(screen.queryByText("System Admin")).not.toBeInTheDocument();
    expect(screen.getByText("2026-05-12")).toBeInTheDocument();

    await userEvent.type(screen.getByLabelText(/^search$/i), "john");

    expect(await screen.findByText("John Reyes")).toBeInTheDocument();
    expect(screen.queryByText("Maria Dela Cruz")).not.toBeInTheDocument();
  });
});
