import {
  FlaskConical,
  ShieldCheck,
  Stethoscope,
  UserRound,
} from "lucide-vue-next";
import type {
  AppointmentScheduleEvent,
  AppointmentTypeOption,
} from "../types";

export const appointmentTypes: AppointmentTypeOption[] = [
  { value: "checkup", label: "General Checkup", icon: Stethoscope },
  { value: "lab-test", label: "Lab Test", icon: FlaskConical },
  { value: "follow-up", label: "Follow-up Consultation", icon: ShieldCheck },
  { value: "clearance", label: "Medical Clearance", icon: UserRound },
];

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
