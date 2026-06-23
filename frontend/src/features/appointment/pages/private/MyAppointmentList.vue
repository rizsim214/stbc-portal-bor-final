<script setup lang="ts">
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import ListMeta from "@/shared/components/ListMeta/ListMeta.vue";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";
import AppointmentListPagination from "../../components/AppointmentListPagination.vue";
import AppointmentListTable from "../../components/AppointmentListTable.vue";
import { useAppointmentListData } from "../../composables/useAppointmentListData";

const { appointments, meta, isLoadingAppointments, dataError, clearDataError, setPage } = useAppointmentListData("mine");
</script>
<template>
  <section class="rounded-xl border border-brand-light/30 bg-white p-6">
    <PageHeader title="My Appointments" subtitle="Review appointments you have already created." heading-tag="h1" />

    <StatusBanner v-if="dataError" :message="dataError" tone="error" @dismiss="clearDataError" />

    <div class="mt-5">
      <ListMeta :shown-count="appointments.length" :total-count="meta.total" label="appointments"
        :is-loading="isLoadingAppointments" />
      <AppointmentListTable :appointments="appointments" mode="mine" />
      <AppointmentListPagination :current-page="meta.current_page" :last-page="meta.last_page" @update:page="setPage" />
    </div>
  </section>
</template>
