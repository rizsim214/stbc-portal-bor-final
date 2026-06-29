import type { EventInput } from "@fullcalendar/core";
import type { CalendarDate } from "@internationalized/date";
import type { LucideIcon } from "lucide-vue-next";

export type ScheduleStatus = "available" | "booked";

export interface AppointmentTypeOption {
  id: number;
  value: string;
  label: string;
  description?: string;
  icon?: LucideIcon;
}

export interface AppointmentFormState {
  fullName: string;
  email: string;
  appointmentType: string;
  time: string;
  notes: string;
}

export interface AppointmentEditFormState {
  appointmentType: string;
  time: string;
  notes: string;
}

export interface AppointmentScheduleEvent extends EventInput {
  extendedProps: {
    status: string;
    note: string;
    appointmentId?: number;
    appointmentTypeId?: number;
  };
}

export interface AppointmentResourceSummary {
  id: number;
  name: string;
  type: string;
}

export interface AppointmentPatientSummary {
  id: number;
  name: string;
  email: string;
  created_at?: string;
}

export interface AppointmentTypeSummary {
  id: number;
  name: string;
  description?: string | null;
}

export interface AppointmentLabResultSummary {
  id: number;
  appointment_id: number;
  file_path: string | null;
  released_at: string | null;
}

export interface AppointmentListItem {
  id: number;
  user_id: number;
  appointment_type_id: number;
  start_time: string;
  end_time: string;
  status: string;
  notes: string | null;
  created_at?: string;
  updated_at?: string;
  allowed_next_statuses?: string[];
  user?: AppointmentPatientSummary | null;
  type?: AppointmentTypeSummary | null;
  resources?: AppointmentResourceSummary[];
  lab_result?: AppointmentLabResultSummary | null;
}

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface PaginatedAppointmentListResponse {
  data: AppointmentListItem[];
  meta: PaginationMeta;
}

export interface AppointmentActivityResponse {
  data: AppointmentListItem[];
}

export interface AssignableResource {
  id: number;
  name: string;
  type: string;
  is_available: boolean;
}

export interface AppointmentAvailabilitySlot {
  start_time: string;
  end_time: string;
}

export interface AppointmentAvailabilityResponse {
  date: string;
  appointment_type_id: number | null;
  slots: AppointmentAvailabilitySlot[];
}

export interface AppointmentCalendarItem {
  id: number;
  appointment_type_id: number;
  start_time: string;
  end_time: string;
  status: string;
  type: AppointmentTypeSummary | null;
}

export interface AppointmentCalendarResponse {
  data: AppointmentCalendarItem[];
}

export interface AppointmentSelectionSummary {
  label: string;
  note: string;
  date: CalendarDate | undefined;
  summaryText: string;
}

export type GuestAppointmentPayload = {
  name: string;
  email: string;
  appointment_type_id: number;
  start_time: string;
  end_time: string;
  notes?: string;
};

export type GuestAppointmentResponse = {
  message: string;
  data: {
    user: {
      id: number;
      name: string;
      email: string;
      role?: {
        id: number;
        name: string;
      };
    };
    appointment: {
      id: number;
      appointment_type_id: number;
      start_time: string;
      end_time: string;
      status: string;
    };
    token: string;
  };
};

export type AuthenticatedAppointmentRequestPayload = {
  appointment_type_id: number;
  start_time: string;
  end_time: string;
  notes?: string;
};

export type AuthenticatedAppointmentRequestResponse = {
  message: string;
  data: AppointmentListItem;
};

export type UpdateAppointmentPayload = {
  appointment_type_id: number;
  start_time: string;
  end_time: string;
  notes?: string;
};

export type UpdateAppointmentStatusPayload = {
  status: string;
};

export type GenerateLabResultUploadUrlPayload = {
  appointment_id: number;
  file_name: string;
  content_type: string;
  size_bytes: number;
};

export type GenerateLabResultUploadUrlResponse = {
  message: string;
  data: {
    upload_url: string;
    headers: Record<string, string>;
    file_key: string;
    expires_at: string;
  };
};

export type CreateLabResultPayload = {
  appointment_id: number;
  file_key: string;
  result_data?: Record<string, unknown>;
  released_at?: string;
};

export type CreateLabResultResponse = {
  message: string;
  data: {
    id: number;
    appointment_id: number;
    file_path: string | null;
    released_at: string | null;
  };
};

export type LabResultFileUrlResponse = {
  message: string;
  data: {
    download_url: string;
    expires_at: string;
  };
};
