import { computed, onBeforeUnmount } from "vue";
import { useQueryClient } from "@tanstack/vue-query";
import { getEcho } from "@/shared/realtime/echo";

type BookingRealtimeOptions = {
  selectedDate?: () => string;
  appointmentType?: () => string;
};

type AppointmentCalendarRealtimePayload = {
  action: string;
  appointment: {
    id: number;
    appointment_type_id: number;
    start_time: string;
    end_time: string;
    status: string;
  };
  occurred_at: string;
};

export function useAppointmentBookingRealtime(options: BookingRealtimeOptions = {}) {
  const queryClient = useQueryClient();
  const echo = getEcho();
  const channelName = "appointments.calendar";
  const currentDate = computed(() => options.selectedDate?.() ?? "");
  const currentAppointmentType = computed(() => options.appointmentType?.() ?? "");

  if (!echo) {
    return;
  }

  const refreshQueries = (_payload: AppointmentCalendarRealtimePayload) => {
    queryClient.invalidateQueries({ queryKey: ["appointments", "calendar"] });

    if (!currentDate.value || !currentAppointmentType.value) {
      return;
    }

    queryClient.invalidateQueries({
      queryKey: ["appointments", "availability", currentDate.value, currentAppointmentType.value],
    });
  };

  echo
    .channel(channelName)
    .listen(".appointment.calendar.updated", refreshQueries);

  onBeforeUnmount(() => {
    echo.leave(channelName);
  });
}
