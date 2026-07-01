<script setup lang="ts">
import { computed } from "vue";
import { ShieldAlert, ToggleLeft, ToggleRight } from "lucide-vue-next";
import { useStaffAvailability } from "@/features/staff/composables/useStaffAvailability";
import { useStaffSchedule } from "@/features/staff/composables/useStaffSchedule";
import StaffScheduleEditorCard from "@/features/staff/components/StaffScheduleEditorCard.vue";
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";
import { Button } from "@/shared/ui/button";

const {
  availability,
  isLoadingAvailability,
  availabilityError,
  updateAvailabilityError,
  isUpdatingAvailability,
  setAvailability,
} = useStaffAvailability(true);

const {
  draftDays,
  isLoadingSchedule,
  scheduleError,
  pageMessage: schedulePageMessage,
  isSavingSchedule,
  setDraftDay,
  saveSchedule,
  clearPageMessage,
} = useStaffSchedule("self", undefined, true);

const staffAvailabilityMessage = computed(() => updateAvailabilityError.value || availabilityError.value);

const staffAvailabilityLabel = computed(() =>
  availability.value?.is_available
    ? "You are currently marked available for assignment."
    : "You are currently marked unavailable for assignment.",
);

async function toggleStaffAvailability(): Promise<void> {
  if (!availability.value) {
    return;
  }

  await setAvailability(!availability.value.is_available);
}

function dismissStaffScheduleBanner(): void {
  clearPageMessage();
}
</script>

<template>
  <section class="space-y-6">
    <div
      class="rounded-[1.75rem] border border-brand-light/25 bg-linear-to-br from-white via-sky-50 to-brand-lighter/30 p-6 shadow-sm">
      <PageHeader title="Staff Dashboard"
        subtitle="Manage your temporary assignment availability while keeping your default working schedule in place."
        heading-tag="h1" />
    </div>

    <div class="rounded-3xl border border-brand-light/20 bg-white p-6 shadow-sm">
      <PageHeader title="Assignment Availability"
        subtitle="Use this when you need to temporarily stop appointment assignments during emergencies, hospital duty, or leave." />

      <StatusBanner v-if="staffAvailabilityMessage" :message="staffAvailabilityMessage" tone="error"
        @dismiss="void 0" />

      <div v-if="isLoadingAvailability" class="mt-4 text-sm text-brand-dark/75">
        Loading your availability status...
      </div>

      <div v-else class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-5">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div class="space-y-2">
            <div class="flex items-center gap-2">
              <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                :class="availability?.is_available ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                {{ availability?.is_available ? "Available" : "Unavailable" }}
              </span>
            </div>
            <p class="text-sm font-medium text-slate-900">{{ staffAvailabilityLabel }}</p>
            <p class="text-sm text-slate-600">
              Your normal working hours still come from your default resource schedule. This toggle is a temporary
              override.
            </p>
          </div>

          <Button type="button" class="w-auto bg-brand-dark text-white hover:bg-brand-darker"
            :loading="isUpdatingAvailability" @click="toggleStaffAvailability">
            <ToggleRight v-if="availability?.is_available" class="mr-2 h-4 w-4" />
            <ToggleLeft v-else class="mr-2 h-4 w-4" />
            {{ availability?.is_available ? "Mark unavailable" : "Mark available" }}
          </Button>
        </div>

        <div class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-4 text-sm text-slate-600">
          <div class="flex items-start gap-3">
            <ShieldAlert class="mt-0.5 h-4 w-4 shrink-0 text-brand-dark" />
            <p>
              This is intended for short-term changes like emergency duty or external assignments. It does not replace
              your default working-hour schedule.
            </p>
          </div>
        </div>
      </div>
    </div>

    <StaffScheduleEditorCard title="Weekly Working Schedule"
      subtitle="Adjust the default hours when appointment assignments can be matched to your account." :days="draftDays"
      :is-loading="isLoadingSchedule" :is-saving="isSavingSchedule" :error="scheduleError"
      :message="schedulePageMessage" @dismiss-message="dismissStaffScheduleBanner" @save="saveSchedule"
      @update-day="setDraftDay" />
  </section>
</template>
