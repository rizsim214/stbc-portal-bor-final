<script setup lang="ts">
import { computed } from "vue";
import { RouterLink } from "vue-router";
import type { AppointmentListItem } from "../types";
import { formatAppointmentStatus, getAppointmentStatusClasses } from "../utils/status";

const props = defineProps<{
  appointments: AppointmentListItem[];
  mode: "mine" | "admin";
}>();

function formatDateTime(value: string): string {
  return new Date(value).toLocaleString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
    hour: "numeric",
    minute: "2-digit",
  });
}

function getDetailsRoute(appointmentId: number) {
  if (props.mode === "admin") {
    return {
      name: "AdminAppointmentView",
      params: { appointmentId: String(appointmentId) },
    };
  }

  return {
    name: "MyAppointmentView",
    params: { appointmentId: String(appointmentId) },
  };
}

const hasRows = computed(() => props.appointments.length > 0);
</script>

<template>
  <div class="overflow-hidden rounded-[1.25rem] border border-brand-light/20 bg-white">
    <div v-if="!hasRows" class="bg-linear-to-br from-white to-slate-50 px-4 py-10 text-center text-sm text-brand-dark/75">
      No appointments found.
    </div>

    <div v-else class="overflow-x-auto bg-white">
      <table class="min-w-full divide-y divide-brand-light/15">
        <thead class="bg-slate-50/90">
          <tr class="text-left text-xs uppercase tracking-[0.12em] text-brand-dark/70">
            <th class="px-5 py-3.5 font-semibold">Appointment</th>
            <th v-if="props.mode === 'admin'" class="px-5 py-3.5 font-semibold">Patient</th>
            <th class="px-5 py-3.5 font-semibold">Schedule</th>
            <th class="px-5 py-3.5 font-semibold">Status</th>
            <th class="px-5 py-3.5 font-semibold">Assigned To</th>
            <th class="px-5 py-3.5 font-semibold">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-brand-light/10">
          <tr
            v-for="appointment in props.appointments"
            :key="appointment.id"
            class="align-top transition hover:bg-slate-50/80"
          >
            <td class="px-5 py-4">
              <p class="font-semibold text-brand-darker">
                {{ appointment.type?.name ?? "Unknown appointment type" }}
              </p>
              <p class="mt-1 text-sm text-brand-dark/75">
                ID #{{ appointment.id }}
              </p>
            </td>
            <td v-if="props.mode === 'admin'" class="px-5 py-4">
              <p class="font-medium text-brand-darker">{{ appointment.user?.name ?? "Unknown patient" }}</p>
              <p class="mt-1 text-sm text-brand-dark/70">{{ appointment.user?.email ?? "-" }}</p>
            </td>
            <td class="px-5 py-4 text-sm text-brand-dark/85">
              <p class="font-medium text-slate-900">{{ formatDateTime(appointment.start_time) }}</p>
              <p class="mt-1 text-brand-dark/65">to {{ formatDateTime(appointment.end_time) }}</p>
            </td>
            <td class="px-5 py-4">
              <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                :class="getAppointmentStatusClasses(appointment.status)">
                {{ formatAppointmentStatus(appointment.status) }}
              </span>
            </td>
            <td class="px-5 py-4 text-sm text-brand-dark/85">
              <p v-if="appointment.resources?.length" class="font-medium text-slate-900">
                {{appointment.resources.map((resource) => resource.name).join(", ")}}
              </p>
              <p v-else class="text-brand-dark/60">Unassigned</p>
            </td>
            <td class="px-5 py-4">
              <RouterLink :to="getDetailsRoute(appointment.id)"
                class="inline-flex rounded-xl border border-brand-light/35 bg-white px-3 py-2 text-sm font-medium text-brand-darker transition hover:border-brand-light/60 hover:bg-brand-lighter/20">
                View
              </RouterLink>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
