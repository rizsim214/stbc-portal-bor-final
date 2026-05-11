<script setup lang="ts">
import Calendar from '@/shared/components/Calendar/Calendar.vue';
</script>

<template>
  <main class="mx-auto w-full max-w-6xl px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-[0.9fr_1.6fr]">
      <section class="rounded-lg border border-brand-light/30 bg-brand-lighter/20 p-4 sm:p-5">
        <h1 class="text-2xl font-bold text-brand-darker">Set Up an Appointment</h1>
        <p class="mt-2 text-sm text-brand-dark">
          Pick an available time slot, review your details, and confirm your booking.
        </p>
        <ol class="mt-3 list-decimal space-y-1 pl-5 text-sm text-brand-dark">
          <li>Use month, week, or day view to find your preferred schedule.</li>
          <li>Select a date/time range directly on the calendar.</li>
          <li>Check service type and patient details before final confirmation.</li>
        </ol>
        <p class="mt-3 text-xs text-brand-dark/80">
          Tip: Click an existing appointment to review booked entries.
        </p>
      </section>

      <section class="min-h-0 overflow-x-auto rounded-lg border border-brand-light/20 bg-white p-2 sm:p-3">
        <Calendar />
      </section>
    </div>
  </main>
</template>
