<script setup lang="ts">
import { computed, ref } from "vue";
import PatientDataTable from "@/features/patients/components/PatientDataTable/PatientDataTable.vue";
import PatientTableFilters from "@/features/patients/components/PatientTableFilters/PatientTableFilters.vue";
import { usePatientListData } from "@/features/patients/composables/usePatientListData";
import type { PatientListSearchField } from "@/features/patients/types";
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import ListMeta from "@/shared/components/ListMeta/ListMeta.vue";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";

const { patients, isLoadingPatients, dataError, clearDataError } = usePatientListData();

const searchTerm = ref("");
const searchField = ref<PatientListSearchField>("name");
const roleFilter = ref("all");

const availableRoles = computed(() =>
  Array.from(new Set(patients.value.map((patient) => patient.role))).filter(Boolean),
);

const filteredPatients = computed(() => {
  const query = searchTerm.value.trim().toLowerCase();

  return patients.value.filter((patient) => {
    const searchableValue = patient[searchField.value].toLowerCase();
    const matchesQuery = !query || searchableValue.includes(query);
    const matchesRole = roleFilter.value === "all" || patient.role === roleFilter.value;
    return matchesQuery && matchesRole;
  });
});

function dismissStatusBanner(): void {
  clearDataError();
}
</script>

<template>
  <section class="rounded-xl border border-brand-light/30 bg-white p-6">
    <PageHeader title="Patient List" subtitle="Live patient records from the database." heading-tag="h1" />

    <StatusBanner v-if="dataError" :message="dataError" tone="error" @dismiss="dismissStatusBanner" />

    <div class="mt-5">
      <PatientTableFilters :search-term="searchTerm" :search-field="searchField" :role-filter="roleFilter"
        :available-roles="availableRoles" @update:search-term="searchTerm = $event"
        @update:search-field="searchField = $event" @update:role-filter="roleFilter = $event" />
      <ListMeta :shown-count="filteredPatients.length" :total-count="patients.length" label="patients"
        :is-loading="isLoadingPatients" />
      <PatientDataTable :patients="filteredPatients" />
    </div>
  </section>
</template>
