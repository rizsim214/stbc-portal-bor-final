import { onBeforeUnmount, watch } from "vue";
import { useQuery, useQueryClient } from "@tanstack/vue-query";
import { appointmentsApi } from "../api/appointmentsApi";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import { useAppointmentStatusNotificationStore } from "@/shared/stores/useAppointmentStatusNotificationStore";
import { formatAppointmentStatus } from "../utils/status";
import {
  subscribeToPatientAppointmentLifecycle,
  unsubscribeFromPatientAppointmentLifecycle,
} from "./useSharedPatientAppointmentChannel";
import type { AppointmentListItem } from "../types";

type AppointmentStatusPayload = {
  action: string;
  appointment: {
    id: number;
    user_id: number;
    status: string;
  };
  occurred_at: string;
};

const NOTIFIABLE_STATUSES = new Set([
  "assigned",
  "checkup_ongoing",
  "awaiting_result",
  "releasing_lab_result",
  "completed",
]);

function isPatientRole(roleName?: string | null): boolean {
  const normalizedRole = roleName?.trim().toLowerCase();
  return normalizedRole === "patient" || normalizedRole === "user";
}

function getStatusMessage(payload: AppointmentStatusPayload): string | null {
  const normalizedAction = payload.action.trim().toLowerCase();
  const normalizedStatus = payload.appointment.status.trim().toLowerCase();

  if (!NOTIFIABLE_STATUSES.has(normalizedStatus)) {
    return null;
  }

  if (
    normalizedAction !== "status_updated" &&
    normalizedAction !== "assigned"
  ) {
    return null;
  }

  switch (normalizedStatus) {
    case "assigned":
      return "Your appointment is now assigned to a staff member.";
    case "checkup_ongoing":
      return "Your appointment is now in progress.";
    case "awaiting_result":
      return "Your appointment is awaiting lab results.";
    case "releasing_lab_result":
      return "Your lab result is being prepared for release.";
    case "completed":
      return "Your appointment has been completed.";
    default:
      return `Your appointment status changed to ${formatAppointmentStatus(payload.appointment.status)}.`;
  }
}

function buildCatchupPayload(
  appointment: AppointmentListItem,
  occurredAt: string,
): AppointmentStatusPayload {
  const normalizedStatus = appointment.status.trim().toLowerCase();

  return {
    action: normalizedStatus === "assigned" ? "assigned" : "status_updated",
    appointment: {
      id: appointment.id,
      user_id: appointment.user_id,
      status: appointment.status,
    },
    occurred_at: occurredAt,
  };
}

export function usePatientAppointmentStatusNotifications() {
  const authStore = useAuthStore();
  const queryClient = useQueryClient();
  const notificationStore = useAppointmentStatusNotificationStore();
  let activeChannelName = "";
  const appointmentCatchupQuery = useQuery({
    queryKey: ["appointments", "notification-catchup", authStore.user?.id ?? 0],
    enabled: false,
    queryFn: async () => {
      const { data } = await appointmentsApi.listMyAppointments(1, 50);
      return data.data;
    },
    staleTime: 30_000,
    refetchOnWindowFocus: false,
  });

  function handleAppointmentStatusUpdate(
    payload: AppointmentStatusPayload,
  ): void {
    const message = getStatusMessage(payload);
    const normalizedStatus = payload.appointment.status.trim().toLowerCase();

    if (!message) {
      return;
    }

    queryClient.invalidateQueries({ queryKey: ["appointments", "list"] });
    queryClient.invalidateQueries({
      queryKey: ["appointments", "detail", String(payload.appointment.id)],
    });

    notificationStore.addNotification({
      appointmentId: payload.appointment.id,
      message,
      status: payload.appointment.status,
      occurredAt: payload.occurred_at,
      dedupeKey: `${payload.appointment.id}:${payload.action}:${payload.occurred_at}`,
    });
    notificationStore.trackStatus(payload.appointment.id, normalizedStatus);
  }

  function syncAppointmentCatchupNotifications(
    appointments: AppointmentListItem[],
  ): void {
    appointments.forEach((appointment) => {
      const normalizedStatus = appointment.status.trim().toLowerCase();
      const trackedStatus = notificationStore.getTrackedStatus(appointment.id);

      if (trackedStatus === normalizedStatus) {
        return;
      }

      if (!NOTIFIABLE_STATUSES.has(normalizedStatus)) {
        notificationStore.trackStatus(appointment.id, normalizedStatus);
        return;
      }

      const occurredAt =
        appointment.updated_at ??
        appointment.created_at ??
        new Date().toISOString();

      handleAppointmentStatusUpdate(
        buildCatchupPayload(appointment, occurredAt),
      );
    });
  }

  function leaveActiveChannel(): void {
    if (!activeChannelName) {
      return;
    }

    unsubscribeFromPatientAppointmentLifecycle(
      activeChannelName,
      handleAppointmentStatusUpdate,
    );
    activeChannelName = "";
  }

  function syncRealtimeSubscription(): void {
    const userId = authStore.user?.id;
    const roleName = authStore.user?.role?.name?.toLowerCase();
    const isPatient = isPatientRole(roleName);
    const nextChannelName =
      authStore.isAuthenticated && isPatient && userId
        ? `users.${userId}.appointments`
        : "";

    if (!nextChannelName) {
      leaveActiveChannel();
      return;
    }

    if (nextChannelName === activeChannelName) {
      return;
    }

    leaveActiveChannel();

    const didSubscribe = subscribeToPatientAppointmentLifecycle(
      nextChannelName,
      handleAppointmentStatusUpdate,
    );

    if (didSubscribe) {
      activeChannelName = nextChannelName;
    }
  }

  async function syncCatchupNotifications(): Promise<void> {
    const roleName = authStore.user?.role?.name?.toLowerCase();

    if (
      !authStore.isAuthenticated ||
      authStore.isBootstrapping ||
      !authStore.user?.id ||
      !isPatientRole(roleName)
    ) {
      return;
    }

    try {
      const appointments = await appointmentCatchupQuery.refetch();

      if (appointments.data) {
        syncAppointmentCatchupNotifications(appointments.data);
      }
    } catch {
      // Ignore notification catch-up failures and keep realtime active.
    }
  }

  watch(
    () => [
      authStore.isAuthenticated,
      authStore.isBootstrapping,
      authStore.user?.id ?? 0,
      authStore.user?.role?.name ?? "",
    ],
    syncRealtimeSubscription,
    { immediate: true },
  );

  watch(
    () => [
      authStore.isAuthenticated,
      authStore.isBootstrapping,
      authStore.user?.id ?? 0,
      authStore.user?.role?.name ?? "",
    ],
    () => {
      void syncCatchupNotifications();
    },
    { immediate: true },
  );

  onBeforeUnmount(() => {
    leaveActiveChannel();
  });
}
