import { CalendarDate, getLocalTimeZone, today } from "@internationalized/date";
import { computed, reactive, ref, watch } from "vue";
import { useMutation, useQuery, useQueryClient } from "@tanstack/vue-query";
import {
  appointmentsApi,
  mapAppointmentTypeOption,
} from "../api/appointmentsApi";
import type {
  AppointmentEditFormState,
  AppointmentListItem,
  AssignableResource,
  AppointmentTypeOption,
} from "../types";
import {
  addMinutesToSqlDateTime,
  dateToCalendarDate,
  extractTimeValue,
  toDateInput,
  toSqlDateTime,
} from "../utils/schedule";
import { useAppointmentRealtime } from "./useAppointmentRealtime";

function getErrorMessage(error: unknown, fallback: string): string {
  if (error instanceof Error && error.message) {
    return error.message;
  }

  return fallback;
}

export function useAppointmentDetailData(
  appointmentId: string,
  mode: "patient" | "admin",
) {
  const queryClient = useQueryClient();
  useAppointmentRealtime(mode === "admin" ? "admin" : "mine");
  const pageMessage = ref("");
  const pageError = ref("");
  const selectedResourceId = ref("");
  const selectedEditDate = ref<CalendarDate | undefined>();
  const datePlaceholder = today(getLocalTimeZone());
  const editForm = reactive<AppointmentEditFormState>({
    appointmentType: "",
    time: "",
    notes: "",
  });

  const appointmentQuery = useQuery({
    queryKey: ["appointments", "detail", appointmentId],
    queryFn: async () => {
      const { data } = await appointmentsApi.getAppointment(appointmentId);
      return data.data;
    },
  });

  const appointmentTypesQuery = useQuery({
    queryKey: ["appointments", "types"],
    queryFn: async () => {
      const { data } = await appointmentsApi.listAppointmentTypes();
      return data.data.map(mapAppointmentTypeOption);
    },
  });

  const resourcesQuery = useQuery({
    queryKey: ["appointments", "resources", appointmentId],
    queryFn: async () => {
      const { data } =
        await appointmentsApi.listAssignableResources(appointmentId);
      return data.data;
    },
    enabled: mode === "admin",
  });

  const availabilityQuery = useQuery({
    queryKey: computed(() => [
      "appointments",
      "availability",
      selectedEditDate.value ? toDateInput(selectedEditDate.value) : "",
      editForm.appointmentType,
      appointmentId,
    ]),
    queryFn: async () => {
      const currentDate = selectedEditDate.value;

      if (!currentDate) {
        return [];
      }

      const { data } = await appointmentsApi.listAppointmentAvailability(
        toDateInput(currentDate),
        Number(editForm.appointmentType),
        Number(appointmentId),
      );

      return data.slots;
    },
    enabled: computed(() =>
      Boolean(selectedEditDate.value && editForm.appointmentType),
    ),
  });

  const assignMutation = useMutation({
    mutationFn: async () => {
      if (!selectedResourceId.value) {
        throw new Error("Please select a staff member.");
      }

      const { data } = await appointmentsApi.assignAppointmentResource(
        appointmentId,
        Number(selectedResourceId.value),
      );

      return data.data;
    },
    onSuccess: (updatedAppointment) => {
      pageError.value = "";
      pageMessage.value = "Assigned staff updated successfully.";
      queryClient.setQueryData(
        ["appointments", "detail", appointmentId],
        updatedAppointment,
      );
      queryClient.invalidateQueries({ queryKey: ["appointments", "list"] });
    },
    onError: (error: unknown) => {
      pageMessage.value = "";
      pageError.value = getErrorMessage(
        error,
        "Failed to update assigned staff.",
      );
    },
  });

  const updateStatusMutation = useMutation({
    mutationFn: async (status: string) => {
      const { data } = await appointmentsApi.updateAppointmentStatus(appointmentId, {
        status,
      });

      return data.data;
    },
    onSuccess: (updatedAppointment) => {
      pageError.value = "";
      pageMessage.value = "Appointment status updated successfully.";
      queryClient.setQueryData(
        ["appointments", "detail", appointmentId],
        updatedAppointment,
      );
      queryClient.invalidateQueries({ queryKey: ["appointments", "list"] });
    },
    onError: (error: unknown) => {
      pageMessage.value = "";
      pageError.value = getErrorMessage(error, "Failed to update appointment status.");
    },
  });

  const updateMutation = useMutation({
    mutationFn: async () => {
      if (!selectedEditDate.value) {
        throw new Error("Please select an appointment date.");
      }

      if (!editForm.appointmentType) {
        throw new Error("Please select an appointment type.");
      }

      if (!editForm.time) {
        throw new Error("Please select an available time slot.");
      }

      const startTime = toSqlDateTime(selectedEditDate.value, editForm.time);
      const endTime = addMinutesToSqlDateTime(startTime, 30);
      const { data } = await appointmentsApi.updateAppointment(appointmentId, {
        appointment_type_id: Number(editForm.appointmentType),
        start_time: startTime,
        end_time: endTime,
        notes: editForm.notes.trim() || undefined,
      });

      return data.data;
    },
    onSuccess: (updatedAppointment) => {
      pageError.value = "";
      pageMessage.value = "Appointment details updated successfully.";
      queryClient.setQueryData(
        ["appointments", "detail", appointmentId],
        updatedAppointment,
      );
      queryClient.invalidateQueries({ queryKey: ["appointments", "list"] });
      queryClient.invalidateQueries({
        queryKey: ["appointments", "availability"],
      });
      hydrateEditor(updatedAppointment);
    },
    onError: (error: unknown) => {
      pageMessage.value = "";
      pageError.value = getErrorMessage(error, "Failed to update appointment.");
    },
  });

  function hydrateEditor(currentAppointment: AppointmentListItem | null): void {
    if (!currentAppointment?.start_time) {
      return;
    }

    const startDate = new Date(currentAppointment.start_time.replace(" ", "T"));
    selectedEditDate.value = dateToCalendarDate(startDate);
    editForm.appointmentType = String(currentAppointment.appointment_type_id);
    editForm.time = extractTimeValue(currentAppointment.start_time);
    editForm.notes = currentAppointment.notes ?? "";
    selectedResourceId.value = currentAppointment.resources?.[0]
      ? String(currentAppointment.resources[0].id)
      : "";
  }

  watch(
    () => appointmentQuery.data.value,
    (currentAppointment) => {
      hydrateEditor(currentAppointment ?? null);
    },
    { immediate: true },
  );

  watch(
    () => availabilityQuery.data.value,
    (slots) => {
      const nextOptions = (slots ?? []).map((slot) =>
        extractTimeValue(slot.start_time),
      );

      if (nextOptions.includes(editForm.time)) {
        return;
      }

      editForm.time = nextOptions[0] ?? "";
    },
    { immediate: true },
  );

  const appointment = computed<AppointmentListItem | null>(
    () => appointmentQuery.data.value ?? null,
  );
  const appointmentTypes = computed<AppointmentTypeOption[]>(
    () => appointmentTypesQuery.data.value ?? [],
  );
  const assignableResources = computed<AssignableResource[]>(
    () => resourcesQuery.data.value ?? [],
  );
  const availableTimeOptions = computed(() =>
    (availabilityQuery.data.value ?? []).map((slot) =>
      extractTimeValue(slot.start_time),
    ),
  );
  const isLoadingAppointment = computed(
    () => appointmentQuery.isPending.value || appointmentQuery.isFetching.value,
  );
  const isLoadingAppointmentTypes = computed(
    () =>
      appointmentTypesQuery.isPending.value ||
      appointmentTypesQuery.isFetching.value,
  );
  const isLoadingResources = computed(
    () => resourcesQuery.isPending.value || resourcesQuery.isFetching.value,
  );
  const isLoadingAvailability = computed(
    () =>
      availabilityQuery.isPending.value || availabilityQuery.isFetching.value,
  );
  const dataError = computed(() => {
    if (appointmentQuery.isError.value) {
      return getErrorMessage(
        appointmentQuery.error.value,
        "Failed to load appointment.",
      );
    }

    if (appointmentTypesQuery.isError.value) {
      return getErrorMessage(
        appointmentTypesQuery.error.value,
        "Failed to load appointment types.",
      );
    }

    if (resourcesQuery.isError.value) {
      return getErrorMessage(
        resourcesQuery.error.value,
        "Failed to load assignable staff.",
      );
    }

    if (availabilityQuery.isError.value) {
      return getErrorMessage(
        availabilityQuery.error.value,
        "Failed to load available schedule.",
      );
    }

    return pageError.value;
  });

  function dismissPageState(): void {
    pageError.value = "";
    pageMessage.value = "";
  }

  function setSelectedEditDate(value: CalendarDate | undefined): void {
    selectedEditDate.value = value;
  }

  return {
    appointment,
    appointmentTypes,
    assignableResources,
    editForm,
    selectedEditDate,
    selectedResourceId,
    datePlaceholder,
    availableTimeOptions,
    isLoadingAppointment,
    isLoadingAppointmentTypes,
    isLoadingResources,
    isLoadingAvailability,
    isAssigningResource: computed(() => assignMutation.isPending.value),
    isUpdatingStatus: computed(() => updateStatusMutation.isPending.value),
    isUpdatingAppointment: computed(() => updateMutation.isPending.value),
    dataError,
    pageMessage,
    dismissPageState,
    setSelectedEditDate,
    assignSelectedResource: async () => {
      await assignMutation.mutateAsync();
    },
    updateAppointmentStatus: async (status: string) => {
      await updateStatusMutation.mutateAsync(status);
    },
    updateAppointmentDetails: async () => {
      await updateMutation.mutateAsync();
    },
  };
}
