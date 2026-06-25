<script setup lang="ts">
import type { CalendarDate, DateValue } from "@internationalized/date";
import {
  Calendar,
  Check,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  Clock3,
} from "lucide-vue-next";
import {
  DatePickerAnchor,
  DatePickerArrow,
  DatePickerCalendar,
  DatePickerCell,
  DatePickerCellTrigger,
  DatePickerContent,
  DatePickerGrid,
  DatePickerGridBody,
  DatePickerGridHead,
  DatePickerGridRow,
  DatePickerHeadCell,
  DatePickerHeader,
  DatePickerHeading,
  DatePickerNext,
  DatePickerPrev,
  DatePickerRoot,
  DatePickerTrigger,
  SelectContent,
  SelectIcon,
  SelectItem,
  SelectItemIndicator,
  SelectItemText,
  SelectPortal,
  SelectRoot,
  SelectTrigger,
  SelectValue,
  SelectViewport,
} from "radix-vue";
import { Button } from "@/shared/ui/button";
import type { AppointmentEditFormState, AppointmentTypeOption } from "../types";
import { formatSelectedDate, formatTimeValue } from "../utils/schedule";

const props = defineProps<{
  appointmentTypes: AppointmentTypeOption[];
  availableTimeOptions: string[];
  datePlaceholder: CalendarDate;
  form: AppointmentEditFormState;
  isLoadingAvailability: boolean;
  isLoadingAppointmentTypes: boolean;
  isSubmitting: boolean;
  selectedDate: CalendarDate | undefined;
}>();

const emit = defineEmits<{
  save: [];
  "update:selectedDate": [value: CalendarDate | undefined];
}>();

function onSelectedDateChange(value: DateValue | undefined): void {
  emit("update:selectedDate", value as CalendarDate | undefined);
}
</script>

