import {
  FlaskConical,
  ShieldCheck,
  Stethoscope,
  UserRound,
} from "lucide-vue-next";
import { http } from "@/shared/api/http";
import type {
  AppointmentAvailabilityResponse,
  AssignableResource,
  AppointmentListItem,
  AppointmentScheduleEvent,
  AppointmentTypeOption,
  GuestAppointmentPayload,
  GuestAppointmentResponse,
  PaginatedAppointmentListResponse,
  UpdateAppointmentPayload,
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

  async listAssignableResources() {
    return http.get<{ data: AssignableResource[] }>("/appointments/resources", {
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

  async updateAppointment(
    appointmentId: string | number,
    payload: UpdateAppointmentPayload,
  ) {
    return http.patch<{ message: string; data: AppointmentListItem }>(
      `/appointments/${appointmentId}`,
      payload,
    );
  },

  async createGuestAppointment(payload: GuestAppointmentPayload) {
    return http.post<GuestAppointmentResponse>("/appointments/guest", payload);
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

export const timeOptions = [
  "07:30",
  "08:00",
  "08:30",
  "09:00",
  "09:30",
  "10:00",
  "10:30",
  "11:00",
  "11:30",
  "13:00",
  "13:30",
  "14:00",
  "14:30",
  "15:00",
  "15:30",
  "16:00",
  "16:30",
];

export const sampleSchedules: AppointmentScheduleEvent[] = [
  {
    id: "booked-1",
    title: "Booked",
    start: "2026-06-22T09:00:00",
    end: "2026-06-22T09:30:00",
    backgroundColor: "#b91c1c",
    borderColor: "#991b1b",
    extendedProps: {
      status: "booked",
      note: "This slot is already reserved.",
    },
  },
  {
    id: "booked-2",
    title: "Booked",
    start: "2026-06-24T14:00:00",
    end: "2026-06-24T14:30:00",
    backgroundColor: "#b91c1c",
    borderColor: "#991b1b",
    extendedProps: {
      status: "booked",
      note: "Reserved by another patient.",
    },
  },
  {
    id: "booked-3",
    title: "Booked",
    start: "2026-06-26T15:00:00",
    end: "2026-06-26T15:30:00",
    backgroundColor: "#b91c1c",
    borderColor: "#991b1b",
    extendedProps: {
      status: "booked",
      note: "No walk-ins for this slot.",
    },
  },
];
