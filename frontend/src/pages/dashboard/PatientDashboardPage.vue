<script setup lang="ts">
import { useQuery } from "@tanstack/vue-query";
import { computed } from "vue";
import { RouterLink } from "vue-router";
import {
  CalendarCheck2,
  CalendarClock,
  ChevronRight,
  FileText,
  FlaskConical,
  Users,
} from "lucide-vue-next";
import { appointmentsApi } from "@/features/appointment/api/appointmentsApi";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import { useAppointmentRealtime } from "@/features/appointment/composables/useAppointmentRealtime";
import type { AppointmentListItem, LabResultListItem } from "@/features/appointment/types";
import { formatScheduleRange } from "@/features/appointment/utils/schedule";
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";

const authStore = useAuthStore();

useAppointmentRealtime("mine");

const patientAppointmentsQuery = useQuery({
  queryKey: ["dashboard", "patient", "appointments"],
  queryFn: async () => {
    const { data } = await appointmentsApi.listMyAppointments(1, 50);
    return data.data;
  },
});

const patientLabResultsQuery = useQuery({
  queryKey: ["dashboard", "patient", "lab-results"],
  queryFn: async () => {
    const { data } = await appointmentsApi.listMyLabResults(1, 10);
    return data.data;
  },
});

function parseDate(value?: string): Date | null {
  if (!value) {
    return null;
  }

  const parsed = new Date(value);
  return Number.isNaN(parsed.getTime()) ? null : parsed;
}

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

