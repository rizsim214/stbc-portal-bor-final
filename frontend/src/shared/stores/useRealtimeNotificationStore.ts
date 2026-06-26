import { defineStore } from "pinia";

type NotificationTone = "success" | "error";

type NotificationPayload = {
  message: string;
  tone?: NotificationTone;
  dedupeKey?: string;
};

export const useRealtimeNotificationStore = defineStore("realtime-notification", {
  state: () => ({
    message: "",
    tone: "success" as NotificationTone,
    lastDedupeKey: "",
  }),

  actions: {
    showNotification(payload: NotificationPayload): void {
      if (payload.dedupeKey && payload.dedupeKey === this.lastDedupeKey) {
        return;
      }

      this.message = payload.message;
      this.tone = payload.tone ?? "success";
      this.lastDedupeKey = payload.dedupeKey ?? "";
    },

    dismiss(): void {
      this.message = "";
      this.tone = "success";
    },
  },
});
