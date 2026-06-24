<script setup lang="ts">
import { RouterLink } from "vue-router";
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import ListMeta from "@/shared/components/ListMeta/ListMeta.vue";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";
import AppointmentListPagination from "../../components/AppointmentListPagination.vue";
import AppointmentListTable from "../../components/AppointmentListTable.vue";
import { useAppointmentListData } from "../../composables/useAppointmentListData";
import { useAppointmentRealtime } from "../../composables/useAppointmentRealtime";

const { appointments, meta, isLoadingAppointments, dataError, clearDataError, setPage } = useAppointmentListData("mine");
useAppointmentRealtime("mine");
</script>
<template>
  <section class="rounded-xl border border-brand-light/30 bg-white p-6">
    <PageHeader title="My Appointments" subtitle="Review appointments you have already created." heading-tag="h1">
      <template #actions>
        <RouterLink
          to="/dashboard/appointments/request"
          class="inline-flex min-h-10 items-center justify-center rounded-md bg-black px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-800"
        >
          Request New Appointment
        </RouterLink>
      </template>
    </PageHeader>

    <StatusBanner v-if="dataError" :message="dataError" tone="error" @dismiss="clearDataError" />

    <div class="mt-5">
      <ListMeta :shown-count="appointments.length" :total-count="meta.total" label="appointments"
        :is-loading="isLoadingAppointments" />
      <AppointmentListTable :appointments="appointments" mode="mine" />
      <AppointmentListPagination :current-page="meta.current_page" :last-page="meta.last_page" @update:page="setPage" />
    </div>
  </section>
</template>
