<script setup lang="ts">
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import timeGridPlugin from "@fullcalendar/timegrid";
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import type {
  CalendarOptions,
  DateSelectArg,
  EventClickArg,
  EventInput,
} from "@fullcalendar/core";
import type { DateClickArg } from "@fullcalendar/interaction";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";

const router = useRouter();
const authStore = useAuthStore();
const isLoginModalOpen = ref(false);
const modalMessage = ref("");

function openLoginModal(message: string): void {
  modalMessage.value = message;
  isLoginModalOpen.value = true;
}

function closeLoginModal(): void {
  isLoginModalOpen.value = false;
}

function redirectToLogin(): void {
  closeLoginModal();
  router.push({
    path: "/login",
    query: { redirect: "/appointments" },
  });
}

const guestSchedules: EventInput[] = [
  { id: "g-1", title: "Booked", start: "2026-05-27T09:00:00", end: "2026-05-27T09:30:00" },
  { id: "g-2", title: "Booked", start: "2026-05-27T11:30:00", end: "2026-05-27T12:00:00" },
  { id: "g-3", title: "Booked", start: "2026-05-28T14:00:00", end: "2026-05-28T14:30:00" },
  { id: "g-4", title: "Booked", start: "2026-05-29T08:30:00", end: "2026-05-29T09:00:00" },
  { id: "g-5", title: "Booked", start: "2026-06-01T10:00:00", end: "2026-06-01T10:30:00" },
  { id: "g-6", title: "Booked", start: "2026-06-02T15:30:00", end: "2026-06-02T16:00:00" },
];

const memberSchedules: EventInput[] = [
  { id: "m-1", title: "Follow-up Consultation", start: "2026-05-27T09:00:00", end: "2026-05-27T09:30:00" },
  { id: "m-2", title: "CBC Result Review", start: "2026-05-27T11:30:00", end: "2026-05-27T12:00:00" },
  { id: "m-3", title: "Platelet Monitoring", start: "2026-05-28T14:00:00", end: "2026-05-28T14:30:00" },
  { id: "m-4", title: "Post-Treatment Check", start: "2026-05-29T08:30:00", end: "2026-05-29T09:00:00" },
  { id: "m-5", title: "Iron Panel Follow-up", start: "2026-06-01T10:00:00", end: "2026-06-01T10:30:00" },
  { id: "m-6", title: "Consultation - Dr. Reyes", start: "2026-06-02T15:30:00", end: "2026-06-02T16:00:00" },
];

const calendarOptions = computed<CalendarOptions>(() => ({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: "dayGridMonth",
  height: "100%",
  contentHeight: "auto",
  expandRows: true,
  headerToolbar: {
    left: "prev,next today",
    center: "title",
    right: "dayGridMonth,timeGridWeek,timeGridDay",
  },
  selectable: true,
  editable: false,
  eventDisplay: "block",
  events: authStore.isAuthenticated ? memberSchedules : guestSchedules,
  dateClick(clickInfo: DateClickArg) {
    if (!authStore.isAuthenticated) {
      openLoginModal(`Please log in to book a schedule on ${clickInfo.dateStr}.`);
      return;
    }
  },
  select(selectionInfo: DateSelectArg) {
    if (!authStore.isAuthenticated) {
      openLoginModal(`Please log in to book ${selectionInfo.startStr} to ${selectionInfo.endStr}.`);
      return;
    }

    globalThis.alert(`Selected: ${selectionInfo.startStr} to ${selectionInfo.endStr}`);
  },
  eventClick(clickInfo: EventClickArg) {
    if (!authStore.isAuthenticated) {
      openLoginModal("Please log in to view schedule details and book an appointment.");
      return;
    }
    globalThis.alert(`Event: ${clickInfo.event.title}`);
  },
}));
</script>

<template>
  <main class="h-full p-1 sm:p-2">
    <div class="stbc-calendar h-full w-full rounded-lg border border-brand-light/30 bg-white p-2 shadow-sm sm:p-3">
      <FullCalendar :options="calendarOptions" />
    </div>

    <div v-if="isLoginModalOpen" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/45 p-4"
      role="dialog" aria-modal="true" aria-labelledby="login-modal-title">
      <div class="w-full max-w-md rounded-lg bg-white p-5 shadow-xl">
        <h2 id="login-modal-title" class="text-lg font-semibold text-brand-darker">
          Login Required
        </h2>
        <p class="mt-2 text-sm text-brand-dark">
          {{ modalMessage }}
        </p>
        <p class="mt-1 text-sm text-brand-dark">
          Log in to continue booking your appointment.
        </p>
        <div class="mt-4 flex justify-end gap-2">
          <button type="button"
            class="rounded-md border border-brand-light px-3 py-2 text-sm text-brand-dark transition hover:bg-brand-lighter/30"
            @click="closeLoginModal">
            Cancel
          </button>
          <button type="button"
            class="rounded-md bg-brand-highlight px-3 py-2 text-sm font-semibold text-white transition hover:bg-brand-dark"
            @click="redirectToLogin">
            Log in
          </button>
        </div>
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
  height: 100%;
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

.stbc-calendar :deep(.fc-dayGridMonth-view .fc-scrollgrid-sync-table),
.stbc-calendar :deep(.fc-dayGridMonth-view .fc-daygrid-body table) {
  table-layout: fixed;
  width: 100%;
}

.stbc-calendar :deep(.fc-dayGridMonth-view .fc-daygrid-day) {
  width: calc(100% / 7);
}

.stbc-calendar :deep(.fc-dayGridMonth-view .fc-daygrid-day-frame) {
  height: 8rem;
  min-height: 8rem;
}

@media (max-width: 640px) {
  .stbc-calendar :deep(.fc-dayGridMonth-view .fc-daygrid-day-frame) {
    height: 6.5rem;
    min-height: 6.5rem;
  }

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
