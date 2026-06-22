import type { EventInput } from "@fullcalendar/core";
import type { CalendarDate } from "@internationalized/date";
import type { LucideIcon } from "lucide-vue-next";

export type ScheduleStatus = "available" | "booked";

export interface AppointmentTypeOption {
  value: string;
  label: string;
  icon: LucideIcon;
}

export interface AppointmentFormState {
  fullName: string;
  email: string;
  appointmentType: string;
  time: string;
}

export interface AppointmentScheduleEvent extends EventInput {
  extendedProps: {
    status: ScheduleStatus;
    note: string;
  };
}

export interface AppointmentSelectionSummary {
  label: string;
  note: string;
  date: CalendarDate | undefined;
  summaryText: string;
}
