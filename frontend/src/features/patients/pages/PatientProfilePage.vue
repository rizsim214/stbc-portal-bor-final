<script setup lang="ts">
import { computed } from "vue";
import {
  Activity,
  CalendarClock,
  ChevronLeft,
  ClipboardPlus,
  Mail,
  ShieldCheck,
} from "lucide-vue-next";
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
  <div class="mb-3">
    <RouterLink :to="{ name: 'userList' }"
      class="inline-flex items-center gap-2 rounded-xl border border-brand-light/25 bg-white px-3 py-2 text-sm font-medium text-brand-darker transition no-underline shadow-sm underline-offset-2 hover:bg-brand-lighter/15 hover:underline">
      <ChevronLeft class="h-4 w-4" />
      Back
    </RouterLink>
  </div>

  <section class="rounded-[1.75rem] border border-brand-light/25 bg-white p-6 shadow-sm">
    <StatusBanner v-if="dataError" :message="dataError" tone="error" @dismiss="dismissStatusBanner" />

    <article class="relative overflow-hidden rounded-[1.75rem] border border-brand-light/20 bg-linear-to-br from-white via-sky-50 to-brand-lighter/35 p-6">
      <div class="absolute right-0 top-0 h-28 w-28 rounded-full bg-brand-light/10 blur-3xl" />
      <div class="absolute bottom-0 left-10 h-24 w-24 rounded-full bg-sky-300/10 blur-3xl" />

      <div class="relative grid gap-4 lg:grid-cols-[1.25fr_0.75fr]">
        <div class="space-y-4">
          <div class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-darker shadow-sm ring-1 ring-brand-light/15">
            <ClipboardPlus class="h-3.5 w-3.5" />
            Patient Profile
          </div>

          <div>
            <div class="flex flex-wrap items-center gap-3">
              <h1 class="text-2xl font-semibold tracking-tight text-brand-darker md:text-3xl">
                {{ patient?.name ?? "Patient" }}
              </h1>
              <span
                class="rounded-full px-3 py-1 text-xs font-medium"
                :class="isFrequentVisitor ? 'bg-brand-dark text-white' : 'bg-white/85 text-brand-darker ring-1 ring-brand-light/20'"
              >
                {{ isFrequentVisitor ? "Frequent Visitor" : "Standard Visit History" }}
              </span>
            </div>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-brand-dark/80">
              Combined profile details and medical history for the selected patient.
            </p>
          </div>

          <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-white/80 bg-white/85 p-4 shadow-sm backdrop-blur">
              <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Records</p>
              <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{{ records.length }}</p>
              <p class="mt-1 text-sm text-slate-600">Entries in history</p>
            </div>

            <div class="rounded-2xl border border-white/80 bg-white/85 p-4 shadow-sm backdrop-blur">
              <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Last Visit</p>
              <p class="mt-2 text-lg font-semibold tracking-tight text-slate-900">{{ lastVisit }}</p>
              <p class="mt-1 text-sm text-slate-600">Most recent entry</p>
            </div>

            <div class="rounded-2xl border border-white/80 bg-white/85 p-4 shadow-sm backdrop-blur">
              <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Account</p>
              <p class="mt-2 text-lg font-semibold tracking-tight text-slate-900">
                {{ patient?.account_status === "inactive" ? "Inactive" : "Active" }}
              </p>
              <p class="mt-1 text-sm text-slate-600">Current access status</p>
            </div>
          </div>
        </div>

        <aside class="rounded-[1.5rem] border border-white/80 bg-white/90 p-5 shadow-sm backdrop-blur">
          <div class="flex items-center gap-3">
            <div class="rounded-2xl bg-brand-lighter/35 p-3 text-brand-darker">
              <ShieldCheck class="h-5 w-5" />
            </div>
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Quick Summary</p>
              <p class="mt-1 text-sm font-medium text-slate-900">{{ patient?.name ?? "Patient" }}</p>
            </div>
          </div>

          <div class="mt-5 space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <div class="flex items-start gap-3">
                <Mail class="mt-0.5 h-4 w-4 text-brand-dark/70" />
                <div class="min-w-0">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Email</p>
                  <p class="mt-1 text-sm font-medium text-slate-900 break-all">{{ patient?.email ?? "Unknown" }}</p>
                </div>
              </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <div class="flex items-start gap-3">
                <CalendarClock class="mt-0.5 h-4 w-4 text-brand-dark/70" />
                <div class="min-w-0">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Visit Pattern</p>
                  <p class="mt-1 text-sm font-medium text-slate-900">
                    {{ isFrequentVisitor ? "Frequent visitor history" : "Standard visit history" }}
                  </p>
                </div>
              </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <div class="flex items-start gap-3">
                <Activity class="mt-0.5 h-4 w-4 text-brand-dark/70" />
                <div class="min-w-0">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Account Status</p>
                  <p class="mt-1 text-sm font-medium text-slate-900">
                    <span class="inline-flex items-center gap-2">
                      <span
                        :class="[
                          'inline-flex h-2 w-2 rounded-full',
                          patient?.account_status === 'inactive' ? 'bg-red-500' : 'bg-emerald-500',
                        ]"
                      />
                      {{ patient?.account_status === "inactive" ? "Inactive" : "Active" }}
                    </span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </article>

    <div class="mt-6 overflow-hidden rounded-[1.5rem] border border-brand-light/20 bg-white shadow-sm">
      <div class="border-b border-brand-light/15 px-5 py-4">
        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Medical History</p>
        <h2 class="mt-2 text-xl font-semibold tracking-tight text-brand-darker">Appointments and released results</h2>
      </div>

      <div v-if="isLoading" class="px-5 py-5">
        <div class="rounded-2xl border border-brand-light/25 bg-brand-lighter/10 p-4 text-sm text-brand-dark">
          Loading records...
        </div>
      </div>

      <div v-else-if="records.length === 0" class="px-5 py-5">
        <div class="rounded-2xl border border-dashed border-brand-light/30 p-4 text-sm text-brand-dark">
          No medical history found for this patient.
        </div>
      </div>

      <div v-else class="px-5 py-5">
        <div class="space-y-3">
          <article
            v-for="record in records"
            :key="record.id"
            class="rounded-2xl border border-brand-light/20 bg-linear-to-r from-white to-slate-50 p-4 transition hover:border-brand-light/35"
          >
            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <p class="text-sm font-semibold text-brand-darker">
                    {{ record.title }}
                  </p>
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-medium uppercase tracking-[0.12em]"
                    :class="record.kind === 'lab_result'
                      ? 'bg-violet-50 text-violet-700 ring-1 ring-violet-200'
                      : 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'"
                  >
                    {{ record.kind === "lab_result" ? "Lab Result" : "Appointment" }}
                  </span>
                </div>
                <p class="mt-1 text-sm leading-6 text-brand-dark">{{ record.summary }}</p>
              </div>

              <div class="shrink-0 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
                {{ record.date }}
              </div>
            </div>
          </article>
        </div>
      </div>
    </div>
  </section>
</template>
