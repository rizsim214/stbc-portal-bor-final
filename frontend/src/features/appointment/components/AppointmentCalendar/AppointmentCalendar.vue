<script setup lang="ts">
import type { CalendarOptions } from "@fullcalendar/core";
import Calendar from "@/shared/components/Calendar/Calendar.vue";

const props = defineProps<{
  calendarOptions: CalendarOptions;
}>();
</script>

<template>
  <div
    class="overflow-hidden rounded-3xl border border-brand-light/30 bg-white shadow-[0_22px_60px_-36px_rgba(21,5,120,0.4)]">
    <div
      class="flex flex-col gap-4 border-b border-brand-light/25 bg-[linear-gradient(135deg,color-mix(in_srgb,var(--color-brand-lighter)_60%,white),white)] px-5 py-5 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-dark/70">
          Live Sample Schedule
        </p>
        <h2 class="mt-1 text-2xl font-semibold text-brand-darker">
          Booked clinic slots
        </h2>
        <p class="mt-2 max-w-2xl text-sm text-brand-dark/80">
          Only booked schedules are shown here. Any empty day or time slot can be treated as available for appointment
          requests.
        </p>
      </div>

      <div class="flex flex-wrap gap-2 text-xs font-medium">
        <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1 text-red-700 ring-1 ring-red-200">
          <span class="h-2.5 w-2.5 rounded-full bg-red-600" />
          Booked
        </span>
      </div>
    </div>

    <div class="appointment-calendar px-3 py-4 sm:px-5">
      <Calendar :calendar-options="props.calendarOptions" wrapper-class="h-full w-full" />
    </div>
  </div>
</template>

<style scoped>
.appointment-calendar :deep(.fc-event) {
  cursor: default;
  pointer-events: none;
}
</style>
