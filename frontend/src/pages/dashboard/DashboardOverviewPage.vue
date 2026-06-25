<script setup lang="ts">
import { computed } from "vue";
import { RouterLink } from "vue-router";
import { CalendarClock, ChevronRight, Clock3, ShieldAlert, ToggleLeft, ToggleRight, UserPlus, Users } from "lucide-vue-next";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import { useAppointmentRealtime } from "@/features/appointment/composables/useAppointmentRealtime";
import { useAdminDashboardActivity } from "@/features/appointment/composables/useAdminDashboardActivity";
import type { AppointmentListItem } from "@/features/appointment/types";
import { useStaffAvailability } from "@/features/staff/composables/useStaffAvailability";
import { useStaffSchedule } from "@/features/staff/composables/useStaffSchedule";
import StaffScheduleEditorCard from "@/features/staff/components/StaffScheduleEditorCard.vue";
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";
import { Button } from "@/shared/ui/button";

const authStore = useAuthStore();
const isAdmin = computed(() => authStore.user?.role?.name === "admin");
const isStaff = computed(() => authStore.user?.role?.name === "staff");

const {
  activities,
  isLoading,
  dataError,
  clearDataError,
} = useAdminDashboardActivity(8, isAdmin);

const {
  availability,
  isLoadingAvailability,
  availabilityError,
  updateAvailabilityError,
  isUpdatingAvailability,
  setAvailability,
} = useStaffAvailability(isStaff.value);

const {
  draftDays,
  isLoadingSchedule,
  scheduleError,
  pageMessage: schedulePageMessage,
  isSavingSchedule,
  setDraftDay,
  saveSchedule,
  clearPageMessage: clearSchedulePageMessage,
} = useStaffSchedule("self", undefined, isStaff.value);

if (isAdmin.value) {
  useAppointmentRealtime("admin");
}

function parseDate(value?: string): Date | null {
  if (!value) {
    return null;
  }

  const parsed = new Date(value);
  return Number.isNaN(parsed.getTime()) ? null : parsed;
}

function isNewUserBooking(appointment: AppointmentListItem): boolean {
  const appointmentCreatedAt = parseDate(appointment.created_at);
  const userCreatedAt = parseDate(appointment.user?.created_at);

  if (!appointmentCreatedAt || !userCreatedAt) {
    return false;
  }

  return Math.abs(appointmentCreatedAt.getTime() - userCreatedAt.getTime()) <= 5 * 60 * 1000;
}

const newUserBookingsCount = computed(
  () => activities.value.filter((activity) => isNewUserBooking(activity)).length,
);

const existingUserBookingsCount = computed(
  () => activities.value.filter((activity) => !isNewUserBooking(activity)).length,
);

const stats = computed(() => [
  {
    label: "Recent bookings",
    value: activities.value.length,
    note: "Latest user booking interactions",
    icon: CalendarClock,
    accent: "from-sky-500/15 to-sky-100",
  },
  {
    label: "New user bookings",
    value: newUserBookingsCount.value,
    note: "Account created during booking",
    icon: UserPlus,
    accent: "from-emerald-500/15 to-emerald-100",
  },
  {
    label: "Existing user bookings",
    value: existingUserBookingsCount.value,
    note: "Returning patient activity",
    icon: Users,
    accent: "from-amber-500/15 to-amber-100",
  },
]);

function formatDateTime(value?: string, options?: Intl.DateTimeFormatOptions): string {
  const parsed = parseDate(value);

  if (!parsed) {
    return "Unknown time";
  }

  return new Intl.DateTimeFormat("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
    hour: "numeric",
    minute: "2-digit",
    ...options,
  }).format(parsed);
}

function formatStatus(status?: string): string {
  if (!status) {
    return "Unknown";
  }

  return status.replace(/_/g, " ").replace(/\b\w/g, (character) => character.toUpperCase());
}

