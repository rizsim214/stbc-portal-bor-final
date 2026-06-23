<script setup lang="ts">
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import AppointmentBookingForm from "../components/AppointmentBookingForm/AppointmentBookingForm.vue";
import AppointmentCalendar from "../components/AppointmentCalendar/AppointmentCalendar.vue";
import { useAppointmentBooking } from "../composables/useAppointmentBooking";

const {
  appointmentTypes,
  appointmentTypesError,
  calendarOptions,
  datePlaceholder,
  form,
  isLoadingAppointmentTypes,
  selectedDate,
  submitMessage,
  summaryText,
  timeOptions,
  formatSelectedDate,
  formatTimeValue,
  setSelectedDate,
  submitAppointment,
} = useAppointmentBooking();
</script>

<template>
  <section
    class="min-h-screen bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-brand-lighter)_35%,white),white_28%,color-mix(in_srgb,var(--color-brand-light)_14%,white))]">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <PageHeader title="Appointment Booking"
        subtitle="Review sample clinic availability, then complete the booking form for a first-time patient request."
        heading-tag="h1" />

      <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(360px,0.9fr)]">
        <AppointmentCalendar :calendar-options="calendarOptions" />

        <AppointmentBookingForm :appointment-types="appointmentTypes" :appointment-types-error="appointmentTypesError"
          :date-placeholder="datePlaceholder" :format-selected-date="formatSelectedDate"
          :format-time-value="formatTimeValue" :form="form" :is-loading-appointment-types="isLoadingAppointmentTypes"
          :selected-date="selectedDate" :submit-message="submitMessage" :summary-text="summaryText"
          :time-options="timeOptions" @update:selected-date="setSelectedDate" @submit="submitAppointment" />
      </div>
    </div>
  </section>
</template>
