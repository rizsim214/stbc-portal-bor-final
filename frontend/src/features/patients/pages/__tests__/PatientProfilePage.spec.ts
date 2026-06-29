import { render, screen } from "@testing-library/vue";
import { computed } from "vue";
import { describe, expect, it, vi } from "vitest";
import PatientProfilePage from "../PatientProfilePage.vue";
import { usePatientRecordsData } from "../../composables/usePatientRecordsData";

vi.mock("../../composables/usePatientRecordsData", () => ({
  usePatientRecordsData: vi.fn(),
}));

const usePatientRecordsDataMock = vi.mocked(usePatientRecordsData);

describe("PatientProfilePage", () => {
  it("renders combined patient details and records without edit actions", () => {
    usePatientRecordsDataMock.mockReturnValue({
      patient: computed(() => ({
        id: 101,
        name: "Maria Dela Cruz",
        email: "maria.delacruz@example.com",
        account_status: "active",
      })),
      records: computed(() => [
        {
          id: 1,
          appointmentId: 44,
          labResultId: 1,
          date: "2026-05-12",
          title: "Lab Exam",
          summary: "Routine follow-up lab result.",
          kind: "lab_result",
          releasedAt: "2026-05-13T00:00:00.000Z",
          filePath: "lab-results/44/result.pdf",
        },
      ]),
      isLoading: computed(() => false),
      dataError: computed(() => ""),
      clearDataError: vi.fn(),
      loadRecords: vi.fn(),
    });

    render(PatientProfilePage, {
      props: {
        userId: "101",
      },
      global: {
        stubs: {
          RouterLink: {
            template: "<a><slot /></a>",
          },
          StatusBanner: {
            template: "<div><slot /></div>",
          },
        },
      },
    });

    expect(
      screen.getByRole("heading", { name: "Maria Dela Cruz" }),
    ).toBeInTheDocument();
    expect(screen.getByText("maria.delacruz@example.com")).toBeInTheDocument();
    expect(screen.getByText("Active")).toBeInTheDocument();
    expect(screen.getByText("Lab Exam")).toBeInTheDocument();
    expect(screen.getByText("Routine follow-up lab result.")).toBeInTheDocument();
    expect(screen.getByText("View Lab Result")).toBeInTheDocument();
    expect(screen.queryByText(/edit/i)).not.toBeInTheDocument();
  });
});
