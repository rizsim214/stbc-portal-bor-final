<script setup lang="ts">
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import AppointmentCalendar from "../../components/AppointmentCalendar/AppointmentCalendar.vue";
import UserAppointmentBookingForm from "../../components/AppointmentBookingForm/UserAppointmentBookingForm.vue";
import { useAppointmentBooking } from "../../composables/useAppointmentBooking";
import { useAppointmentBookingRealtime } from "../../composables/useAppointmentBookingRealtime";
import { toDateInput } from "../../utils/schedule";

const authStore = useAuthStore();
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
  <section class="rounded-xl border border-brand-light/30 bg-white p-6">
    <PageHeader
      title="Request Appointment"
      subtitle="Create a new appointment request using your signed-in patient account."
      heading-tag="h1"
    />

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(360px,0.9fr)]">
      <AppointmentCalendar :calendar-options="calendarOptions" />

      <UserAppointmentBookingForm
        :appointment-types="appointmentTypes"
        :appointment-types-error="appointmentTypesError"
        :availability-error="availabilityError"
        :date-placeholder="datePlaceholder"
        :format-selected-date="formatSelectedDate"
        :format-time-value="formatTimeValue"
        :form="form"
        :is-loading-availability="isLoadingAvailability"
        :is-loading-appointment-types="isLoadingAppointmentTypes"
        :selected-date="selectedDate"
        :signed-in-email="authStore.user?.email ?? ''"
        :signed-in-name="authStore.user?.name ?? 'Signed-in patient'"
        :submit-message="submitMessage"
        :time-options="availableTimeOptions"
        @update:selected-date="setSelectedDate"
        @submit="submitAppointment"
      />
    </div>
  </section>
</template>
