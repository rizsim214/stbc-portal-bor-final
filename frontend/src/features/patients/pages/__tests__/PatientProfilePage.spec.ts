import { render, screen } from "@testing-library/vue";
import { ref } from "vue";
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
      patient: ref({
        id: 101,
        name: "Maria Dela Cruz",
        email: "maria.delacruz@example.com",
        account_status: "active",
      }),
      records: ref([
        {
          id: 1,
          date: "2026-05-12",
          title: "Lab Exam",
          summary: "Routine follow-up lab result.",
          kind: "lab_result",
          releasedAt: "2026-05-13T00:00:00.000Z",
        },
      ]),
      isLoading: ref(false),
      dataError: ref(""),
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

    expect(screen.getByText("Maria Dela Cruz")).toBeInTheDocument();
    expect(screen.getByText("maria.delacruz@example.com")).toBeInTheDocument();
    expect(screen.getByText("active")).toBeInTheDocument();
    expect(screen.getByText("2026-05-12 - Lab Exam")).toBeInTheDocument();
    expect(screen.queryByText(/edit/i)).not.toBeInTheDocument();
  });
});