function formatReleasedAt(value?: string | null): string {
  const parsed = parseDate(value ?? undefined);

  if (!parsed) {
    return "Unknown date";
  }

  return new Intl.DateTimeFormat("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  }).format(parsed);
}

function formatStatus(status?: string): string {
  if (!status) {
    return "Unknown";
  }

  return status.replace(/_/g, " ").replace(/\b\w/g, (character) => character.toUpperCase());
}

const patientAppointments = computed<AppointmentListItem[]>(() => patientAppointmentsQuery.data.value ?? []);
const patientLabResults = computed<LabResultListItem[]>(() => patientLabResultsQuery.data.value ?? []);
const isLoadingPatientDashboard = computed(
  () =>
    patientAppointmentsQuery.isPending.value ||
    patientAppointmentsQuery.isFetching.value ||
    patientLabResultsQuery.isPending.value ||
    patientLabResultsQuery.isFetching.value,
);
const patientDashboardError = computed(() => {
  const appointmentError = patientAppointmentsQuery.error.value;
  const labResultError = patientLabResultsQuery.error.value;

  if (appointmentError instanceof Error) {
    return appointmentError.message;
  }

  if (labResultError instanceof Error) {
    return labResultError.message;
  }

  return patientAppointmentsQuery.isError.value || patientLabResultsQuery.isError.value
    ? "Unable to load your recent patient activity."
    : "";
});

const upcomingAppointmentsCount = computed(() => {
  const now = Date.now();

  return patientAppointments.value.filter((appointment) => {
    const startTime = parseDate(appointment.start_time);
    return startTime && startTime.getTime() >= now;
  }).length;
});

const completedAppointmentsCount = computed(() =>
  patientAppointments.value.filter((appointment) => appointment.status === "completed").length,
);

const releasedLabResultsCount = computed(() => patientLabResults.value.length);

const patientStats = computed(() => [
  {
    label: "Upcoming appointments",
    value: upcomingAppointmentsCount.value,
    note: "Future schedules you can review quickly",
    icon: CalendarClock,
    accent: "from-sky-500/15 to-sky-100",
  },
  {
    label: "Completed visits",
    value: completedAppointmentsCount.value,
    note: "Appointments already finished",
    icon: CalendarCheck2,
    accent: "from-emerald-500/15 to-emerald-100",
  },
  {
    label: "Released lab results",
    value: releasedLabResultsCount.value,
    note: "Results ready for viewing or download",
    icon: FlaskConical,
    accent: "from-amber-500/15 to-amber-100",
  },
]);

type PatientActivityItem = {
  id: string;
  title: string;
  note: string;
  timestamp: string | null;
  timestampLabel: string;
  badge: string;
  badgeClass: string;
  route:
  | { name: "MyAppointmentView"; params: { appointmentId: string } }
  | { name: "userLabResultDetail"; params: { labResultId: string } };
};

const patientRecentActivity = computed<PatientActivityItem[]>(() => {
  const appointmentActivities = patientAppointments.value.map((appointment) => ({
    id: `appointment-${appointment.id}`,
    title: `${appointment.type?.name ?? "Appointment"} is ${formatStatus(appointment.status).toLowerCase()}.`,
    note: formatScheduleRange(appointment.start_time, appointment.end_time),
    timestamp: appointment.updated_at ?? appointment.start_time,
    timestampLabel: formatDateTime(appointment.updated_at ?? appointment.start_time),
    badge: formatStatus(appointment.status),
    badgeClass: appointment.status === "completed"
      ? "bg-emerald-100 text-emerald-700"
      : appointment.status === "assigned"
        ? "bg-sky-100 text-sky-700"
        : "bg-amber-100 text-amber-700",
    route: {
      name: "MyAppointmentView" as const,
      params: { appointmentId: String(appointment.id) },
    },
  }));

  const labResultActivities = patientLabResults.value.map((labResult) => ({
    id: `lab-result-${labResult.id}`,
    title: `${labResult.appointment?.type?.name ?? "Lab result"} was released.`,
    note: labResult.appointment
      ? formatScheduleRange(
        labResult.appointment.start_time,
        labResult.appointment.end_time,
      )
      : "Released lab result available for viewing.",
    timestamp: labResult.released_at ?? labResult.updated_at ?? labResult.created_at ?? null,
    timestampLabel: formatDateTime(
      labResult.released_at ?? labResult.updated_at ?? labResult.created_at ?? undefined,
    ),
    badge: "Lab Result",
    badgeClass: "bg-violet-100 text-violet-700",
    route: {
      name: "userLabResultDetail" as const,
      params: { labResultId: String(labResult.id) },
    },
  }));

  return [...labResultActivities, ...appointmentActivities]
    .sort((left, right) => {
      const leftTime = parseDate(left.timestamp ?? undefined)?.getTime() ?? 0;
      const rightTime = parseDate(right.timestamp ?? undefined)?.getTime() ?? 0;
      return rightTime - leftTime;
    })
    .slice(0, 8);
});

function getPatientGreeting(): string {
  const fullName = authStore.user?.name?.trim();
  if (!fullName) {
    return "Patient Dashboard";
  }

  const [firstName] = fullName.split(/\s+/);
  return `${firstName}'s Dashboard`;
}
</script>

<template>
  <section class="space-y-6">
    <div
      class="rounded-[1.75rem] border border-brand-light/25 bg-linear-to-br from-white via-sky-50 to-brand-lighter/30 p-6 shadow-sm">
      <PageHeader :title="getPatientGreeting()"
        subtitle="Check your latest appointment changes, upcoming schedules, and released lab results from one place."
        heading-tag="h1">
        <template #actions>
          <RouterLink to="/dashboard/appointments/request"
            class="inline-flex items-center gap-2 rounded-full bg-brand-dark px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-darker">
            Request appointment
            <ChevronRight class="h-4 w-4" />
          </RouterLink>
        </template>
      </PageHeader>

      <StatusBanner v-if="patientDashboardError" :message="patientDashboardError" tone="error" @dismiss="void 0" />

      <div class="mt-6 grid gap-4 md:grid-cols-3">
        <article v-for="stat in patientStats" :key="stat.label"
          class="rounded-2xl border border-white/80 bg-white/85 p-5 shadow-sm backdrop-blur">
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

    <div class="grid gap-6 xl:grid-cols-[1.35fr_0.95fr]">
      <div class="rounded-3xl border border-brand-light/20 bg-white p-6 shadow-sm">
        <PageHeader title="Recent Activity"
          subtitle="Your latest appointment status updates and newly released lab results.">
          <template #actions>
            <RouterLink :to="{ name: 'MyAppointmentList' }"
              class="inline-flex items-center gap-2 rounded-full border border-brand-light/30 px-4 py-2 text-sm font-medium text-brand-dark transition hover:border-brand-light hover:bg-brand-lighter/30">
              Open appointments
              <ChevronRight class="h-4 w-4" />
            </RouterLink>
          </template>
        </PageHeader>

        <div v-if="isLoadingPatientDashboard" class="space-y-3">
          <div v-for="placeholder in 5" :key="placeholder" class="h-24 animate-pulse rounded-2xl bg-slate-100" />
        </div>

        <div v-else-if="patientRecentActivity.length === 0"
          class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-sm text-slate-600">
          No patient activity yet. Your appointments and released lab results will start appearing here automatically.
        </div>

        <div v-else class="space-y-3">
          <article v-for="activity in patientRecentActivity" :key="activity.id"
            class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 transition hover:border-brand-light/35 hover:bg-white">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
              <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="activity.badgeClass">
                    {{ activity.badge }}
                  </span>
                  <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-medium text-slate-700">
                    {{ activity.timestampLabel }}
                  </span>
                </div>

                <div>
                  <p class="text-sm font-semibold text-slate-900">{{ activity.title }}</p>
                  <p class="mt-1 text-sm text-slate-600">{{ activity.note }}</p>
                </div>
              </div>

              <RouterLink :to="activity.route"
                class="inline-flex items-center gap-2 self-start rounded-full border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-brand-light hover:text-brand-dark">
                View
                <ChevronRight class="h-4 w-4" />
              </RouterLink>
            </div>
          </article>
        </div>
      </div>

      <div class="space-y-6">
        <div class="rounded-3xl border border-brand-light/20 bg-white p-6 shadow-sm">
          <PageHeader title="Quick Access" subtitle="Jump straight to the pages you are most likely to use next." />

          <div class="mt-5 grid gap-3">
            <RouterLink :to="{ name: 'MyAppointmentList' }"
              class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-4 text-left transition hover:border-brand-light/35 hover:bg-white">
              <div class="flex items-center gap-3">
                <div class="rounded-2xl bg-sky-100 p-3 text-sky-700">
                  <CalendarClock class="h-5 w-5" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-slate-900">My Appointments</p>
                  <p class="text-sm text-slate-600">Review all schedules and workflow updates.</p>
                </div>
              </div>
              <ChevronRight class="h-4 w-4 text-slate-500" />
            </RouterLink>

            <RouterLink :to="{ name: 'userMedicalRecord' }"
              class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-4 text-left transition hover:border-brand-light/35 hover:bg-white">
              <div class="flex items-center gap-3">
                <div class="rounded-2xl bg-amber-100 p-3 text-amber-700">
                  <FileText class="h-5 w-5" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-slate-900">My Lab-Results</p>
                  <p class="text-sm text-slate-600">Open released result files and downloads.</p>
                </div>
              </div>
              <ChevronRight class="h-4 w-4 text-slate-500" />
            </RouterLink>

            <RouterLink :to="{ name: 'userDetailView', params: { userId: String(authStore.user?.id ?? '') } }"
              class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-4 text-left transition hover:border-brand-light/35 hover:bg-white">
              <div class="flex items-center gap-3">
                <div class="rounded-2xl bg-emerald-100 p-3 text-emerald-700">
                  <Users class="h-5 w-5" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-slate-900">My Profile</p>
                  <p class="text-sm text-slate-600">Check your stored account and patient details.</p>
                </div>
              </div>
              <ChevronRight class="h-4 w-4 text-slate-500" />
            </RouterLink>
          </div>
        </div>

        <div class="rounded-3xl border border-brand-light/20 bg-white p-6 shadow-sm">
          <PageHeader title="Latest Released Results"
            subtitle="Most recent lab results available to view or download." />

          <div v-if="isLoadingPatientDashboard" class="mt-5 space-y-3">
            <div v-for="placeholder in 3" :key="placeholder" class="h-20 animate-pulse rounded-2xl bg-slate-100" />
          </div>

          <div v-else-if="patientLabResults.length === 0"
            class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-sm text-slate-600">
            No released lab results are available yet.
          </div>

          <div v-else class="mt-5 space-y-3">
            <RouterLink v-for="labResult in patientLabResults.slice(0, 3)" :key="labResult.id"
              :to="{ name: 'userLabResultDetail', params: { labResultId: String(labResult.id) } }"
              class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-4 transition hover:border-brand-light/35 hover:bg-white">
              <div class="flex min-w-0 items-center gap-3">
                <div class="rounded-2xl bg-violet-100 p-3 text-violet-700">
                  <FlaskConical class="h-5 w-5" />
                </div>
                <div class="min-w-0">
                  <p class="truncate text-sm font-semibold text-slate-900">
                    {{ labResult.appointment?.type?.name ?? "Lab Result" }}
                  </p>
                  <p class="mt-1 text-sm text-slate-600">
                    Released {{ formatReleasedAt(labResult.released_at) }}
                  </p>
                </div>
              </div>
              <ChevronRight class="h-4 w-4 shrink-0 text-slate-500" />
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
