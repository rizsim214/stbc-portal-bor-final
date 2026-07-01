<script setup lang="ts">
import { useQuery } from "@tanstack/vue-query";
import {
  CalendarDays,
  ChevronRight,
  CircleAlert,
  FileText,
  FlaskConical,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import { appointmentsApi } from "@/features/appointment/api/appointmentsApi";
import AppointmentListPagination from "@/features/appointment/components/AppointmentListPagination.vue";
import type { LabResultListItem, PaginationMeta } from "@/features/appointment/types";
import { formatScheduleRange } from "@/features/appointment/utils/schedule";
import ListMeta from "@/shared/components/ListMeta/ListMeta.vue";
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";
import { Button } from "@/shared/ui/button";

const router = useRouter();
const page = ref(1);
const perPage = ref(10);

const labResultsQuery = useQuery({
  queryKey: ["lab-results", "mine", page, perPage],
  queryFn: async () => {
    const { data } = await appointmentsApi.listMyLabResults(page.value, perPage.value);
    return data;
  },
});

const labResults = computed<LabResultListItem[]>(() => labResultsQuery.data.value?.data ?? []);
const meta = computed<PaginationMeta>(() => labResultsQuery.data.value?.meta ?? {
  current_page: 1,
  last_page: 1,
  per_page: perPage.value,
  total: 0,
});
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

function getFileTypeLabel(filePath: string | null | undefined): string {
  return filePath?.toLowerCase().endsWith(".pdf")
    ? "PDF document"
    : "Image document";
}

function openLabResultDetail(labResultId: number): void {
  router.push({
    name: "userLabResultDetail",
    params: { labResultId: String(labResultId) },
  });
}

function setPage(nextPage: number): void {
  page.value = nextPage;
}
</script>

<template>
  <section class="space-y-6">
    <PageHeader
      title="My Lab-Results"
      subtitle="Browse all released lab results linked to your appointments. Open any item to view the full file details and download page."
      heading-tag="h1"
    />

    <StatusBanner
      v-if="dataError"
      :message="dataError"
      tone="error"
      @dismiss="void 0"
    />

    <ListMeta
      :shown-count="labResults.length"
      :total-count="meta.total"
      label="lab results"
      :is-loading="isLoading"
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

    <div v-else class="grid gap-5">
      <article
        v-for="labResult in labResults"
        :key="labResult.id"
        class="rounded-[1.8rem] border border-brand-light/20 bg-white p-5 shadow-sm transition hover:border-brand-light/35 hover:shadow-md"
      >
        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
          <div class="min-w-0 flex-1">
            <div
              class="inline-flex items-center gap-2 rounded-full bg-brand-lighter/20 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-darker"
            >
              <FlaskConical class="h-3.5 w-3.5" />
              Released Lab Result
            </div>

            <h2 class="mt-4 text-xl font-semibold tracking-tight text-brand-darker">
              {{ getRecordTitle(labResult) }}
            </h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-brand-dark/80">
              {{ getRecordSummary(labResult) }}
            </p>

            <div class="mt-4 grid gap-3 md:grid-cols-3">
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
                      {{ getFileTypeLabel(labResult.file_path) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="shrink-0">
            <Button
              type="button"
              class="w-auto bg-brand-dark text-white hover:bg-brand-darker"
              @click="openLabResultDetail(labResult.id)"
            >
              View Details
              <ChevronRight class="ml-2 h-4 w-4" />
            </Button>
          </div>
        </div>
      </article>

      <AppointmentListPagination
        :current-page="meta.current_page"
        :last-page="meta.last_page"
        @update:page="setPage"
      />
    </div>
  </section>
</template>
