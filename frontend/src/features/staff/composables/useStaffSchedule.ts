import { computed, ref, watch } from "vue";
import { useMutation, useQuery, useQueryClient } from "@tanstack/vue-query";
import { staffApi } from "../api/staffApi";
import type { StaffScheduleDay, StaffScheduleResponseData } from "../types";
import { usersApi } from "@/features/user-management/api/usersApi";

function normalizeTimeValue(value: string | null): string | null {
  if (!value) {
    return null;
  }

  return value.slice(0, 5);
}

function normalizeDays(days: StaffScheduleDay[]): StaffScheduleDay[] {
  return days.map((day) => ({
    day_of_week: day.day_of_week,
    is_enabled: day.is_enabled,
    start_time: normalizeTimeValue(day.start_time),
    end_time: normalizeTimeValue(day.end_time),
  }));
}

function cloneDays(days: StaffScheduleDay[]): StaffScheduleDay[] {
  return days.map((day) => ({ ...day }));
}

function getErrorMessage(error: unknown, fallback: string): string {
  if (error instanceof Error && error.message) {
    return error.message;
  }

  return fallback;
}

export function useStaffSchedule(mode: "self" | "admin", userId?: string, enabled = true) {
  const queryClient = useQueryClient();
  const pageMessage = ref("");
  const draftDays = ref<StaffScheduleDay[]>([]);

  const scheduleQuery = useQuery({
    queryKey: mode === "self" ? ["staff", "schedule"] : ["staff", "schedule", userId],
    enabled: enabled && (mode === "self" || Boolean(userId)),
    queryFn: async () => {
      if (mode === "self") {
        const { data } = await staffApi.getMySchedule();
        return data.data;
      }

      const { data } = await usersApi.getUserStaffSchedule(userId!);
      return data.data;
    },
  });

  watch(
    () => scheduleQuery.data.value?.days,
    (days) => {
      if (!days) {
        draftDays.value = [];
        return;
      }

      draftDays.value = cloneDays(normalizeDays(days));
    },
    { immediate: true },
  );

  const updateScheduleMutation = useMutation({
    mutationFn: async (days: StaffScheduleDay[]) => {
      if (mode === "self") {
        const { data } = await staffApi.updateMySchedule(days);
        return data.data;
      }

      const { data } = await usersApi.updateUserStaffSchedule(userId!, days);
      return data.data;
    },
    onSuccess: (payload) => {
      pageMessage.value = "Weekly schedule updated successfully.";
      draftDays.value = cloneDays(normalizeDays(payload.days));
      queryClient.setQueryData(
        mode === "self" ? ["staff", "schedule"] : ["staff", "schedule", userId],
        payload,
      );
      queryClient.invalidateQueries({ queryKey: ["appointments", "resources"] });
    },
  });

  return {
    schedule: computed<StaffScheduleResponseData | null>(() => scheduleQuery.data.value ?? null),
    draftDays,
    isLoadingSchedule: computed(() => scheduleQuery.isPending.value || scheduleQuery.isFetching.value),
    scheduleError: computed(() => {
      if (scheduleQuery.isError.value) {
        return getErrorMessage(scheduleQuery.error.value, "Failed to load weekly schedule.");
      }

      if (updateScheduleMutation.isError.value) {
        return getErrorMessage(updateScheduleMutation.error.value, "Failed to update weekly schedule.");
      }

      return "";
    }),
    pageMessage,
    isSavingSchedule: computed(() => updateScheduleMutation.isPending.value),
    setDraftDay(dayOfWeek: number, updates: Partial<StaffScheduleDay>) {
      draftDays.value = draftDays.value.map((day) =>
        day.day_of_week === dayOfWeek
          ? { ...day, ...updates }
          : day,
      );
    },
    saveSchedule: async () => {
      pageMessage.value = "";
      await updateScheduleMutation.mutateAsync(cloneDays(draftDays.value));
    },
    clearPageMessage() {
      pageMessage.value = "";
    },
  };
}
