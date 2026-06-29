import { onBeforeUnmount, watch } from "vue";
import { useQueryClient } from "@tanstack/vue-query";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import { useAppointmentStatusNotificationStore } from "@/shared/stores/useAppointmentStatusNotificationStore";
import { formatAppointmentStatus } from "../utils/status";
import {
  subscribeToPatientAppointmentLifecycle,
  unsubscribeFromPatientAppointmentLifecycle,
} from "./useSharedPatientAppointmentChannel";

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

  function getStatusMessage(payload: AppointmentStatusPayload): string | null {
    const normalizedAction = payload.action.trim().toLowerCase();
    const normalizedStatus = payload.appointment.status.trim().toLowerCase();

    if (
      !NOTIFIABLE_STATUSES.has(normalizedStatus)
    ) {
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

export function usePatientAppointmentStatusNotifications() {
  const authStore = useAuthStore();
  const queryClient = useQueryClient();
  const notificationStore = useAppointmentStatusNotificationStore();
  let activeChannelName = "";

  function handleAppointmentStatusUpdate(
    payload: AppointmentStatusPayload,
  ): void {
    const message = getStatusMessage(payload);

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
    const isPatient = roleName === "patient" || roleName === "user";
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

  onBeforeUnmount(() => {
    leaveActiveChannel();
  });
}
