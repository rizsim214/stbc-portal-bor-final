import axios from "axios";
import { AUTH_STORAGE_KEYS } from "@/features/auth/constants";
import type { CalendarOptions } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import { CalendarDate, getLocalTimeZone, today } from "@internationalized/date";
import { useQuery } from "@tanstack/vue-query";
import { computed, reactive, ref } from "vue";
import {
  appointmentsApi,
  mapAppointmentTypeOption,
  sampleSchedules,
  timeOptions,
} from "../api/appointmentsApi";
import type { AppointmentFormState, AppointmentTypeOption } from "../types";
import {
  addMinutesToSqlDateTime,
  dateToCalendarDate,
  formatSelectedDate,
  formatTimeValue,
  toDateInput,
  toSqlDateTime,
} from "../utils/schedule";

function padTime(value: number): string {
  return String(value).padStart(2, "0");
}

export function useAppointmentBooking() {
  const form = reactive<AppointmentFormState>({
    fullName: "",
    email: "",
    appointmentType: "",
    time: "",
    notes: "",
  });

  const selectedDate = ref<CalendarDate | undefined>();
  const datePlaceholder = today(getLocalTimeZone());
  const submitMessage = ref("");
  const minDate = toDateInput(datePlaceholder);
  const appointmentTypesQuery = useQuery({
    queryKey: ["appointments", "types"],
    queryFn: async () => {
      const { data } = await appointmentsApi.listAppointmentTypes();
      return data.data.map(mapAppointmentTypeOption);
    },
  });

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
    contentHeight: 920,
    allDaySlot: false,
    slotMinTime: "07:30:00",
    slotMaxTime: "17:00:00",
    slotDuration: "00:30:00",
    slotLabelInterval: "01:00:00",
    nowIndicator: true,
    eventDisplay: "block",
    eventMinHeight: 32,
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
      (appointmentTypesQuery.data.value ?? []).find(
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

  async function submitAppointment(): Promise<void> {
    submitMessage.value = "";

    if (!selectedDate.value) {
      submitMessage.value = "Please select an appointment date.";
      return;
    }

    if (!form.appointmentType) {
      submitMessage.value = "Please select an appointment type.";
      return;
    }

    if (!form.time) {
      submitMessage.value = "Please select a preferred time.";
      return;
    }

    const startTime = toSqlDateTime(selectedDate.value, form.time);
    const endTime = addMinutesToSqlDateTime(startTime, 30);

    try {
      const { data } = await appointmentsApi.createGuestAppointment({
        name: form.fullName,
        email: form.email,
        appointment_type_id: Number(form.appointmentType),
        start_time: startTime,
        end_time: endTime,
        notes: form.notes.trim() || undefined,
      });

      localStorage.setItem(AUTH_STORAGE_KEYS.token, data.data.token);
      localStorage.setItem(
        AUTH_STORAGE_KEYS.user,
        JSON.stringify(data.data.user),
      );

      submitMessage.value = `Appointment request submitted. Temporary password: ${data.data.temporary_password}`;
    } catch (error) {
      if (axios.isAxiosError(error)) {
        submitMessage.value =
          error.response?.data?.message ?? "Unable to submit appointment.";
        return;
      }

      submitMessage.value = "Unable to submit appointment.";
    }
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
    appointmentTypes: computed(() => appointmentTypesQuery.data.value ?? []),
    appointmentTypesError: computed(() =>
      appointmentTypesQuery.isError.value
        ? "Unable to load appointment types."
        : "",
    ),
    isLoadingAppointmentTypes: computed(
      () =>
        appointmentTypesQuery.isPending.value ||
        appointmentTypesQuery.isFetching.value,
    ),
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
