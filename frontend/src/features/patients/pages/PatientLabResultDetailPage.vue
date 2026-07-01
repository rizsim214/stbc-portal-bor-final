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
} from "lucide-vue-next";
import { computed, ref } from "vue";
import { appointmentsApi } from "@/features/appointment/api/appointmentsApi";
import type { LabResultListItem } from "@/features/appointment/types";
import { formatScheduleRange } from "@/features/appointment/utils/schedule";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";
import { Button } from "@/shared/ui/button";

const props = defineProps<{
  labResultId: string;
}>();

const labResultUrl = ref("");
const labResultError = ref("");
const isLoadingLabResult = ref(false);

const labResultQuery = useQuery({
  queryKey: ["lab-results", "detail", props.labResultId],
  queryFn: async () => {
    const { data } = await appointmentsApi.getLabResult(props.labResultId);
    return data.data;
  },
  enabled: computed(() => Boolean(props.labResultId)),
});

const labResult = computed<LabResultListItem | null>(() => labResultQuery.data.value ?? null);
const isPdf = computed(() => labResult.value?.file_path?.toLowerCase().endsWith(".pdf") ?? false);
const isLoading = computed(
  () => labResultQuery.isPending.value || labResultQuery.isFetching.value,
);
const dataError = computed(() => {
  if (!labResultQuery.isError.value) {
    return "";
  }

  return labResultQuery.error.value instanceof Error
    ? labResultQuery.error.value.message
    : "Unable to load this lab result.";
});

function formatReleasedAt(value: string | null): string {
  if (!value) {
    return "Not released";
  }

  const parsed = new Date(value);

  if (Number.isNaN(parsed.getTime())) {
    return value;
  }

  return new Intl.DateTimeFormat("en-US", {
    month: "long",
    day: "numeric",
    year: "numeric",
    hour: "numeric",
    minute: "2-digit",
  }).format(parsed);
}

function getRecordTitle(item: LabResultListItem | null): string {
  return item?.appointment?.type?.name ?? "Lab Result";
}

function getRecordSummary(item: LabResultListItem | null): string {
  return (
    item?.appointment?.notes ??
    "No appointment note was recorded for this lab result."
  );
}

async function ensureLabResultUrl(): Promise<string> {
  if (!labResult.value?.id) {
    throw new Error("No lab result file is available for this record.");
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
  } catch {
    labResultError.value = "Unable to load the selected lab result file.";
    throw new Error(labResultError.value);
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
        :to="{ name: 'userMedicalRecord' }"
        class="inline-flex items-center gap-2 rounded-xl border border-brand-light/25 bg-white px-3 py-2 text-sm font-medium text-brand-darker transition no-underline shadow-sm underline-offset-2 hover:bg-brand-lighter/15 hover:underline"
      >
        <ChevronLeft class="h-4 w-4" />
        Back to My Lab-Results
      </RouterLink>
    </div>

    <div v-if="dataError">
      <StatusBanner :message="dataError" tone="error" />
    </div>

    <div v-if="isLoading" class="rounded-[1.75rem] border border-brand-light/20 bg-white p-6 shadow-sm">
      <p class="text-sm text-brand-dark">Loading lab-result details...</p>
    </div>

    <template v-else-if="labResult">
      <article class="overflow-hidden rounded-[1.9rem] border border-brand-light/20 bg-white shadow-sm">
        <div class="border-b border-brand-light/15 bg-linear-to-r from-white via-sky-50 to-brand-lighter/25 p-6">
          <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div class="min-w-0">
              <div
                class="inline-flex items-center gap-2 rounded-full bg-white/85 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-darker shadow-sm ring-1 ring-brand-light/15"
              >
                <FlaskConical class="h-3.5 w-3.5" />
                Lab Result Details
              </div>

              <h1 class="mt-4 text-2xl font-semibold tracking-tight text-brand-darker">
                {{ getRecordTitle(labResult) }}
              </h1>
              <p class="mt-3 max-w-3xl text-sm leading-6 text-brand-dark/80">
                {{ getRecordSummary(labResult) }}
              </p>
            </div>

            <div class="flex flex-wrap gap-3">
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
        </div>

        <div class="grid gap-6 p-6 xl:grid-cols-[0.95fr_1.05fr]">
          <aside class="space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <div class="flex items-start gap-3">
                <CalendarDays class="mt-0.5 h-4 w-4 text-brand-dark/70" />
                <div class="min-w-0">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">
                    Appointment Schedule
                  </p>
                  <p class="mt-2 text-sm font-medium text-slate-900">
                    {{
                      labResult.appointment
                        ? formatScheduleRange(
                            labResult.appointment.start_time,
                            labResult.appointment.end_time,
                          )
                        : "-"
                    }}
                  </p>
                </div>
              </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <div class="flex items-start gap-3">
                <FileText class="mt-0.5 h-4 w-4 text-brand-dark/70" />
                <div class="min-w-0">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">
                    Released At
                  </p>
                  <p class="mt-2 text-sm font-medium text-slate-900">
                    {{ formatReleasedAt(labResult.released_at) }}
                  </p>
                </div>
              </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <div class="flex items-start gap-3">
                <FileText class="mt-0.5 h-4 w-4 text-brand-dark/70" />
                <div class="min-w-0">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">
                    File Type
                  </p>
                  <p class="mt-2 text-sm font-medium text-slate-900">
                    {{ isPdf ? "PDF document" : "Image document" }}
                  </p>
                </div>
              </div>
            </div>
          </aside>

          <div class="rounded-2xl border border-brand-light/20 bg-slate-50 p-4">
            <div
              v-if="labResultUrl"
              class="overflow-hidden rounded-2xl border border-brand-light/15 bg-white"
            >
              <iframe
                v-if="isPdf"
                :src="labResultUrl"
                :title="`${getRecordTitle(labResult)} Preview`"
                class="h-[38rem] w-full bg-white"
              />
              <img
                v-else
                :src="labResultUrl"
                alt="Lab result preview"
                class="max-h-[38rem] w-full object-contain bg-white"
              />
            </div>

            <div
              v-else
              class="flex min-h-56 items-center justify-center rounded-2xl border border-dashed border-brand-light/30 bg-white px-6 text-center"
            >
              <div>
                <p class="text-sm font-semibold text-brand-darker">
                  Preview not opened yet
                </p>
                <p class="mt-2 text-sm leading-6 text-brand-dark/75">
                  Select <span class="font-medium">View File</span> to preview this result here, or use
                  <span class="font-medium">Download</span> to open it in a new tab.
                </p>
              </div>
            </div>
          </div>
        </div>
      </article>

      <StatusBanner
        v-if="labResultError"
        :message="labResultError"
        tone="error"
        @dismiss="labResultError = ''"
      />
    </template>

    <div
      v-else
      class="rounded-[1.9rem] border border-dashed border-brand-light/30 bg-white p-6 shadow-sm"
    >
      <div class="flex items-start gap-3">
        <CircleAlert class="mt-0.5 h-5 w-5 text-brand-dark/70" />
        <div>
          <p class="text-base font-semibold text-brand-darker">
            Lab result not found
          </p>
          <p class="mt-2 text-sm leading-6 text-brand-dark/80">
            This lab result may no longer be available or you may not have access to it.
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
