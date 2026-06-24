<script setup lang="ts">
import { computed, ref } from "vue";
import { ClipboardPlus, Eye, UserRound, UserRoundCheck } from "lucide-vue-next";
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

const filteredPatients = computed(() => {
  const query = searchTerm.value.trim().toLowerCase();

  return patients.value.filter((patient) => {
    const searchableValue = patient[searchField.value].toLowerCase();
    return !query || searchableValue.includes(query);
  });
});

const activePatientsCount = computed(
  () => patients.value.filter((patient) => patient.status === "active").length,
);
const inactivePatientsCount = computed(
  () => patients.value.filter((patient) => patient.status === "inactive").length,
);
const nextPatient = computed(() => filteredPatients.value[0] ?? patients.value[0] ?? null);

function dismissStatusBanner(): void {
  clearDataError();
}
</script>

<template>
  <section class="rounded-[1.75rem] border border-brand-light/25 bg-white p-6 shadow-sm">
    <PageHeader
      title="Patient Records"
      subtitle="Review patient accounts and open their clinical record details from one place."
      heading-tag="h1"
    />

    <StatusBanner v-if="dataError" :message="dataError" tone="error" @dismiss="dismissStatusBanner" />

    <div class="mt-6 space-y-6">
      <article class="relative overflow-hidden rounded-[1.75rem] border border-brand-light/20 bg-linear-to-br from-white via-sky-50 to-brand-lighter/35 p-6">
        <div class="absolute right-0 top-0 h-28 w-28 rounded-full bg-brand-light/10 blur-3xl" />
        <div class="absolute bottom-0 left-10 h-24 w-24 rounded-full bg-sky-300/10 blur-3xl" />

        <div class="relative grid gap-4 lg:grid-cols-[1.3fr_0.7fr]">
          <div class="space-y-4">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-darker shadow-sm ring-1 ring-brand-light/15">
              <ClipboardPlus class="h-3.5 w-3.5" />
              Record Overview
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
              <div class="rounded-2xl border border-white/80 bg-white/85 p-4 shadow-sm backdrop-blur">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Visible</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{{ filteredPatients.length }}</p>
                <p class="mt-1 text-sm text-slate-600">Patients in current view</p>
              </div>

              <div class="rounded-2xl border border-white/80 bg-white/85 p-4 shadow-sm backdrop-blur">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Active</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{{ activePatientsCount }}</p>
                <p class="mt-1 text-sm text-slate-600">Can access their account</p>
              </div>

              <div class="rounded-2xl border border-white/80 bg-white/85 p-4 shadow-sm backdrop-blur">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Inactive</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{{ inactivePatientsCount }}</p>
                <p class="mt-1 text-sm text-slate-600">Need account review</p>
              </div>
            </div>
          </div>

          <div class="rounded-[1.5rem] border border-white/80 bg-white/90 p-5 shadow-sm backdrop-blur">
            <div class="flex items-center gap-3">
              <div class="rounded-2xl bg-brand-lighter/35 p-3 text-brand-darker">
                <Eye class="h-5 w-5" />
              </div>
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Current Focus</p>
                <p class="mt-1 text-sm font-medium text-slate-900">
                  {{ nextPatient?.name ?? "No patient available" }}
                </p>
              </div>
            </div>

            <div class="mt-4 space-y-2 text-sm text-slate-600">
              <p v-if="nextPatient">{{ nextPatient.email }}</p>
              <p v-if="nextPatient">{{ nextPatient.role }}</p>
              <p v-if="nextPatient">{{ nextPatient.status === "inactive" ? "Inactive" : "Active" }}</p>
              <p v-else>No patient is available in the current view.</p>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                <div class="flex items-center gap-2 text-brand-dark/70">
                  <UserRoundCheck class="h-4 w-4" />
                  <span class="text-[11px] font-semibold uppercase tracking-[0.12em]">Active</span>
                </div>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ activePatientsCount }}</p>
              </div>
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                <div class="flex items-center gap-2 text-brand-dark/70">
                  <UserRound class="h-4 w-4" />
                  <span class="text-[11px] font-semibold uppercase tracking-[0.12em]">Total</span>
                </div>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ patients.length }}</p>
              </div>
            </div>
          </div>
        </div>
      </article>

      <article class="overflow-hidden rounded-[1.5rem] border border-brand-light/20 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-brand-light/15 px-5 py-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Patient Directory</p>
            <h2 class="mt-2 text-xl font-semibold tracking-tight text-brand-darker">Search and open individual patient records</h2>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
            <ListMeta
              :shown-count="filteredPatients.length"
              :total-count="patients.length"
              label="patients"
              :is-loading="isLoadingPatients"
            />
          </div>
        </div>

        <div class="px-5 py-5">
          <PatientTableFilters
            :search-term="searchTerm"
            :search-field="searchField"
            @update:search-term="searchTerm = $event"
            @update:search-field="searchField = $event"
          />

          <div class="mt-5">
            <PatientDataTable :patients="filteredPatients" />
          </div>
        </div>
      </article>
    </div>
  </section>
</template>
