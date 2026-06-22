import type { CalendarOptions } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import { CalendarDate, getLocalTimeZone, today } from "@internationalized/date";
import { computed, reactive, ref } from "vue";
import {
  appointmentTypes,
  sampleSchedules,
  timeOptions,
} from "../api/appointmentsApi";
import type { AppointmentFormState, AppointmentTypeOption } from "../types";

function dateToCalendarDate(value: Date): CalendarDate {
  return new CalendarDate(
    value.getFullYear(),
    value.getMonth() + 1,
    value.getDate(),
  );
}

function padTime(value: number): string {
  return String(value).padStart(2, "0");
}

function toDateInput(value: CalendarDate): string {
  return `${value.year}-${padTime(value.month)}-${padTime(value.day)}`;
}

export function formatTimeValue(value: string): string {
  const [hoursText, minutes] = value.split(":");
  const hours = Number(hoursText);
  const suffix = hours >= 12 ? "PM" : "AM";
  const hour12 = hours % 12 || 12;
  return `${hour12}:${minutes} ${suffix}`;
}

export function formatSelectedDate(value?: CalendarDate): string {
  if (!value) return "No date selected";
  return value.toDate(getLocalTimeZone()).toLocaleDateString("en-US", {
    weekday: "short",
    month: "long",
    day: "numeric",
    year: "numeric",
  });
}

export function useAppointmentBooking() {
  const form = reactive<AppointmentFormState>({
    fullName: "",
    email: "",
    appointmentType: "",
    time: "",
  });

  const selectedDate = ref<CalendarDate | undefined>();
  const datePlaceholder = today(getLocalTimeZone());
  const submitMessage = ref("");
  const minDate = toDateInput(datePlaceholder);

  function hydrateFormFromDate(date: Date): void {
    selectedDate.value = dateToCalendarDate(date);

    const minutes = date.getMinutes();
    const normalizedMinutes = minutes === 0 || minutes === 30 ? minutes : 0;
    const normalizedTime = `${padTime(date.getHours())}:${padTime(normalizedMinutes)}`;

    if (timeOptions.includes(normalizedTime)) {
      form.time = normalizedTime;
    } else if (!form.time) {
      form.time = "08:00";
    }
  }

  const calendarOptions = computed<CalendarOptions>(() => ({
    plugins: [dayGridPlugin, timeGridPlugin],
    initialView: "timeGridWeek",
    initialDate: minDate,
    height: "auto",
    contentHeight: 760,
    allDaySlot: false,
    slotMinTime: "07:30:00",
    slotMaxTime: "17:00:00",
    slotDuration: "01:00:00",
    nowIndicator: true,
    eventDisplay: "block",
    selectable: false,
    eventInteractive: false,
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "timeGridWeek,dayGridMonth",
    },
    validRange: {
      start: minDate,
    },
    events: sampleSchedules,
  }));

  const selectedAppointmentTypeLabel = computed(
    () =>
      appointmentTypes.find(
        (option: AppointmentTypeOption) =>
          option.value === form.appointmentType,
      )?.label ?? "Choose an appointment type",
  );

  const summaryText = computed(() => {
    const type = selectedAppointmentTypeLabel.value;
    const date = formatSelectedDate(selectedDate.value);
    const time = form.time ? formatTimeValue(form.time) : "No time selected";
    return `${type} | ${date} | ${time}`;
  });

  /*
    The actual function/method to be used for submitting the appointment data into the backend
  */
  function submitAppointment(): void {
    submitMessage.value = `Sample flow: an appointment request for ${form.fullName || "this patient"} would be submitted and a patient account would be created for ${form.email || "the provided email"}.`;
  }

  function setSelectedDate(value: CalendarDate | undefined): void {
    selectedDate.value = value;

    if (!value) {
      return;
    }

    const date = value.toDate(getLocalTimeZone());
    const hours = form.time ? Number(form.time.split(":")[0]) : 8;
    const minutes = form.time ? Number(form.time.split(":")[1]) : 0;
    date.setHours(hours, minutes, 0, 0);

    hydrateFormFromDate(date);
  }

  return {
    appointmentTypes,
    calendarOptions,
    datePlaceholder,
    form,
    selectedDate,
    setSelectedDate,
    submitMessage,
    summaryText,
    timeOptions,
    formatSelectedDate,
    formatTimeValue,
    submitAppointment,
  };
}
