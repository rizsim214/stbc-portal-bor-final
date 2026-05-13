<script setup lang="ts">
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import timeGridPlugin from "@fullcalendar/timegrid";
import { ref } from "vue";
import { useRouter } from "vue-router";
import type { CalendarOptions, DateSelectArg, EventClickArg } from "@fullcalendar/core";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";

const router = useRouter();
const authStore = useAuthStore();
const guestNotice = ref("");

function redirectToLogin(): void {
  router.push({
    path: "/login",
    query: { redirect: "/appointments" },
  });
}

const calendarOptions: CalendarOptions = {
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: "dayGridMonth",
  height: "auto",
  contentHeight: 520,
  aspectRatio: 1.45,
  headerToolbar: {
    left: "prev,next today",
    center: "title",
    right: "dayGridMonth,timeGridWeek,timeGridDay",
  },
  selectable: true,
  editable: false,
  eventDisplay: "block",
  events: [
    {
      id: "appointment-1",
      title: "Follow-up Consultation",
      start: "2026-04-27T09:30:00",
      end: "2026-04-27T10:00:00",
    },
    {
      id: "appointment-2",
      title: "Lab Result Review",
      start: "2026-04-28T14:00:00",
      end: "2026-04-28T14:30:00",
    },
  ],
  select(selectionInfo: DateSelectArg) {
    if (!authStore.isAuthenticated) {
      guestNotice.value =
        "You can browse schedules as a guest. Please log in to confirm a booking.";
      return;
    }

    guestNotice.value = "";
    globalThis.alert(`Selected: ${selectionInfo.startStr} to ${selectionInfo.endStr}`);
  },
  eventClick(clickInfo: EventClickArg) {
    globalThis.alert(`Event: ${clickInfo.event.title}`);
  },
};
</script>

<template>
  <main class="p-3 sm:p-4">
    <div class="stbc-calendar mx-auto w-full max-w-4xl rounded-lg border border-brand-light/30 bg-white p-2 shadow-sm sm:p-3">
      <FullCalendar :options="calendarOptions" />
      <div v-if="guestNotice" class="mt-3 rounded-md border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900">
        <p>{{ guestNotice }}</p>
        <button type="button" class="mt-2 font-semibold underline" @click="redirectToLogin">
          Log in to continue booking
        </button>
      </div>
    </div>
  </main>
</template>

<style scoped>
.stbc-calendar {
  --fc-border-color: color-mix(in srgb, var(--color-brand-light) 35%, white);
  --fc-page-bg-color: #ffffff;
  --fc-neutral-bg-color: color-mix(in srgb, var(--color-brand-lighter) 28%, white);
  --fc-button-text-color: #ffffff;
  --fc-button-bg-color: var(--color-brand-highlight);
  --fc-button-border-color: var(--color-brand-highlight);
  --fc-button-hover-bg-color: var(--color-brand-dark);
  --fc-button-hover-border-color: var(--color-brand-dark);
  --fc-button-active-bg-color: var(--color-brand-darker);
  --fc-button-active-border-color: var(--color-brand-darker);
  --fc-event-bg-color: var(--color-brand-highlight);
  --fc-event-border-color: var(--color-brand-dark);
  --fc-event-text-color: #ffffff;
  --fc-today-bg-color: color-mix(in srgb, var(--color-brand-lighter) 40%, white);
}

.stbc-calendar :deep(.fc) {
  color: var(--color-brand-dark);
}

.stbc-calendar :deep(.fc-toolbar-title) {
  color: var(--color-brand-darker);
  font-weight: 700;
}

.stbc-calendar :deep(.fc-col-header-cell) {
  background: color-mix(in srgb, var(--color-brand-lighter) 45%, white);
}

.stbc-calendar :deep(.fc-col-header-cell-cushion) {
  color: var(--color-brand-darker);
  font-weight: 600;
}

.stbc-calendar :deep(.fc-daygrid-day-number),
.stbc-calendar :deep(.fc-timegrid-axis-cushion),
.stbc-calendar :deep(.fc-timegrid-slot-label-cushion) {
  color: var(--color-brand-dark);
}

.stbc-calendar :deep(.fc-highlight) {
  background: color-mix(in srgb, var(--color-brand-light) 30%, white);
}

.stbc-calendar :deep(.fc-button) {
  box-shadow: none;
}

@media (max-width: 640px) {
  .stbc-calendar :deep(.fc-toolbar) {
    gap: 0.5rem;
  }

  .stbc-calendar :deep(.fc-toolbar.fc-header-toolbar) {
    margin-bottom: 0.75rem;
  }

  .stbc-calendar :deep(.fc-toolbar-title) {
    font-size: 1rem;
  }

  .stbc-calendar :deep(.fc .fc-toolbar.fc-header-toolbar) {
    flex-direction: column;
    align-items: stretch;
  }

  .stbc-calendar :deep(.fc-toolbar-chunk) {
    display: flex;
    justify-content: center;
  }

  .stbc-calendar :deep(.fc .fc-button) {
    padding: 0.25rem 0.45rem;
    font-size: 0.72rem;
  }
}
</style>
