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
  <div class="overflow-hidden rounded-xl border border-brand-light/25">
    <div v-if="!hasRows" class="bg-white px-4 py-8 text-center text-sm text-brand-dark/75">
      No appointments found.
    </div>

    <div v-else class="overflow-x-auto bg-white">
      <table class="min-w-full divide-y divide-brand-light/20">
        <thead class="bg-brand-lighter/15">
          <tr class="text-left text-xs uppercase tracking-[0.12em] text-brand-dark/70">
            <th class="px-4 py-3 font-semibold">Appointment</th>
            <th v-if="props.mode === 'admin'" class="px-4 py-3 font-semibold">Patient</th>
            <th class="px-4 py-3 font-semibold">Schedule</th>
            <th class="px-4 py-3 font-semibold">Status</th>
            <th class="px-4 py-3 font-semibold">Assigned To</th>
            <th class="px-4 py-3 font-semibold">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-brand-light/15">
          <tr v-for="appointment in props.appointments" :key="appointment.id" class="align-top">
            <td class="px-4 py-4">
              <p class="font-medium text-brand-darker">
                {{ appointment.type?.name ?? "Unknown appointment type" }}
              </p>
              <p class="mt-1 text-sm text-brand-dark/75">
                ID #{{ appointment.id }}
              </p>
            </td>
            <td v-if="props.mode === 'admin'" class="px-4 py-4">
              <p class="font-medium text-brand-darker">{{ appointment.user?.name ?? "Unknown patient" }}</p>
              <p class="mt-1 text-sm text-brand-dark/75">{{ appointment.user?.email ?? "-" }}</p>
            </td>
            <td class="px-4 py-4 text-sm text-brand-dark/85">
              <p>{{ formatDateTime(appointment.start_time) }}</p>
              <p class="mt-1 text-brand-dark/65">to {{ formatDateTime(appointment.end_time) }}</p>
            </td>
            <td class="px-4 py-4">
              <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium" :class="getAppointmentStatusClasses(appointment.status)">
                {{ formatAppointmentStatus(appointment.status) }}
              </span>
            </td>
            <td class="px-4 py-4 text-sm text-brand-dark/85">
              <p v-if="appointment.resources?.length">
                {{ appointment.resources.map((resource) => resource.name).join(", ") }}
              </p>
              <p v-else class="text-brand-dark/60">Not assigned yet</p>
            </td>
            <td class="px-4 py-4">
              <RouterLink :to="getDetailsRoute(appointment.id)"
                class="inline-flex rounded-md border border-brand-light/40 px-3 py-2 text-sm text-brand-darker transition hover:bg-brand-lighter/20">
                View
              </RouterLink>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