<template>
  <section class="rounded-2xl border border-brand-light/25 bg-white p-5 shadow-sm">
    <h2 class="text-lg font-semibold text-brand-darker">Modify Appointment</h2>
    <p class="mt-2 text-sm text-brand-dark/80">
      Update the appointment type, schedule, and description. Only available time slots are shown.
    </p>

    <div class="mt-4 space-y-4">
      <div class="space-y-1">
        <label for="edit-appointment-type" class="text-sm font-medium text-brand-darker">
          Appointment type
        </label>
        <SelectRoot v-model="props.form.appointmentType" id="edit-appointment-type">
          <SelectTrigger
            class="inline-flex h-10 w-full items-center justify-between rounded-md border border-brand-light/50 bg-white px-3 py-2 text-sm text-brand-darker outline-none transition focus:border-brand-highlight focus:ring-2 focus:ring-brand-highlight/30"
            :disabled="props.isLoadingAppointmentTypes || !props.appointmentTypes.length">
            <SelectValue
              :placeholder="props.isLoadingAppointmentTypes ? 'Loading appointment types...' : 'Select appointment type'" />
            <SelectIcon>
              <ChevronDown class="h-4 w-4 text-brand-dark/70" />
            </SelectIcon>
          </SelectTrigger>

          <SelectPortal>
            <SelectContent
              class="z-80 min-w-(--radix-select-trigger-width) overflow-hidden rounded-xl border border-brand-light/30 bg-white shadow-xl"
              position="popper" :side-offset="8">
              <SelectViewport class="max-h-72 overflow-y-auto p-1">
                <SelectItem v-for="option in props.appointmentTypes" :key="option.value" :value="option.value"
                  class="relative flex cursor-pointer select-none items-center rounded-lg px-3 py-2 text-sm text-brand-darker outline-none data-highlighted:bg-brand-lighter/35 data-[state=checked]:bg-brand-lighter/45">
                  <div class="flex min-w-0 flex-col">
                    <SelectItemText>{{ option.label }}</SelectItemText>
                    <span v-if="option.description" class="truncate text-xs text-brand-dark/65">
                      {{ option.description }}
                    </span>
                  </div>
                  <SelectItemIndicator class="ml-auto">
                    <Check class="h-4 w-4 text-brand-highlight" />
                  </SelectItemIndicator>
                </SelectItem>
              </SelectViewport>
            </SelectContent>
          </SelectPortal>
        </SelectRoot>
      </div>

      <div class="space-y-1">
        <label for="edit-appointment-date" class="text-sm font-medium text-brand-darker">
          Appointment date
        </label>
        <DatePickerRoot :model-value="props.selectedDate" :placeholder="props.datePlaceholder" :weekday-format="'short'"
          :fixed-weeks="true" :min-value="props.datePlaceholder" @update:model-value="onSelectedDateChange">
          <DatePickerAnchor class="w-full">
            <DatePickerTrigger
              class="inline-flex h-10 w-full items-center justify-between rounded-md border border-brand-light/50 bg-white px-3 py-2 text-sm text-brand-darker transition hover:bg-brand-lighter/25 focus:outline-none focus:ring-2 focus:ring-brand-highlight/30">
              <span :class="props.selectedDate ? 'text-brand-darker' : 'text-brand-dark/60'"
                class="inline-flex items-center gap-2">
                <Calendar class="h-4 w-4" />
                <span>{{ props.selectedDate ? formatSelectedDate(props.selectedDate) : "Choose a date" }}</span>
              </span>
              <ChevronDown class="h-4 w-4 text-brand-dark/70" />
            </DatePickerTrigger>
          </DatePickerAnchor>

          <DatePickerContent class="z-80 rounded-2xl border border-brand-light/30 bg-white p-3 shadow-xl"
            :side-offset="8">
            <DatePickerArrow class="fill-white" />
            <DatePickerCalendar v-slot="{ weekDays, grid }" class="space-y-3">
              <DatePickerHeader class="flex items-center justify-between gap-2">
                <DatePickerPrev
                  class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-brand-light/40 text-brand-dark transition hover:bg-brand-lighter/25">
                  <ChevronLeft class="h-4 w-4" />
                </DatePickerPrev>
                <DatePickerHeading class="text-sm font-semibold text-brand-darker" />
                <DatePickerNext
                  class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-brand-light/40 text-brand-dark transition hover:bg-brand-lighter/25">
                  <ChevronRight class="h-4 w-4" />
                </DatePickerNext>
              </DatePickerHeader>

              <div class="flex flex-col gap-4">
                <DatePickerGrid v-for="month in grid" :key="month.value.toString()"
                  class="w-full border-collapse select-none space-y-1">
                  <DatePickerGridHead>
                    <DatePickerGridRow class="grid grid-cols-7">
                      <DatePickerHeadCell v-for="day in weekDays" :key="day"
                        class="text-center text-xs font-medium text-brand-dark/65">
                        {{ day }}
                      </DatePickerHeadCell>
                    </DatePickerGridRow>
                  </DatePickerGridHead>
                  <DatePickerGridBody class="space-y-1">
                    <DatePickerGridRow v-for="(weekDates, index) in month.rows" :key="`week-${index}`"
                      class="grid grid-cols-7 gap-1">
                      <DatePickerCell v-for="weekDate in weekDates" :key="weekDate.toString()" :date="weekDate">
                        <DatePickerCellTrigger :day="weekDate" :month="month.value"
                          class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-sm text-brand-darker outline-none transition hover:bg-brand-lighter/35 data-selected:bg-brand-dark data-selected:text-white data-today:ring-1 data-today:ring-brand-highlight/45 data-outside-view:text-brand-dark/35 data-disabled:pointer-events-none data-disabled:opacity-40">
                          {{ weekDate.day }}
                        </DatePickerCellTrigger>
                      </DatePickerCell>
                    </DatePickerGridRow>
                  </DatePickerGridBody>
                </DatePickerGrid>
              </div>
            </DatePickerCalendar>
          </DatePickerContent>
        </DatePickerRoot>
      </div>

      <div class="space-y-1">
        <label for="edit-appointment-time" class="text-sm font-medium text-brand-darker">
          Available time
        </label>
        <SelectRoot v-model="props.form.time" id="edit-appointment-time">
          <SelectTrigger
            class="inline-flex h-10 w-full items-center justify-between rounded-md border border-brand-light/50 bg-white px-3 py-2 text-sm text-brand-darker outline-none transition focus:border-brand-highlight focus:ring-2 focus:ring-brand-highlight/30"
            :disabled="props.isLoadingAvailability || !props.availableTimeOptions.length">
            <span class="inline-flex items-center gap-2">
              <Clock3 class="h-4 w-4 text-brand-dark/70" />
              <SelectValue
                :placeholder="props.isLoadingAvailability ? 'Loading available slots...' : 'Select available time'" />
            </span>
            <SelectIcon>
              <ChevronDown class="h-4 w-4 text-brand-dark/70" />
            </SelectIcon>
          </SelectTrigger>

          <SelectPortal>
            <SelectContent
              class="z-80 min-w-(--radix-select-trigger-width) overflow-hidden rounded-xl border border-brand-light/30 bg-white shadow-xl"
              position="popper" :side-offset="8">
              <SelectViewport class="max-h-64 overflow-y-auto p-1">
                <SelectItem v-for="time in props.availableTimeOptions" :key="time" :value="time"
                  class="relative flex cursor-pointer select-none items-center rounded-lg px-3 py-2 text-sm text-brand-darker outline-none data-highlighted:bg-brand-lighter/35 data-[state=checked]:bg-brand-lighter/45">
                  <SelectItemText>{{ formatTimeValue(time) }}</SelectItemText>
                  <SelectItemIndicator class="ml-auto">
                    <Check class="h-4 w-4 text-brand-highlight" />
                  </SelectItemIndicator>
                </SelectItem>
              </SelectViewport>
            </SelectContent>
          </SelectPortal>
        </SelectRoot>
        <p v-if="!props.isLoadingAvailability && !props.availableTimeOptions.length" class="text-xs text-amber-700">
          No available slots for the selected date.
        </p>
      </div>

      <div class="space-y-1">
        <label for="edit-appointment-notes" class="text-sm font-medium text-brand-darker">
          Appointment description
        </label>
        <textarea id="edit-appointment-notes" v-model="props.form.notes" rows="4"
          placeholder="Describe the test, checkup, symptoms, or clinic service you need."
          class="flex min-h-28 w-full rounded-md border border-brand-light/50 bg-white px-3 py-2 text-sm text-brand-darker transition placeholder:text-brand-dark/60 focus:border-brand-highlight focus:outline-none focus:ring-2 focus:ring-brand-highlight/40" />
      </div>

      <Button type="button" class="w-full bg-brand-dark text-white hover:bg-brand-darker" :loading="props.isSubmitting"
        @click="emit('save')">
        Save Appointment Changes
      </Button>
    </div>
  </section>
</template>
