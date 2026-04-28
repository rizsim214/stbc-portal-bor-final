<script setup lang="ts">
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import timeGridPlugin from "@fullcalendar/timegrid";
import type { CalendarOptions, DateSelectArg, EventClickArg } from "@fullcalendar/core";

const calendarOptions: CalendarOptions = {
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: "dayGridMonth",
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
    globalThis.alert(`Selected: ${selectionInfo.startStr} to ${selectionInfo.endStr}`);
  },
  eventClick(clickInfo: EventClickArg) {
    globalThis.alert(`Event: ${clickInfo.event.title}`);
  },
};
</script>

<template>
  <main class="p-6 space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-slate-800">Calendar</h1>
      <RouterLink to="/" class="text-sm text-blue-600 hover:text-blue-700">
        Back to Home
      </RouterLink>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
      <FullCalendar :options="calendarOptions" />
    </div>
  </main>
</template>
