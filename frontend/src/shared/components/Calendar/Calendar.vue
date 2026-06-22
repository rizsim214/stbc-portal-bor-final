<script setup lang="ts">
import FullCalendar from "@fullcalendar/vue3";
import type { CalendarApi, CalendarOptions } from "@fullcalendar/core";
import { onBeforeUnmount, onMounted, ref } from "vue";

const emit = defineEmits<{
  ready: [value: CalendarApi | null];
}>();

const props = withDefaults(defineProps<{
  calendarKey?: number;
  calendarOptions: CalendarOptions;
  wrapperClass?: string;
}>(), {
  calendarKey: 0,
  wrapperClass: "",
});

const calendarRef = ref<InstanceType<typeof FullCalendar> | null>(null);

onMounted(() => {
  emit("ready", calendarRef.value?.getApi() ?? null);
});

onBeforeUnmount(() => {
  emit("ready", null);
});
</script>

<template>
  <div :class="['stbc-calendar', props.wrapperClass]">
    <FullCalendar :key="props.calendarKey" ref="calendarRef" :options="props.calendarOptions" />
  </div>
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
  --fc-today-bg-color: color-mix(in srgb, var(--color-brand-lighter) 35%, white);
}

.stbc-calendar :deep(.fc) {
  color: var(--color-brand-dark);
}

.stbc-calendar :deep(.fc-toolbar-title) {
  color: var(--color-brand-darker);
  font-size: 1.05rem;
  font-weight: 700;
}

.stbc-calendar :deep(.fc-col-header-cell) {
  background: color-mix(in srgb, var(--color-brand-lighter) 42%, white);
}

.stbc-calendar :deep(.fc-col-header-cell-cushion),
.stbc-calendar :deep(.fc-timegrid-axis-cushion),
.stbc-calendar :deep(.fc-timegrid-slot-label-cushion),
.stbc-calendar :deep(.fc-daygrid-day-number) {
  color: var(--color-brand-dark);
}

.stbc-calendar :deep(.fc-event) {
  border-radius: 0.65rem;
  font-size: 0.78rem;
  font-weight: 600;
  padding: 0.1rem 0.2rem;
}

.stbc-calendar :deep(.fc-button) {
  box-shadow: none;
}

@media (max-width: 1024px) {
  .stbc-calendar :deep(.fc .fc-toolbar.fc-header-toolbar) {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
  }

  .stbc-calendar :deep(.fc-toolbar-chunk) {
    display: flex;
    justify-content: center;
  }
}
</style>
