import {
  FlaskConical,
  ShieldCheck,
  Stethoscope,
  UserRound,
} from "lucide-vue-next";
import { http } from "@/shared/api/http";
import type {
  AuthenticatedAppointmentRequestPayload,
  AuthenticatedAppointmentRequestResponse,
  AppointmentAvailabilityResponse,
  AppointmentActivityResponse,
  AppointmentCalendarItem,
  AppointmentCalendarResponse,
  AssignableResource,
  AppointmentListItem,
  AppointmentScheduleEvent,
  AppointmentTypeOption,
  CreateLabResultPayload,
  CreateLabResultResponse,
  GenerateLabResultUploadUrlPayload,
  GenerateLabResultUploadUrlResponse,
  GuestAppointmentPayload,
  GuestAppointmentResponse,
  LabResultDetailResponse,
  LabResultListResponse,
  LabResultFileUrlResponse,
  PaginatedAppointmentListResponse,
  UpdateAppointmentPayload,
  UpdateAppointmentStatusPayload,
} from "../types";

type AppointmentTypeRecord = {
  id: number;
  name: string;
  description: string | null;
};

function deriveAppointmentTypeIcon(name: string) {
  const normalizedName = name.toLowerCase();

  if (
    normalizedName.includes("lab") ||
    normalizedName.includes("blood") ||
    normalizedName.includes("chemistry") ||
    normalizedName.includes("hematology") ||
    normalizedName.includes("serology") ||
    normalizedName.includes("microscopy")
  ) {
    return FlaskConical;
  }

  if (
    normalizedName.includes("check") ||
    normalizedName.includes("consult") ||
    normalizedName.includes("ecg") ||
    normalizedName.includes("echo")
  ) {
    return Stethoscope;
  }

  if (
    normalizedName.includes("drug") ||
    normalizedName.includes("typing") ||
    normalizedName.includes("special")
  ) {
    return ShieldCheck;
  }

  return UserRound;
}

export const appointmentsApi = {
  async listAppointmentTypes() {
    return http.get<{ data: AppointmentTypeRecord[] }>("/appointments/types", {
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },

  async listMyAppointments(page = 1, perPage = 10) {
    return http.get<PaginatedAppointmentListResponse>("/appointments/mine", {
      params: {
        page,
        per_page: perPage,
      },
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },

  async listAllAppointments(page = 1, perPage = 10) {
    return http.get<PaginatedAppointmentListResponse>("/appointments/admin-list", {
      params: {
        page,
        per_page: perPage,
      },
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },

  async getAppointment(appointmentId: string | number) {
    return http.get<{ data: AppointmentListItem }>(`/appointments/${appointmentId}`, {
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },

  async listAssignableResources(appointmentId?: string | number) {
    return http.get<{ data: AssignableResource[] }>("/appointments/resources", {
      params: appointmentId ? { appointment_id: appointmentId } : undefined,
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },

  async assignAppointmentResource(appointmentId: string | number, resourceId: number) {
    return http.patch<{ message: string; data: AppointmentListItem }>(
      `/appointments/${appointmentId}/assignment`,
      { resource_id: resourceId },
    );
  },

  async listAppointmentAvailability(
    date: string,
    appointmentTypeId?: number,
    appointmentId?: number,
  ) {
    return http.get<AppointmentAvailabilityResponse>("/appointments/availability", {
      params: {
        date,
        appointment_type_id: appointmentTypeId,
        appointment_id: appointmentId,
      },
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },

  async listAdminActivity(limit = 8) {
    return http.get<AppointmentActivityResponse>("/appointments/admin-activity", {
      params: {
        limit,
      },
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },

  async listAppointmentCalendar(start: string, end: string) {
    return http.get<AppointmentCalendarResponse>("/appointments/calendar", {
      params: {
        start,
        end,
      },
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },

  async updateAppointment(
    appointmentId: string | number,
    payload: UpdateAppointmentPayload,
  ) {
    return http.patch<{ message: string; data: AppointmentListItem }>(
      `/appointments/${appointmentId}`,
      payload,
    );
  },

  async updateAppointmentStatus(
    appointmentId: string | number,
    payload: UpdateAppointmentStatusPayload,
  ) {
    return http.patch<{ message: string; data: AppointmentListItem }>(
      `/appointments/${appointmentId}/status`,
      payload,
    );
  },

  async createGuestAppointment(payload: GuestAppointmentPayload) {
    return http.post<GuestAppointmentResponse>("/appointments/guest", payload);
  },

  async createMyAppointmentRequest(payload: AuthenticatedAppointmentRequestPayload) {
    return http.post<AuthenticatedAppointmentRequestResponse>("/appointments/request", payload);
  },

  async generateLabResultUploadUrl(payload: GenerateLabResultUploadUrlPayload) {
    return http.post<GenerateLabResultUploadUrlResponse>("/lab-results/upload-url", payload);
  },

  async createLabResult(payload: CreateLabResultPayload) {
    return http.post<CreateLabResultResponse>("/lab-results", payload);
  },

  async listMyLabResults(page = 1, perPage = 10) {
    return http.get<LabResultListResponse>("/lab-results", {
      params: {
        page,
        per_page: perPage,
      },
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },

  async getLabResult(labResultId: string | number) {
    return http.get<LabResultDetailResponse>(`/lab-results/${labResultId}`, {
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },

  async getLabResultFileUrl(labResultId: string | number) {
    return http.get<LabResultFileUrlResponse>(`/lab-results/${labResultId}/file-url`, {
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },
};

export function mapAppointmentTypeOption(
  appointmentType: AppointmentTypeRecord,
): AppointmentTypeOption {
  return {
    id: appointmentType.id,
    value: String(appointmentType.id),
    label: appointmentType.name,
    description: appointmentType.description ?? undefined,
    icon: deriveAppointmentTypeIcon(appointmentType.name),
  };
}

export function extractTimeValue(dateTime: string): string {
  const date = new Date(dateTime.replace(" ", "T"));
  return `${String(date.getHours()).padStart(2, "0")}:${String(date.getMinutes()).padStart(2, "0")}`;
}

function mapCalendarStatusLabel(status: string): string {
  return status
    .replace(/_/g, " ")
    .replace(/\b\w/g, (character) => character.toUpperCase());
}

function mapCalendarStatusPalette(status: string): { backgroundColor: string; borderColor: string } {
  switch (status) {
    case "assigned":
      return {
        backgroundColor: "#1d4ed8",
        borderColor: "#1e40af",
      };
    case "completed":
      return {
        backgroundColor: "#047857",
        borderColor: "#065f46",
      };
    case "pending":
      return {
        backgroundColor: "#b45309",
        borderColor: "#92400e",
      };
    default:
      return {
        backgroundColor: "#7c2d12",
        borderColor: "#9a3412",
      };
  }
}

export function mapAppointmentCalendarEvent(
  appointment: AppointmentCalendarItem,
): AppointmentScheduleEvent {
  const statusLabel = mapCalendarStatusLabel(appointment.status);
  const typeLabel = appointment.type?.name ?? "Appointment";
  const palette = mapCalendarStatusPalette(appointment.status);

  return {
    id: String(appointment.id),
    title: `${typeLabel} (${statusLabel})`,
    start: appointment.start_time.replace(" ", "T"),
    end: appointment.end_time.replace(" ", "T"),
    backgroundColor: palette.backgroundColor,
    borderColor: palette.borderColor,
    extendedProps: {
      status: appointment.status,
      note: `${typeLabel} is ${statusLabel.toLowerCase()}.`,
      appointmentId: appointment.id,
      appointmentTypeId: appointment.appointment_type_id,
    },
  };
}
