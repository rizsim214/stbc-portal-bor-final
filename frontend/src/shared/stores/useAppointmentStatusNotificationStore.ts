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

export const useAppointmentStatusNotificationStore = defineStore(
  "appointment-status-notification",
  {
    state: () => ({
      items: [] as AppointmentStatusNotificationItem[],
      lastDedupeKey: "",
    }),

    getters: {
      unreadCount: (state) => state.items.filter((item) => !item.isRead).length,
    },

    actions: {
      addNotification(payload: AddAppointmentStatusNotificationPayload): void {
        if (
          payload.dedupeKey === this.lastDedupeKey ||
          this.items.some((item) => item.dedupeKey === payload.dedupeKey)
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
      },

      markAllRead(): void {
        this.items = this.items.map((item) => ({
          ...item,
          isRead: true,
        }));
      },

      clearNotifications(): void {
        this.items = [];
        this.lastDedupeKey = "";
      },
    },
  },
);
