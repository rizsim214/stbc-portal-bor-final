<script setup lang="ts">
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import GuestAppointmentBookingForm from "../components/AppointmentBookingForm/GuestAppointmentBookingForm.vue";
import AppointmentCalendar from "../components/AppointmentCalendar/AppointmentCalendar.vue";
import { useAppointmentBooking } from "../composables/useAppointmentBooking";
import { useAppointmentBookingRealtime } from "../composables/useAppointmentBookingRealtime";
import { toDateInput } from "../utils/schedule";

const {
  appointmentTypes,
  appointmentTypesError,
  availabilityError,
  availableTimeOptions,
  calendarOptions,
  datePlaceholder,
  form,
  isLoadingAvailability,
  isLoadingAppointmentTypes,
  selectedDate,
  submitMessage,
  summaryText,
  formatSelectedDate,
  formatTimeValue,
  setSelectedDate,
  submitAppointment,
} = useAppointmentBooking();

useAppointmentBookingRealtime({
  selectedDate: () => selectedDate.value ? toDateInput(selectedDate.value) : "",
  appointmentType: () => form.appointmentType,
});
</script>

<template>
  <section
    class="min-h-screen bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-brand-lighter)_35%,white),white_28%,color-mix(in_srgb,var(--color-brand-light)_14%,white))]">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <PageHeader title="Guest Appointment Booking"
        subtitle="Review current clinic bookings, then complete the form for a first-time patient appointment request."
        heading-tag="h1" />

      <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(360px,0.9fr)]">
        <AppointmentCalendar :calendar-options="calendarOptions" />

        <GuestAppointmentBookingForm :appointment-types="appointmentTypes"
          :appointment-types-error="appointmentTypesError" :date-placeholder="datePlaceholder"
          :availability-error="availabilityError"
          :format-selected-date="formatSelectedDate" :format-time-value="formatTimeValue" :form="form"
          :is-loading-appointment-types="isLoadingAppointmentTypes" :is-loading-availability="isLoadingAvailability"
          :selected-date="selectedDate"
          :submit-message="submitMessage" :summary-text="summaryText"
          :time-options="availableTimeOptions"
          @update:selected-date="setSelectedDate" @submit="submitAppointment" />
      </div>
    </div>
  </section>
</template>
