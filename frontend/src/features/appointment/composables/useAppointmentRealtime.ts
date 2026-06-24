import { onBeforeUnmount } from "vue";
import { useQueryClient } from "@tanstack/vue-query";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import { getEcho } from "@/shared/realtime/echo";

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
  const echo = getEcho();
  const userId = authStore.user?.id;

  if (!echo || !userId) {
    return;
  }

  const userChannel = `users.${userId}.appointments`;
  const adminChannel = "admin.appointments";

  const refreshQueries = (payload: AppointmentRealtimePayload) => {
    queryClient.invalidateQueries({ queryKey: ["appointments", "list"] });
    queryClient.invalidateQueries({ queryKey: ["appointments", "activity"] });
    queryClient.invalidateQueries({
      queryKey: ["appointments", "detail", String(payload.appointment.id)],
    });
  };

  echo
    .private(userChannel)
    .listen(".appointment.lifecycle.updated", refreshQueries);

  if (mode === "admin" && authStore.user?.role?.name === "admin") {
    echo
      .private(adminChannel)
      .listen(".appointment.lifecycle.updated", refreshQueries);
  }

  onBeforeUnmount(() => {
    echo.leave(`private-${userChannel}`);

    if (mode === "admin" && authStore.user?.role?.name === "admin") {
      echo.leave(`private-${adminChannel}`);
    }
  });
}
