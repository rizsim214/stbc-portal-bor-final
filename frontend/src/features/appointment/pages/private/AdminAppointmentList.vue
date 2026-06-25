<script setup lang="ts">
import { CalendarClock, ClipboardList, TimerReset, UserRoundCog } from "lucide-vue-next";
import { computed } from "vue";
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import ListMeta from "@/shared/components/ListMeta/ListMeta.vue";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";
import AppointmentListPagination from "../../components/AppointmentListPagination.vue";
import AppointmentListTable from "../../components/AppointmentListTable.vue";
import { useAppointmentListData } from "../../composables/useAppointmentListData";
import { useAppointmentRealtime } from "../../composables/useAppointmentRealtime";
import { formatAppointmentStatus } from "../../utils/status";

const { appointments, meta, isLoadingAppointments, dataError, clearDataError, setPage } = useAppointmentListData("admin");
useAppointmentRealtime("admin");

const pendingCount = computed(
  () => appointments.value.filter((appointment) => appointment.status === "pending").length,
);
const activeCount = computed(
  () => appointments.value.filter((appointment) => appointment.status !== "completed").length,
);
const unassignedCount = computed(
  () => appointments.value.filter((appointment) => !appointment.resources?.length).length,
);
const nextAppointment = computed(() => {
  return [...appointments.value]
    .filter((appointment) => new Date(appointment.start_time).getTime() >= Date.now())
    .sort(
      (left, right) =>
        new Date(left.start_time).getTime() - new Date(right.start_time).getTime(),
    )[0] ?? null;
});

function formatDateTime(value: string): string {
  return new Date(value).toLocaleString("en-US", {
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
  });
}
</script>
<template>
  <section class="rounded-[1.75rem] border border-brand-light/25 bg-white p-6 shadow-sm">
    <PageHeader
      title="All Appointments"
      subtitle="Monitor bookings, staffing gaps, and the next appointments that need attention."
      heading-tag="h1"
    />

    <StatusBanner v-if="dataError" :message="dataError" tone="error" @dismiss="clearDataError" />

    <div class="mt-6 space-y-6">
      <article class="relative overflow-hidden rounded-[1.75rem] border border-brand-light/20 bg-linear-to-br from-white via-sky-50 to-brand-lighter/35 p-6">
        <div class="absolute right-0 top-0 h-28 w-28 rounded-full bg-brand-light/10 blur-3xl" />
        <div class="absolute bottom-0 left-10 h-24 w-24 rounded-full bg-sky-300/10 blur-3xl" />

        <div class="relative grid gap-4 lg:grid-cols-[1.3fr_0.7fr]">
          <div class="space-y-4">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-darker shadow-sm ring-1 ring-brand-light/15">
              <ClipboardList class="h-3.5 w-3.5" />
              Clinic Queue
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
              <div class="rounded-2xl border border-white/80 bg-white/85 p-4 shadow-sm backdrop-blur">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">On this page</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{{ appointments.length }}</p>
                <p class="mt-1 text-sm text-slate-600">Visible appointments</p>
              </div>

              <div class="rounded-2xl border border-white/80 bg-white/85 p-4 shadow-sm backdrop-blur">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Pending</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{{ pendingCount }}</p>
                <p class="mt-1 text-sm text-slate-600">Need assignment or action</p>
              </div>

              <div class="rounded-2xl border border-white/80 bg-white/85 p-4 shadow-sm backdrop-blur">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Unassigned</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{{ unassignedCount }}</p>
                <p class="mt-1 text-sm text-slate-600">Missing staff coverage</p>
              </div>
            </div>
          </div>

          <div class="rounded-[1.5rem] border border-white/80 bg-white/90 p-5 shadow-sm backdrop-blur">
            <div class="flex items-center gap-3">
              <div class="rounded-2xl bg-brand-lighter/35 p-3 text-brand-darker">
                <CalendarClock class="h-5 w-5" />
              </div>
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Next Focus</p>
                <p class="mt-1 text-sm font-medium text-slate-900">
                  {{ nextAppointment?.type?.name ?? "No upcoming appointment" }}
                </p>
              </div>
            </div>

            <div class="mt-4 space-y-2 text-sm text-slate-600">
              <p v-if="nextAppointment">
                {{ formatDateTime(nextAppointment.start_time) }}
              </p>
              <p v-if="nextAppointment">
                {{ nextAppointment.user?.name ?? "Unknown patient" }}
              </p>
              <p v-if="nextAppointment">
                {{ formatAppointmentStatus(nextAppointment.status) }}
              </p>
              <p v-else>No scheduled appointment ahead on this page.</p>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                <div class="flex items-center gap-2 text-brand-dark/70">
                  <TimerReset class="h-4 w-4" />
                  <span class="text-[11px] font-semibold uppercase tracking-[0.12em]">Active</span>
                </div>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ activeCount }}</p>
              </div>
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                <div class="flex items-center gap-2 text-brand-dark/70">
                  <UserRoundCog class="h-4 w-4" />
                  <span class="text-[11px] font-semibold uppercase tracking-[0.12em]">Total</span>
                </div>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ meta.total }}</p>
              </div>
            </div>
          </div>
        </div>
      </article>

      <article class="overflow-hidden rounded-[1.5rem] border border-brand-light/20 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-brand-light/15 px-5 py-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Appointment Queue</p>
            <h2 class="mt-2 text-xl font-semibold tracking-tight text-brand-darker">Recent bookings and current progress</h2>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
            <ListMeta
              :shown-count="appointments.length"
              :total-count="meta.total"
              label="appointments"
              :is-loading="isLoadingAppointments"
            />
          </div>
        </div>

        <div class="px-5 py-5">
          <AppointmentListTable :appointments="appointments" mode="admin" />
        </div>

        <div class="border-t border-brand-light/15 px-5 py-4">
          <AppointmentListPagination
            :current-page="meta.current_page"
            :last-page="meta.last_page"
            @update:page="setPage"
          />
        </div>
      </article>
    </div>
  </section>
</template>
