import axios from "axios";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import type { CalendarOptions, DatesSetArg } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import { CalendarDate, getLocalTimeZone, today } from "@internationalized/date";
import { useQuery, useQueryClient } from "@tanstack/vue-query";
import { computed, reactive, ref, watch } from "vue";
import { useRouter } from "vue-router";
import {
  appointmentsApi,
  extractTimeValue,
  mapAppointmentCalendarEvent,
  mapAppointmentTypeOption,
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

function toCalendarRangeValue(date: Date): string {
  return (
    [
      date.getFullYear(),
      padTime(date.getMonth() + 1),
      padTime(date.getDate()),
    ].join("-") +
    ` ${padTime(date.getHours())}:${padTime(date.getMinutes())}:${padTime(date.getSeconds())}`
  );
}

export function useAppointmentBooking() {
  const authStore = useAuthStore();
  const queryClient = useQueryClient();
  const router = useRouter();
  const form = reactive<AppointmentFormState>({
    fullName: authStore.user?.name ?? "",
    email: authStore.user?.email ?? "",
    appointmentType: "",
    time: "",
    notes: "",
  });

  const selectedDate = ref<CalendarDate | undefined>();
  const datePlaceholder = today(getLocalTimeZone());
  const submitMessage = ref("");
  const minDate = toDateInput(datePlaceholder);
  const initialRangeStartDate = new Date(`${minDate}T00:00:00`);
  const initialRangeEndDate = new Date(initialRangeStartDate);
  initialRangeEndDate.setDate(initialRangeEndDate.getDate() + 7);
  const calendarRangeStart = ref(toCalendarRangeValue(initialRangeStartDate));
  const calendarRangeEnd = ref(toCalendarRangeValue(initialRangeEndDate));
  const bookingMode = computed(() =>
    authStore.isAuthenticated ? "user" : "guest",
  );
  const availabilityEnabled = computed(() =>
    Boolean(selectedDate.value && form.appointmentType),
  );
  const appointmentTypesQuery = useQuery({
    queryKey: ["appointments", "types"],
    queryFn: async () => {
      const { data } = await appointmentsApi.listAppointmentTypes();
      return data.data.map(mapAppointmentTypeOption);
    },
  });
  const availabilityQuery = useQuery({
    queryKey: computed(() => [
      "appointments",
      "availability",
      selectedDate.value ? toDateInput(selectedDate.value) : "",
      form.appointmentType,
    ]),
    queryFn: async () => {
      const currentDate = selectedDate.value;

      if (!currentDate) {
        return [];
      }

      const { data } = await appointmentsApi.listAppointmentAvailability(
        toDateInput(currentDate),
        Number(form.appointmentType),
      );

      return data.slots;
    },
    enabled: availabilityEnabled,
  });
  const calendarEventsQuery = useQuery({
    queryKey: computed(() => [
      "appointments",
      "calendar",
      calendarRangeStart.value,
      calendarRangeEnd.value,
    ]),
    queryFn: async () => {
      const { data } = await appointmentsApi.listAppointmentCalendar(
        calendarRangeStart.value,
        calendarRangeEnd.value,
      );

      return data.data.map(mapAppointmentCalendarEvent);
    },
  });

  function hydrateFormFromDate(date: Date): void {
    selectedDate.value = dateToCalendarDate(date);

    const minutes = date.getMinutes();
    const normalizedMinutes = minutes === 0 || minutes === 30 ? minutes : 0;
    const normalizedTime = `${padTime(date.getHours())}:${padTime(normalizedMinutes)}`;
    const availableTimes = availableTimeOptions.value;

    if (availableTimes.includes(normalizedTime)) {
      form.time = normalizedTime;
      return;
    }

    form.time = availableTimes[0] ?? "";
  }

  function handleCalendarDatesSet(range: DatesSetArg): void {
    calendarRangeStart.value = toCalendarRangeValue(range.start);
    calendarRangeEnd.value = toCalendarRangeValue(range.end);
  }

  const calendarOptions = computed<CalendarOptions>(() => ({
    plugins: [dayGridPlugin, timeGridPlugin],
    initialView: "timeGridWeek",
    initialDate: minDate,
    height: "auto",
    contentHeight: 920,
    allDaySlot: false,
    slotMinTime: "09:00:00",
    slotMaxTime: "16:30:00",
    slotDuration: "00:30:00",
    slotLabelInterval: "00:30:00",
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
    datesSet: handleCalendarDatesSet,
    events: calendarEventsQuery.data.value ?? [],
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
  const availableTimeOptions = computed(() =>
    (availabilityQuery.data.value ?? []).map((slot) =>
      extractTimeValue(slot.start_time),
    ),
  );

  watch(
    () => availableTimeOptions.value,
    (nextOptions) => {
      if (!selectedDate.value || !form.appointmentType) {
        form.time = "";
        return;
      }

      if (nextOptions.includes(form.time)) {
        return;
      }

      form.time = nextOptions[0] ?? "";
    },
    { immediate: true },
  );

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
      if (bookingMode.value === "guest") {
        await appointmentsApi.createGuestAppointment({
          name: form.fullName,
          email: form.email,
          appointment_type_id: Number(form.appointmentType),
          start_time: startTime,
          end_time: endTime,
          notes: form.notes.trim() || undefined,
        });

        await queryClient.invalidateQueries({
          queryKey: ["appointments", "calendar"],
        });
        submitMessage.value = "Appointment request submitted. Check your email for your temporary password.";
        return;
      }

      await appointmentsApi.createMyAppointmentRequest({
        appointment_type_id: Number(form.appointmentType),
        start_time: startTime,
        end_time: endTime,
        notes: form.notes.trim() || undefined,
      });

      await queryClient.invalidateQueries({
        queryKey: ["appointments", "calendar"],
      });
      await queryClient.invalidateQueries({
        queryKey: ["appointments", "list"],
      });
      await router.replace({ name: "MyAppointmentList" });
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
    const hours = form.time ? Number(form.time.split(":")[0]) : 9;
    const minutes = form.time ? Number(form.time.split(":")[1]) : 0;
    date.setHours(hours, minutes, 0, 0);

    hydrateFormFromDate(date);
  }

  return {
    bookingMode,
    appointmentTypes: computed(() => appointmentTypesQuery.data.value ?? []),
    appointmentTypesError: computed(() =>
      appointmentTypesQuery.isError.value
        ? "Unable to load appointment types."
        : "",
    ),
    availabilityError: computed(() =>
      availabilityEnabled.value && availabilityQuery.isError.value
        ? "Unable to load available time slots."
        : "",
    ),
    isLoadingAppointmentTypes: computed(
      () =>
        appointmentTypesQuery.isPending.value ||
        appointmentTypesQuery.isFetching.value,
    ),
    isLoadingAvailability: computed(
      () =>
        availabilityEnabled.value &&
        (availabilityQuery.isPending.value ||
          availabilityQuery.isFetching.value),
    ),
    availableTimeOptions,
    calendarOptions,
    datePlaceholder,
    form,
    selectedDate,
    setSelectedDate,
    submitMessage,
    summaryText,
    formatSelectedDate,
    formatTimeValue,
    submitAppointment,
  };
}
