<script setup lang="ts">
import type { CalendarDate, DateValue } from "@internationalized/date";
import {
  Calendar,
  Check,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  Clock3,
  Mail,
  ShieldCheck,
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
import { Input } from "@/shared/ui/input";
import type { AppointmentFormState, AppointmentTypeOption } from "../../types";

const props = defineProps<{
  appointmentTypes: AppointmentTypeOption[];
  datePlaceholder: CalendarDate;
  formatSelectedDate: (value?: CalendarDate) => string;
  formatTimeValue: (value: string) => string;
  form: AppointmentFormState;
  selectedDate: CalendarDate | undefined;
  submitMessage: string;
  summaryText: string;
  timeOptions: string[];
}>();

const emit = defineEmits<{
  submit: [];
  "update:selectedDate": [value: CalendarDate | undefined];
}>();

function onSelectedDateChange(value: DateValue | undefined): void {
  emit("update:selectedDate", value as CalendarDate | undefined);
}
</script>

<template>
  <aside class="rounded-3xl border border-brand-light/30 bg-white p-5 shadow-[0_22px_60px_-36px_rgba(21,5,120,0.4)]">
    <div class="border-b border-brand-light/25 pb-4">
      <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-dark/70">
        First-Time Patient Form
      </p>
      <h2 class="mt-1 text-2xl font-semibold text-brand-darker">
        Scheduled Appointment Request
      </h2>
      <p class="mt-2 text-sm text-brand-dark/80">
        New patients may request an appointment using their email address. Upon submission, an account may be created
        automatically to support future bookings and profile access.
      </p>
    </div>

    <form class="mt-5 space-y-4" @submit.prevent="emit('submit')">
      <Input v-model="props.form.fullName" label="Full name" placeholder="Juan Dela Cruz" />

      <Input v-model="props.form.email" label="Email address" type="email" placeholder="juan@example.com" />

      <div class="space-y-1">
        <label for="select-appointment" class="text-sm font-medium text-brand-darker">
          Appointment type
        </label>
        <SelectRoot v-model="props.form.appointmentType" id="select-appointment">
          <SelectTrigger
            class="inline-flex h-10 w-full items-center justify-between rounded-md border border-brand-light/50 bg-white px-3 py-2 text-sm text-brand-darker outline-none transition focus:border-brand-highlight focus:ring-2 focus:ring-brand-highlight/30"
            aria-label="Appointment type">
            <SelectValue placeholder="Choose an appointment type" />
            <SelectIcon>
              <ChevronDown class="h-4 w-4 text-brand-dark/70" />
            </SelectIcon>
          </SelectTrigger>

          <SelectPortal>
            <SelectContent
              class="z-80 min-w-(--radix-select-trigger-width) overflow-hidden rounded-xl border border-brand-light/30 bg-white shadow-xl"
              position="popper" :side-offset="8">
              <SelectViewport class="p-1">
                <SelectItem v-for="option in props.appointmentTypes" :key="option.value" :value="option.value"
                  class="relative flex cursor-pointer select-none items-center gap-2 rounded-lg px-3 py-2 text-sm text-brand-darker outline-none data-highlighted:bg-brand-lighter/35 data-[state=checked]:bg-brand-lighter/45">
                  <component :is="option.icon" class="h-4 w-4 text-brand-dark/75" />
                  <SelectItemText>{{ option.label }}</SelectItemText>
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
        <label for="appointment-date-picker" class="text-sm font-medium text-brand-darker">
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
                <span>{{ props.selectedDate ? props.formatSelectedDate(props.selectedDate) : "Choose a date" }}</span>
              </span>
              <ChevronDown class="h-4 w-4 text-brand-dark/70" />
            </DatePickerTrigger>
          </DatePickerAnchor>

          <DatePickerContent class="z-80` rounded-2xl border border-brand-light/30 bg-white p-3 shadow-xl"
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
        <label for="select-preferred-time" class="text-sm font-medium text-brand-darker">
          Preferred time
        </label>
        <SelectRoot v-model="props.form.time" id="select-preferred-time">
          <SelectTrigger
            class="inline-flex h-10 w-full items-center justify-between rounded-md border border-brand-light/50 bg-white px-3 py-2 text-sm text-brand-darker outline-none transition focus:border-brand-highlight focus:ring-2 focus:ring-brand-highlight/30"
            aria-label="Preferred time">
            <span class="inline-flex items-center gap-2">
              <Clock3 class="h-4 w-4 text-brand-dark/70" />
              <SelectValue placeholder="Choose a time slot" />
            </span>
            <SelectIcon>
              <ChevronDown class="h-4 w-4 text-brand-dark/70" />
            </SelectIcon>
          </SelectTrigger>

          <SelectPortal>
            <SelectContent
              class="z-80 min-w-(--radix-select-trigger-width) overflow-hidden rounded-xl border border-brand-light/30 bg-white shadow-xl"
              position="popper" :side-offset="8">
              <SelectViewport class="max-h-64 p-1">
                <SelectItem v-for="time in props.timeOptions" :key="time" :value="time"
                  class="relative flex cursor-pointer select-none items-center rounded-lg px-3 py-2 text-sm text-brand-darker outline-none data-highlighted:bg-brand-lighter/35 data-[state=checked]:bg-brand-lighter/45">
                  <SelectItemText>{{ props.formatTimeValue(time) }}</SelectItemText>
                  <SelectItemIndicator class="ml-auto">
                    <Check class="h-4 w-4 text-brand-highlight" />
                  </SelectItemIndicator>
                </SelectItem>
              </SelectViewport>
            </SelectContent>
          </SelectPortal>
        </SelectRoot>
      </div>

      <div class="rounded-2xl border border-brand-light/25 bg-brand-lighter/10 p-4">
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-dark/70">
          Account Creation Flow
        </p>
        <p class="mt-2 text-sm text-brand-dark/80">
          First-time patients can book using their email. After submission, your backend flow can create a patient
          account and let them reuse it for future bookings.
        </p>
        <div class="mt-3 flex flex-wrap gap-2 text-xs font-medium text-brand-darker">
          <span class="rounded-full bg-white px-3 py-1 ring-1 ring-brand-light/30">
            <Mail class="mr-1 inline h-3.5 w-3.5" />
            Email-based account creation
          </span>
          <span class="rounded-full bg-white px-3 py-1 ring-1 ring-brand-light/30">
            <ShieldCheck class="mr-1 inline h-3.5 w-3.5" />
            Auto-generated password
          </span>
        </div>
      </div>

      <div class="space-y-3">
        <Button type="submit" class="h-11 w-full bg-brand-dark text-white hover:bg-brand-darker">
          Submit Appointment Request
        </Button>
        <p v-if="props.submitMessage" class="text-sm text-emerald-700">
          {{ props.submitMessage }}
        </p>
      </div>
    </form>
  </aside>
</template>
