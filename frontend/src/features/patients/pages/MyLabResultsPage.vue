<script setup lang="ts">
import { useQuery } from "@tanstack/vue-query";
import {
  CalendarDays,
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
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";
import { Button } from "@/shared/ui/button";

const activePreviewLabResultId = ref<number | null>(null);
const activePreviewUrl = ref("");
const labResultError = ref("");
const isLoadingLabResultId = ref<number | null>(null);

const labResultsQuery = useQuery({
  queryKey: ["lab-results", "mine"],
  queryFn: async () => {
    const { data } = await appointmentsApi.listMyLabResults();
    return data.data;
  },
});

const labResults = computed<LabResultListItem[]>(() => labResultsQuery.data.value ?? []);
const isLoading = computed(
  () => labResultsQuery.isPending.value || labResultsQuery.isFetching.value,
);
const dataError = computed(() => {
  if (!labResultsQuery.isError.value) {
    return "";
  }

  return labResultsQuery.error.value instanceof Error
    ? labResultsQuery.error.value.message
    : "Unable to load your lab results.";
});

function isPdfFile(filePath: string | null | undefined): boolean {
  return filePath?.toLowerCase().endsWith(".pdf") ?? false;
}

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

function getRecordTitle(labResult: LabResultListItem): string {
  return labResult.appointment?.type?.name ?? "Lab Result";
}

function getRecordSummary(labResult: LabResultListItem): string {
  return (
    labResult.appointment?.notes ??
    "No appointment note was recorded for this lab result."
  );
}

async function ensureLabResultUrl(labResult: LabResultListItem): Promise<string> {
  if (!labResult.id) {
    throw new Error("No lab result file is available for this record.");
  }

  if (
    activePreviewLabResultId.value === labResult.id &&
    activePreviewUrl.value
  ) {
    return activePreviewUrl.value;
  }

  isLoadingLabResultId.value = labResult.id;
  labResultError.value = "";

  try {
    const { data } = await appointmentsApi.getLabResultFileUrl(labResult.id);
    activePreviewLabResultId.value = labResult.id;
    activePreviewUrl.value = data.data.download_url;

    return activePreviewUrl.value;
  } catch {
    labResultError.value = "Unable to load the selected lab result file.";
    throw new Error(labResultError.value);
  } finally {
    isLoadingLabResultId.value = null;
  }
}

async function handleViewLabResult(labResult: LabResultListItem): Promise<void> {
  await ensureLabResultUrl(labResult);
}

async function handleDownloadLabResult(labResult: LabResultListItem): Promise<void> {
  const url = await ensureLabResultUrl(labResult);
  globalThis.open(url, "_blank", "noopener,noreferrer");
}
</script>

<template>
  <section class="space-y-6">
    <PageHeader
      title="My Lab-Results"
      subtitle="Review every released lab result associated with your appointments, then preview or download the file you need."
      heading-tag="h1"
    />

    <StatusBanner
      v-if="dataError"
      :message="dataError"
      tone="error"
      @dismiss="void 0"
    />
    <StatusBanner
      v-if="labResultError"
      :message="labResultError"
      tone="error"
      @dismiss="labResultError = ''"
    />

    <div
      v-if="isLoading"
      class="rounded-[1.75rem] border border-brand-light/20 bg-white p-6 text-sm text-brand-dark shadow-sm"
    >
      Loading your lab results...
    </div>

    <div
      v-else-if="labResults.length === 0"
      class="rounded-[1.9rem] border border-dashed border-brand-light/30 bg-white p-6 shadow-sm"
    >
      <div class="flex items-start gap-3">
        <CircleAlert class="mt-0.5 h-5 w-5 text-brand-dark/70" />
        <div>
          <p class="text-base font-semibold text-brand-darker">
            No released lab results yet
          </p>
          <p class="mt-2 text-sm leading-6 text-brand-dark/80">
            Your completed appointments with released lab result files will appear here automatically.
          </p>
        </div>
      </div>
    </div>

    <div v-else class="grid gap-6">
      <article
        v-for="labResult in labResults"
        :key="labResult.id"
        class="overflow-hidden rounded-[1.9rem] border border-brand-light/20 bg-white shadow-sm"
      >
        <div class="border-b border-brand-light/15 bg-linear-to-r from-white via-sky-50 to-brand-lighter/25 p-6">
          <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div class="min-w-0">
              <div
                class="inline-flex items-center gap-2 rounded-full bg-white/85 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-darker shadow-sm ring-1 ring-brand-light/15"
              >
                <FlaskConical class="h-3.5 w-3.5" />
                Released Lab Result
              </div>

              <h2 class="mt-4 text-2xl font-semibold tracking-tight text-brand-darker">
                {{ getRecordTitle(labResult) }}
              </h2>
              <p class="mt-3 max-w-3xl text-sm leading-6 text-brand-dark/80">
                {{ getRecordSummary(labResult) }}
              </p>
            </div>

            <div class="flex flex-wrap gap-3">
              <Button
                type="button"
                class="w-auto bg-brand-dark text-white hover:bg-brand-darker"
                :loading="isLoadingLabResultId === labResult.id"
                @click="handleViewLabResult(labResult)"
              >
                <ScanEye class="mr-2 h-4 w-4" />
                View File
              </Button>

              <Button
                type="button"
                variant="outline"
                class="w-auto border-brand-light/40 text-brand-darker hover:bg-brand-lighter/20"
                :disabled="isLoadingLabResultId === labResult.id"
                @click="handleDownloadLabResult(labResult)"
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
                    {{ isPdfFile(labResult.file_path) ? "PDF document" : "Image document" }}
                  </p>
                </div>
              </div>
            </div>
          </aside>

          <div class="rounded-2xl border border-brand-light/20 bg-slate-50 p-4">
            <div
              v-if="activePreviewLabResultId === labResult.id && activePreviewUrl"
              class="overflow-hidden rounded-2xl border border-brand-light/15 bg-white"
            >
              <iframe
                v-if="isPdfFile(labResult.file_path)"
                :src="activePreviewUrl"
                :title="`${getRecordTitle(labResult)} Preview`"
                class="h-[38rem] w-full bg-white"
              />
              <img
                v-else
                :src="activePreviewUrl"
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
    </div>
  </section>
</template>
