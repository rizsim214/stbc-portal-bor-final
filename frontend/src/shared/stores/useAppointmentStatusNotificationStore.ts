import { defineStore } from "pinia";

export type AppointmentStatusNotificationItem = {
  id: string;
  appointmentId: number;
  message: string;
  status: string;
  occurredAt: string;
  isRead: boolean;
  dedupeKey: string;
};

type AddAppointmentStatusNotificationPayload = {
  appointmentId: number;
  message: string;
  status: string;
  occurredAt: string;
  dedupeKey: string;
};

const MAX_NOTIFICATIONS = 20;
const STORAGE_KEY = "appointment_status_notifications";

type PersistedAppointmentStatusNotificationState = {
  items: AppointmentStatusNotificationItem[];
  lastDedupeKey: string;
  trackedStatuses: Record<string, string>;
};

function safeParsePersistedState(): PersistedAppointmentStatusNotificationState {
  const fallback: PersistedAppointmentStatusNotificationState = {
    items: [],
    lastDedupeKey: "",
    trackedStatuses: {},
  };

  const raw =
    typeof globalThis.localStorage === "undefined"
      ? null
      : globalThis.localStorage.getItem(STORAGE_KEY);

  if (!raw) {
    return fallback;
  }

  try {
    const parsed = JSON.parse(raw) as Partial<PersistedAppointmentStatusNotificationState>;

    return {
      items: Array.isArray(parsed.items) ? parsed.items : [],
      lastDedupeKey:
        typeof parsed.lastDedupeKey === "string" ? parsed.lastDedupeKey : "",
      trackedStatuses:
        parsed.trackedStatuses &&
        typeof parsed.trackedStatuses === "object" &&
        !Array.isArray(parsed.trackedStatuses)
          ? (parsed.trackedStatuses as Record<string, string>)
          : {},
    };
  } catch {
    return fallback;
  }
}

export const useAppointmentStatusNotificationStore = defineStore(
  "appointment-status-notification",
  {
    state: (): PersistedAppointmentStatusNotificationState =>
      safeParsePersistedState(),

    getters: {
      unreadCount: (state) => state.items.filter((item) => !item.isRead).length,
    },

    actions: {
      persistState(): void {
        if (typeof globalThis.localStorage === "undefined") {
          return;
        }

        globalThis.localStorage.setItem(
          STORAGE_KEY,
          JSON.stringify({
            items: this.items,
            lastDedupeKey: this.lastDedupeKey,
            trackedStatuses: this.trackedStatuses,
          }),
        );
      },

      addNotification(payload: AddAppointmentStatusNotificationPayload): void {
        if (
          payload.dedupeKey === this.lastDedupeKey ||
          this.items.some(
            (item) =>
              item.dedupeKey === payload.dedupeKey ||
              (item.appointmentId === payload.appointmentId &&
                item.status === payload.status),
          )
        ) {
          return;
        }

        this.items.unshift({
          id: `${payload.appointmentId}-${payload.occurredAt}`,
          appointmentId: payload.appointmentId,
          message: payload.message,
          status: payload.status,
          occurredAt: payload.occurredAt,
          isRead: false,
          dedupeKey: payload.dedupeKey,
        });
        this.items = this.items.slice(0, MAX_NOTIFICATIONS);
        this.lastDedupeKey = payload.dedupeKey;
        this.persistState();
      },

      markAllRead(): void {
        this.items = this.items.map((item) => ({
          ...item,
          isRead: true,
        }));
        this.persistState();
      },

      getTrackedStatus(appointmentId: number): string {
        return this.trackedStatuses[String(appointmentId)] ?? "";
      },

      trackStatus(appointmentId: number, status: string): void {
        this.trackedStatuses = {
          ...this.trackedStatuses,
          [String(appointmentId)]: status,
        };
        this.persistState();
      },

      clearNotifications(): void {
        this.items = [];
        this.lastDedupeKey = "";
        this.trackedStatuses = {};
        if (typeof globalThis.localStorage !== "undefined") {
          globalThis.localStorage.removeItem(STORAGE_KEY);
        }
      },
    },
  },
);
