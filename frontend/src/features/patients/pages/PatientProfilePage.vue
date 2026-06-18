<script setup lang="ts">
import { computed } from "vue";
import { ChevronLeft } from "lucide-vue-next";
import { usePatientRecordsData } from "@/features/patients/composables/usePatientRecordsData";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";

const props = defineProps<{
  userId: string;
}>();

const { patient, records, isLoading, dataError, clearDataError } =
  usePatientRecordsData(props.userId);

const isFrequentVisitor = computed(() => records.value.length >= 3);
const lastVisit = computed(() => records.value[0]?.date ?? "Unknown");

function dismissStatusBanner(): void {
  clearDataError();
}
</script>

<template>
  <div class="mb-2">
    <RouterLink :to="{ name: 'userList' }"
      class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-brand-darker transition no-underline underline-offset-2 hover:underline tracking-widest">
      <ChevronLeft class="h-4 w-4" />
      Back
    </RouterLink>
  </div>

  <section class="rounded-xl border border-brand-light/30 bg-white p-6">
    <StatusBanner v-if="dataError" :message="dataError" tone="error" @dismiss="dismissStatusBanner" />

    <div class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <p class="text-xs uppercase tracking-[0.12em] text-brand-dark/65">Patient Profile</p>
        <div class="mt-2 flex flex-wrap items-center gap-3">
          <h1 class="text-2xl font-semibold text-brand-darker">
            {{ patient?.name ?? "Patient" }}
          </h1>
          <span class="rounded-full px-3 py-1 text-xs font-medium"
            :class="isFrequentVisitor ? 'bg-brand-dark text-white' : 'bg-brand-lighter/50 text-brand-darker'">
            {{ isFrequentVisitor ? "Frequent Visitor" : "Standard Visit History" }}
          </span>
        </div>
        <p class="mt-2 text-sm text-brand-dark">
          Combined profile and medical history for the selected patient.
        </p>
      </div>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2">
      <div class="rounded-lg border border-brand-light/25 bg-brand-lighter/10 p-4">
        <p class="text-xs uppercase tracking-widest text-brand-dark/65">Contact</p>
        <dl class="mt-3 space-y-2 text-sm text-brand-darker">
          <div class="flex justify-between gap-4">
            <dt class="text-brand-dark/70">Email</dt>
            <dd class="font-medium">{{ patient?.email ?? "Unknown" }}</dd>
          </div>
          <div class="flex justify-between gap-4">
            <dt class="text-brand-dark/70">Account Status</dt>
            <dd class="font-medium capitalize">
              {{ patient?.account_status ?? "active" }}
            </dd>
          </div>
        </dl>
      </div>

      <div class="rounded-lg border border-brand-light/25 bg-brand-lighter/10 p-4">
        <p class="text-xs uppercase tracking-widest text-brand-dark/65">Status</p>
        <dl class="mt-3 space-y-2 text-sm text-brand-darker">
          <div class="flex justify-between gap-4">
            <dt class="text-brand-dark/70">Last Visit</dt>
            <dd class="font-medium">{{ lastVisit }}</dd>
          </div>
          <div class="flex justify-between gap-4">
            <dt class="text-brand-dark/70">Records Found</dt>
            <dd class="font-medium">{{ records.length }}</dd>
          </div>
        </dl>
      </div>
    </div>

    <div class="mt-8 flex items-center justify-between gap-3">
      <div>
        <p class="text-xs uppercase tracking-[0.12em] text-brand-dark/65">Medical History</p>
        <p class="mt-2 text-sm text-brand-dark">
          Appointments and released lab results are shown together below.
        </p>
      </div>
    </div>

    <div v-if="isLoading" class="mt-6 space-y-3">
      <div class="rounded-lg border border-brand-light/25 bg-brand-lighter/10 p-4">
        Loading records...
      </div>
    </div>

    <div v-else-if="records.length === 0"
      class="mt-6 rounded-lg border border-dashed border-brand-light/30 p-4 text-sm text-brand-dark">
      No medical history found for this patient.
    </div>

    <div v-else class="mt-6 space-y-3">
      <div v-for="record in records" :key="record.id"
        class="rounded-lg border border-brand-light/25 bg-brand-lighter/10 p-4">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-sm font-semibold text-brand-darker">
              {{ record.date }} - {{ record.title }}
            </p>
            <p class="mt-1 text-sm text-brand-dark">{{ record.summary }}</p>
            <p class="mt-1 text-xs uppercase tracking-widest text-brand-dark/65">
              {{ record.kind === "lab_result" ? "Lab Result" : "Appointment" }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