function getActivityTitle(appointment: AppointmentListItem): string {
  const patientName = appointment.user?.name ?? "Unknown patient";
  const typeName = appointment.type?.name ?? "appointment";

  return isNewUserBooking(appointment)
    ? `${patientName} created an account and booked ${typeName}.`
    : `${patientName} booked ${typeName}.`;
}

function getActivityNote(appointment: AppointmentListItem): string {
  return `Booked ${formatDateTime(appointment.created_at)} for ${formatDateTime(appointment.start_time)}.`;
}

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
  clearSchedulePageMessage();
}
</script>

<template>
  <section v-if="isAdmin" class="space-y-6">
    <div class="rounded-[1.75rem] border border-brand-light/25 bg-linear-to-br from-white via-sky-50 to-brand-lighter/30 p-6 shadow-sm">
      <PageHeader
        title="Admin Dashboard"
        subtitle="Track the latest booking activity from new and existing users."
        heading-tag="h1"
      />
      <StatusBanner v-if="dataError" :message="dataError" tone="error" @dismiss="clearDataError" />

      <div class="mt-6 grid gap-4 md:grid-cols-3">
        <article
          v-for="stat in stats"
          :key="stat.label"
          class="rounded-2xl border border-white/80 bg-white/80 p-5 shadow-sm backdrop-blur"
        >
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-sm font-medium text-brand-dark">{{ stat.label }}</p>
              <p class="mt-3 text-3xl font-semibold text-brand-darker">{{ stat.value }}</p>
              <p class="mt-2 text-xs text-brand-dark/70">{{ stat.note }}</p>
            </div>
            <div :class="['rounded-2xl bg-linear-to-br p-3 text-brand-dark', stat.accent]">
              <component :is="stat.icon" class="h-5 w-5" />
            </div>
          </div>
        </article>
      </div>
    </div>

    <div class="rounded-[1.5rem] border border-brand-light/20 bg-white p-6 shadow-sm">
      <PageHeader
        title="Recent User Activity"
        subtitle="Shows who booked, whether the account was new or existing, and the schedule they chose."
      >
        <template #actions>
          <RouterLink
            to="/dashboard/appointments/admin/list"
            class="inline-flex items-center gap-2 rounded-full border border-brand-light/30 px-4 py-2 text-sm font-medium text-brand-dark transition hover:border-brand-light hover:bg-brand-lighter/30"
          >
            Open all appointments
            <ChevronRight class="h-4 w-4" />
          </RouterLink>
        </template>
      </PageHeader>

      <div v-if="isLoading" class="space-y-3">
        <div
          v-for="placeholder in 4"
          :key="placeholder"
          class="h-24 animate-pulse rounded-2xl bg-slate-100"
        />
      </div>

      <div v-else-if="activities.length === 0" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-sm text-slate-600">
        No appointment activity has been recorded yet.
      </div>

      <div v-else class="space-y-3">
        <article
          v-for="appointment in activities"
          :key="appointment.id"
          class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 transition hover:border-brand-light/35 hover:bg-white"
        >
          <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="space-y-3">
              <div class="flex flex-wrap items-center gap-2">
                <span
                  class="rounded-full px-3 py-1 text-xs font-semibold"
                  :class="isNewUserBooking(appointment)
                    ? 'bg-emerald-100 text-emerald-700'
                    : 'bg-sky-100 text-sky-700'"
                >
                  {{ isNewUserBooking(appointment) ? "New user" : "Existing user" }}
                </span>
                <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-medium text-slate-700">
                  {{ formatStatus(appointment.status) }}
                </span>
              </div>

              <div>
                <p class="text-sm font-semibold text-slate-900">{{ getActivityTitle(appointment) }}</p>
                <p class="mt-1 text-sm text-slate-600">{{ getActivityNote(appointment) }}</p>
              </div>

              <div class="flex flex-wrap gap-4 text-xs text-slate-600">
                <span class="inline-flex items-center gap-2">
                  <Clock3 class="h-4 w-4 text-brand-dark" />
                  Scheduled for {{ formatDateTime(appointment.start_time) }}
                </span>
                <span v-if="appointment.user?.email" class="inline-flex items-center gap-2">
                  <Users class="h-4 w-4 text-brand-dark" />
                  {{ appointment.user.email }}
                </span>
              </div>
            </div>

            <RouterLink
              :to="`/dashboard/appointments/admin/${appointment.id}/details`"
              class="inline-flex items-center gap-2 self-start rounded-full border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-brand-light hover:text-brand-dark"
            >
              View details
              <ChevronRight class="h-4 w-4" />
            </RouterLink>
          </div>
        </article>
      </div>
    </div>

  </section>

  <section v-else-if="isStaff" class="space-y-6">
    <div class="rounded-[1.75rem] border border-brand-light/25 bg-linear-to-br from-white via-sky-50 to-brand-lighter/30 p-6 shadow-sm">
      <PageHeader
        title="Staff Dashboard"
        subtitle="Manage your temporary assignment availability while keeping your default working schedule in place."
        heading-tag="h1"
      />
    </div>

    <div class="rounded-[1.5rem] border border-brand-light/20 bg-white p-6 shadow-sm">
      <PageHeader
        title="Assignment Availability"
        subtitle="Use this when you need to temporarily stop appointment assignments during emergencies, hospital duty, or leave."
      />

      <StatusBanner
        v-if="staffAvailabilityMessage"
        :message="staffAvailabilityMessage"
        tone="error"
        @dismiss="void 0"
      />

      <div v-if="isLoadingAvailability" class="mt-4 text-sm text-brand-dark/75">
        Loading your availability status...
      </div>

      <div v-else class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-5">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div class="space-y-2">
            <div class="flex items-center gap-2">
              <span
                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                :class="availability?.is_available ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
              >
                {{ availability?.is_available ? "Available" : "Unavailable" }}
              </span>
            </div>
            <p class="text-sm font-medium text-slate-900">{{ staffAvailabilityLabel }}</p>
            <p class="text-sm text-slate-600">
              Your normal working hours still come from your default resource schedule. This toggle is a temporary override.
            </p>
          </div>

          <Button
            type="button"
            class="w-auto bg-brand-dark text-white hover:bg-brand-darker"
            :loading="isUpdatingAvailability"
            @click="toggleStaffAvailability"
          >
            <ToggleRight v-if="availability?.is_available" class="mr-2 h-4 w-4" />
            <ToggleLeft v-else class="mr-2 h-4 w-4" />
            {{ availability?.is_available ? "Mark unavailable" : "Mark available" }}
          </Button>
        </div>

        <div class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-4 text-sm text-slate-600">
          <div class="flex items-start gap-3">
            <ShieldAlert class="mt-0.5 h-4 w-4 shrink-0 text-brand-dark" />
            <p>
              This is intended for short-term changes like emergency duty or external assignments. It does not replace your default working-hour schedule.
            </p>
          </div>
        </div>
      </div>
    </div>

    <StaffScheduleEditorCard
      title="Weekly Working Schedule"
      subtitle="Adjust the default hours when appointment assignments can be matched to your account."
      :days="draftDays"
      :is-loading="isLoadingSchedule"
      :is-saving="isSavingSchedule"
      :error="scheduleError"
      :message="schedulePageMessage"
      @dismiss-message="dismissStaffScheduleBanner"
      @save="saveSchedule"
      @update-day="setDraftDay"
    />
  </section>

  <section v-else class="rounded-xl border border-brand-light/30 bg-white p-6">
    <h1 class="text-2xl font-semibold text-brand-darker">Dashboard Overview</h1>
    <p class="mt-2 text-sm text-brand-dark">Welcome to the STBC portal dashboard.</p>
  </section>
</template>
