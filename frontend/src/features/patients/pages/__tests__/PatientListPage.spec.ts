import { render, screen } from "@testing-library/vue";
import userEvent from "@testing-library/user-event";
import { describe, expect, it } from "vitest";
import PatientListPage from "../PatientListPage.vue";

describe("PatientListPage", () => {
  it("filters users by search term and status", async () => {
    render(PatientListPage);

    expect(screen.getByText("Maria Dela Cruz")).toBeInTheDocument();
    expect(screen.getByText("John Reyes")).toBeInTheDocument();

    await userEvent.type(screen.getByLabelText(/^search$/i), "patricia");

    expect(await screen.findByText("Patricia Gomez")).toBeInTheDocument();
    expect(screen.queryByText("Maria Dela Cruz")).not.toBeInTheDocument();
    expect(screen.queryByText("John Reyes")).not.toBeInTheDocument();

    await userEvent.selectOptions(screen.getByLabelText(/^status$/i), "Active");

    expect(await screen.findByText("No records found.")).toBeInTheDocument();
  });
});
