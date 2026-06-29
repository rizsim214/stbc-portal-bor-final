<script setup lang="ts">
import { useQuery } from "@tanstack/vue-query";
import {
  CalendarDays,
  ChevronLeft,
  CircleAlert,
  Download,
  FileText,
  FlaskConical,
  ScanEye,
  UserRound,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import { appointmentsApi } from "@/features/appointment/api/appointmentsApi";
import { formatScheduleRange } from "@/features/appointment/utils/schedule";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";
import { Button } from "@/shared/ui/button";
import { patientsApi, type PatientMedicalHistoryItem } from "../api/patientsApi";

const props = defineProps<{
  userId: string;
  appointmentId: string;
}>();

const appointmentIdNumber = computed(() => Number(props.appointmentId));
const labResultUrl = ref("");
const labResultError = ref("");
const isLoadingLabResult = ref(false);

const historyQuery = useQuery({
  queryKey: ["patients", "records", props.userId],
  queryFn: async () => {
    const { data } = await patientsApi.getPatientMedicalHistory(props.userId);
    return data.data;
  },
  enabled: computed(() => Boolean(props.userId)),
});

const appointmentQuery = useQuery({
  queryKey: ["appointments", "detail", props.appointmentId],
  queryFn: async () => {
    const { data } = await appointmentsApi.getAppointment(props.appointmentId);
    return data.data;
  },
  enabled: computed(() => Boolean(props.appointmentId)),
});

const patient = computed(
  () => historyQuery.data.value?.user ?? appointmentQuery.data.value?.user ?? null,
);

const historyRecord = computed<PatientMedicalHistoryItem | null>(() => {
  const records = historyQuery.data.value?.medical_history ?? [];

  return (
    records.find((item) => item.appointment?.id === appointmentIdNumber.value) ?? null
  );
});

const appointment = computed(() => appointmentQuery.data.value ?? null);
const labResult = computed(() => appointment.value?.lab_result ?? null);
const filePath = computed(
  () => labResult.value?.file_path ?? historyRecord.value?.file_path ?? null,
);
const hasLabResult = computed(() => Boolean(labResult.value?.id));
const isPdf = computed(() => filePath.value?.toLowerCase().endsWith(".pdf") ?? false);
const isLoading = computed(
  () =>
    historyQuery.isPending.value ||
    appointmentQuery.isPending.value ||
    historyQuery.isFetching.value ||
    appointmentQuery.isFetching.value,
);
const dataError = computed(() => {
  const historyError =
    historyQuery.isError.value && historyQuery.error.value instanceof Error
      ? historyQuery.error.value.message
      : "";
  const appointmentError =
    appointmentQuery.isError.value && appointmentQuery.error.value instanceof Error
      ? appointmentQuery.error.value.message
      : "";

  return historyError || appointmentError || "";
});

const recordTitle = computed(
  () => historyRecord.value?.title ?? appointment.value?.type?.name ?? "Appointment Record",
);
const recordSummary = computed(
  () =>
    historyRecord.value?.summary ??
    appointment.value?.notes ??
    "No lab-result summary is available for this appointment.",
);

async function ensureLabResultUrl(): Promise<string> {
  if (!labResult.value?.id) {
    throw new Error("No uploaded lab result is available for this appointment.");
  }

  if (labResultUrl.value) {
    return labResultUrl.value;
  }

  isLoadingLabResult.value = true;
  labResultError.value = "";

  try {
    const { data } = await appointmentsApi.getLabResultFileUrl(labResult.value.id);
    labResultUrl.value = data.data.download_url;
    return labResultUrl.value;
  } catch (error) {
    labResultError.value =
      error instanceof Error && error.message
        ? error.message
        : "Unable to load the lab result file.";
    throw error;
  } finally {
    isLoadingLabResult.value = false;
  }
}

async function handleViewLabResult(): Promise<void> {
  await ensureLabResultUrl();
}

async function handleDownloadLabResult(): Promise<void> {
  const url = await ensureLabResultUrl();
  globalThis.open(url, "_blank", "noopener,noreferrer");
}
</script>

<template>
  <section class="space-y-6">
    <div>
      <RouterLink
        :to="{ name: 'userDetailView', params: { userId } }"
        class="inline-flex items-center gap-2 rounded-xl border border-brand-light/25 bg-white px-3 py-2 text-sm font-medium text-brand-darker transition no-underline shadow-sm underline-offset-2 hover:bg-brand-lighter/15 hover:underline"
      >
        <ChevronLeft class="h-4 w-4" />
        Back to Patient Profile
      </RouterLink>
    </div>

    <div v-if="dataError">
      <StatusBanner :message="dataError" tone="error" />
    </div>

    <div v-if="isLoading" class="rounded-[1.75rem] border border-brand-light/20 bg-white p-6 shadow-sm">
      <p class="text-sm text-brand-dark">Loading lab-result record...</p>
    </div>

    <template v-else>
      <article class="relative overflow-hidden rounded-[1.75rem] border border-brand-light/20 bg-linear-to-br from-white via-sky-50 to-brand-lighter/35 p-6 shadow-sm">
        <div class="absolute right-0 top-0 h-28 w-28 rounded-full bg-brand-light/10 blur-3xl" />
        <div class="absolute bottom-0 left-10 h-24 w-24 rounded-full bg-sky-300/10 blur-3xl" />

        <div class="relative grid gap-4 lg:grid-cols-[1.25fr_0.75fr]">
          <div class="space-y-4">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-darker shadow-sm ring-1 ring-brand-light/15">
              <FlaskConical class="h-3.5 w-3.5" />
              Appointment Lab Result
            </div>

            <div>
              <h1 class="text-2xl font-semibold tracking-tight text-brand-darker md:text-3xl">
                {{ recordTitle }}
              </h1>
              <p class="mt-3 max-w-2xl text-sm leading-6 text-brand-dark/80">
                {{ recordSummary }}
              </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
              <div class="rounded-2xl border border-white/80 bg-white/85 p-4 shadow-sm backdrop-blur">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Patient</p>
                <p class="mt-2 text-base font-semibold tracking-tight text-slate-900">
                  {{ patient?.name ?? appointment?.user?.name ?? "Patient" }}
                </p>
                <p class="mt-1 text-sm text-slate-600">{{ patient?.email ?? appointment?.user?.email ?? "-" }}</p>
              </div>

              <div class="rounded-2xl border border-white/80 bg-white/85 p-4 shadow-sm backdrop-blur">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Schedule</p>
                <p class="mt-2 text-base font-semibold tracking-tight text-slate-900">
                  {{ appointment ? formatScheduleRange(appointment.start_time, appointment.end_time) : "-" }}
                </p>
                <p class="mt-1 text-sm text-slate-600">Appointment window</p>
              </div>

              <div class="rounded-2xl border border-white/80 bg-white/85 p-4 shadow-sm backdrop-blur">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Lab Result</p>
                <p class="mt-2 text-base font-semibold tracking-tight text-slate-900">
                  {{ hasLabResult ? "Available" : "Not Uploaded" }}
                </p>
                <p class="mt-1 text-sm text-slate-600">
                  {{ historyRecord?.released_at ? "Released to patient" : "Admin file access" }}
                </p>
              </div>
            </div>
          </div>

          <aside class="rounded-[1.5rem] border border-white/80 bg-white/90 p-5 shadow-sm backdrop-blur">
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Record Details</p>

            <div class="mt-5 space-y-4">
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-start gap-3">
                  <CalendarDays class="mt-0.5 h-4 w-4 text-brand-dark/70" />
                  <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Appointment Date</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">
                      {{ historyRecord?.date?.slice(0, 10) ?? appointment?.start_time?.slice(0, 10) ?? "Unknown" }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-start gap-3">
                  <UserRound class="mt-0.5 h-4 w-4 text-brand-dark/70" />
                  <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Notes</p>
                    <p class="mt-1 text-sm leading-6 text-slate-700">
                      {{ appointment?.notes ?? "No appointment notes were recorded." }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-start gap-3">
                  <FileText class="mt-0.5 h-4 w-4 text-brand-dark/70" />
                  <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">File Type</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">
                      {{ filePath ? (isPdf ? "PDF document" : "Image document") : "No file uploaded" }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </aside>
        </div>
      </article>

      <section class="rounded-[1.75rem] border border-brand-light/20 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Lab Result File</p>
            <h2 class="mt-2 text-xl font-semibold tracking-tight text-brand-darker">Preview and download</h2>
            <p class="mt-2 text-sm leading-6 text-brand-dark/80">
              Open the uploaded result for this appointment or download a copy.
            </p>
          </div>

          <div v-if="hasLabResult" class="flex flex-wrap gap-3">
            <Button
              type="button"
              class="w-auto bg-brand-dark text-white hover:bg-brand-darker"
              :loading="isLoadingLabResult"
              @click="handleViewLabResult"
            >
              <ScanEye class="mr-2 h-4 w-4" />
              View File
            </Button>

            <Button
              type="button"
              variant="outline"
              class="w-auto border-brand-light/40 text-brand-darker hover:bg-brand-lighter/20"
              :disabled="isLoadingLabResult"
              @click="handleDownloadLabResult"
            >
              <Download class="mr-2 h-4 w-4" />
              Download
            </Button>
          </div>
        </div>

        <StatusBanner
          v-if="labResultError"
          class="mt-4"
          :message="labResultError"
          tone="error"
          @dismiss="labResultError = ''"
        />

        <div
          v-if="!hasLabResult"
          class="mt-6 rounded-2xl border border-dashed border-brand-light/30 bg-brand-lighter/10 p-5"
        >
          <div class="flex items-start gap-3">
            <CircleAlert class="mt-0.5 h-5 w-5 text-brand-dark/70" />
            <div>
              <p class="text-sm font-semibold text-brand-darker">No lab result file is attached to this appointment.</p>
              <p class="mt-2 text-sm leading-6 text-brand-dark/75">
                The record page is available, but there is nothing to preview or download yet.
              </p>
            </div>
          </div>
        </div>

        <div
          v-else-if="labResultUrl"
          class="mt-6 overflow-hidden rounded-2xl border border-brand-light/20 bg-slate-50"
        >
          <iframe
            v-if="isPdf"
            :src="labResultUrl"
            title="Admin Patient Lab Result Preview"
            class="h-[42rem] w-full bg-white"
          />
          <img
            v-else
            :src="labResultUrl"
            alt="Lab result preview"
            class="max-h-[42rem] w-full object-contain bg-white"
          />
        </div>
      </section>
    </template>
  </section>
</template>
