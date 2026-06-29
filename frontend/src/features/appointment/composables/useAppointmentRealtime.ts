import { onBeforeUnmount } from "vue";
import { useQueryClient } from "@tanstack/vue-query";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import { getEcho } from "@/shared/realtime/echo";
import { formatAppointmentStatus } from "../utils/status";
import { useRealtimeNotificationStore } from "@/shared/stores/useRealtimeNotificationStore";
import {
  subscribeToPatientAppointmentLifecycle,
  unsubscribeFromPatientAppointmentLifecycle,
} from "./useSharedPatientAppointmentChannel";

type AppointmentRealtimePayload = {
  action: string;
  appointment: {
    id: number;
    user_id: number;
    status: string;
  };
  occurred_at: string;
};

export function useAppointmentRealtime(mode: "mine" | "admin") {
  const authStore = useAuthStore();
  const queryClient = useQueryClient();
  const realtimeNotificationStore = useRealtimeNotificationStore();
  const echo = getEcho();
  const userId = authStore.user?.id;

  if (!echo || !userId) {
    return;
  }

  const userChannel = `users.${userId}.appointments`;
  const adminChannel = "admin.appointments";

  const getNotificationMessage = (payload: AppointmentRealtimePayload): string | null => {
    if (mode !== "mine") {
      return null;
    }

    const normalizedAction = payload.action.trim().toLowerCase();
    const normalizedStatus = payload.appointment.status.trim().toLowerCase();

    if (normalizedAction === "created") {
      return "Your appointment request was submitted successfully.";
    }

    if (normalizedAction === "updated") {
      return "Your appointment details were updated.";
    }

    if (normalizedAction === "assigned" || normalizedStatus === "assigned") {
      return "A staff member has been assigned to your appointment.";
    }

    if (normalizedAction === "status_updated") {
      switch (normalizedStatus) {
        case "checkup_ongoing":
          return "Your appointment is now in progress.";
        case "awaiting_result":
          return "Your appointment is awaiting results.";
        case "releasing_lab_result":
          return "Your lab result is now being released.";
        case "completed":
          return "Your appointment has been completed.";
        default:
          return `Your appointment status changed to ${formatAppointmentStatus(payload.appointment.status)}.`;
      }
    }

    return `Your appointment was updated to ${formatAppointmentStatus(payload.appointment.status)}.`;
  };

  const refreshQueries = (payload: AppointmentRealtimePayload) => {
    queryClient.invalidateQueries({ queryKey: ["appointments", "list"] });
    queryClient.invalidateQueries({ queryKey: ["appointments", "activity"] });
    queryClient.invalidateQueries({
      queryKey: ["appointments", "detail", String(payload.appointment.id)],
    });

    const message = getNotificationMessage(payload);
    if (message) {
      realtimeNotificationStore.showNotification({
        message,
        dedupeKey: `${payload.appointment.id}:${payload.action}:${payload.occurred_at}`,
      });
    }
  };

  subscribeToPatientAppointmentLifecycle(userChannel, refreshQueries);

  if (mode === "admin" && authStore.user?.role?.name === "admin") {
    echo
      .private(adminChannel)
      .listen(".appointment.lifecycle.updated", refreshQueries);
  }

  onBeforeUnmount(() => {
    unsubscribeFromPatientAppointmentLifecycle(userChannel, refreshQueries);

    if (mode === "admin" && authStore.user?.role?.name === "admin") {
      echo.leave(`private-${adminChannel}`);
    }
  });
}
