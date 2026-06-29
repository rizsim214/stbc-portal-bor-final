import { getEcho } from "@/shared/realtime/echo";

type AppointmentLifecyclePayload = {
  action: string;
  appointment: {
    id: number;
    user_id: number;
    status: string;
  };
  occurred_at: string;
};

type AppointmentLifecycleListener = (payload: AppointmentLifecyclePayload) => void;

type ChannelSubscription = {
  listeners: Set<AppointmentLifecycleListener>;
  dispatcher: AppointmentLifecycleListener;
};

const subscriptions = new Map<string, ChannelSubscription>();
const EVENT_NAME = ".appointment.lifecycle.updated";

export function subscribeToPatientAppointmentLifecycle(
  channelName: string,
  listener: AppointmentLifecycleListener,
): boolean {
  const existing = subscriptions.get(channelName);

  if (existing) {
    existing.listeners.add(listener);
    return true;
  }

  const listeners = new Set<AppointmentLifecycleListener>([listener]);
  const dispatcher: AppointmentLifecycleListener = (payload) => {
    listeners.forEach((registeredListener) => {
      registeredListener(payload);
    });
  };

  const echo = getEcho();

  if (!echo) {
    return false;
  }

  echo.private(channelName).listen(EVENT_NAME, dispatcher);

  subscriptions.set(channelName, {
    listeners,
    dispatcher,
  });

  return true;
}

export function unsubscribeFromPatientAppointmentLifecycle(
  channelName: string,
  listener: AppointmentLifecycleListener,
): void {
  const existing = subscriptions.get(channelName);

  if (!existing) {
    return;
  }

  existing.listeners.delete(listener);

  if (existing.listeners.size > 0) {
    return;
  }

  getEcho()?.leave(`private-${channelName}`);
  subscriptions.delete(channelName);
}
