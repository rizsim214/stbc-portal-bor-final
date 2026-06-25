<script setup lang="ts">
import {
  CalendarClock,
  CalendarCheck,
  ChevronLeft,
  ChevronRight,
  ClipboardList,
  Download,
  FileText,
  FileUp,
  FlaskConical,
  LoaderCircle,
  ScanEye,
  Stethoscope,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import { useRoute } from "vue-router";
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";
import { Button } from "@/shared/ui/button";
import AppointmentEditFormCard from "../../components/AppointmentEditFormCard.vue";
import { appointmentsApi } from "../../api/appointmentsApi";
import { useAppointmentDetailData } from "../../composables/useAppointmentDetailData";
import { formatScheduleRange } from "../../utils/schedule";
import { formatAppointmentStatus, getAppointmentStatusClasses } from "../../utils/status";

const route = useRoute();
const appointmentId = computed(() => String(route.params.appointmentId ?? ""));
const {
  appointment,
  appointmentTypes,
  editForm,
  selectedEditDate,
  datePlaceholder,
  availableTimeOptions,
  isLoadingAppointment,
  isLoadingAppointmentTypes,
  isLoadingAvailability,
  isUpdatingAppointment,
  dataError,
  pageMessage,
  dismissPageState,
  setSelectedEditDate,
  updateAppointmentDetails,
} = useAppointmentDetailData(appointmentId.value, "patient");
const isEditFormVisible = ref(false);
const appointmentGridClass = computed(() =>
  isEditFormVisible.value ? "xl:grid-cols-[1.2fr_0.8fr]" : "xl:grid-cols-1",
);
const appointmentStatusLabel = computed(() =>
  appointment.value ? formatAppointmentStatus(appointment.value.status) : "",
);
const labResultUrl = ref("");
const labResultError = ref("");
const isLoadingLabResult = ref(false);
const hasReleasedLabResult = computed(() =>
  Boolean(appointment.value?.lab_result?.id && appointment.value?.lab_result?.released_at),
);

async function handleUpdateAppointmentDetails(): Promise<void> {
  await updateAppointmentDetails();
  isEditFormVisible.value = false;
}

async function ensureLabResultUrl(): Promise<string> {
  if (!appointment.value?.lab_result?.id) {
    throw new Error("No released lab result is available for this appointment.");
  }

  if (labResultUrl.value) {
    return labResultUrl.value;
  }

  isLoadingLabResult.value = true;
  labResultError.value = "";

  try {
    const { data } = await appointmentsApi.getLabResultFileUrl(
      appointment.value.lab_result.id,
    );
    labResultUrl.value = data.data.download_url;
    return labResultUrl.value;
  } catch {
    labResultError.value = "Unable to load the lab result PDF.";
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
    <PageHeader title="Appointment Details"
      subtitle="Review the schedule, status, and notes for your appointment request in one clear view."
      heading-tag="h1">
      <template #actions>
        <Button type="button" variant="outline"
          class="border-brand-light/40 text-brand-darker hover:bg-brand-lighter/20"
          @click="isEditFormVisible = !isEditFormVisible">
          <ChevronLeft v-if="!isEditFormVisible" class="mr-2 h-4 w-4" />
          <ChevronRight v-else class="mr-2 h-4 w-4" />
          {{ isEditFormVisible ? "Cancel" : "Update" }}
        </Button>
      </template>
    </PageHeader>

    <StatusBanner v-if="dataError" :message="dataError" tone="error" @dismiss="dismissPageState" />
    <StatusBanner v-else-if="pageMessage" :message="pageMessage" tone="success" @dismiss="dismissPageState" />

    <div v-if="isLoadingAppointment"
      class="rounded-[1.75rem] border border-brand-light/25 bg-white p-6 text-sm text-brand-dark/75 shadow-sm">
      Loading appointment details...
    </div>

    <div v-else-if="appointment" :class="appointmentGridClass" class="grid gap-6 overflow-hidden">
      <div class="space-y-6">
        <article
          class="relative overflow-hidden rounded-[1.9rem] border border-brand-light/25 bg-linear-to-br from-white via-sky-50 to-brand-lighter/35 p-6 shadow-sm">
          <div class="absolute right-0 top-0 h-36 w-36 rounded-full bg-brand-light/10 blur-3xl" />
          <div class="absolute bottom-0 left-10 h-28 w-28 rounded-full bg-sky-300/10 blur-3xl" />

          <div class="relative space-y-6">
            <div
              class="inline-flex items-center gap-2 rounded-full bg-white/85 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-darker shadow-sm ring-1 ring-brand-light/15">
              <ClipboardList class="h-3.5 w-3.5" />
              Request Overview
            </div>

            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
              <div class="min-w-0">
                <div class="flex items-center gap-4">
                  <div
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-dark text-white shadow-[0_18px_40px_-28px_rgba(15,23,42,0.9)]">
                    <Stethoscope class="h-6 w-6" />
                  </div>

                  <div class="min-w-0">
                    <h2 class="text-2xl font-semibold tracking-tight text-brand-darker md:text-3xl">
                      {{ appointment.type?.name ?? "Appointment" }}
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-brand-dark/80">
                      {{ appointment.type?.description ?? "No appointment type description available." }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="shrink-0">
                <span class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.12em]"
                  :class="getAppointmentStatusClasses(appointment.status)">
                  {{ appointmentStatusLabel }}
                </span>
              </div>
            </div>

          </div>
        </article>

        <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
          <article class="rounded-[1.75rem] border border-brand-light/20 bg-white p-5 shadow-sm">
            <div class="flex items-start gap-3">
              <div class="rounded-2xl bg-brand-lighter/35 p-3 text-brand-darker">
                <CalendarClock class="h-5 w-5" />
              </div>
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">
                  Visit Details
                </p>
                <h3 class="mt-2 text-lg font-semibold tracking-tight text-brand-darker">
                  Schedule and request notes
                </h3>
              </div>
            </div>

            <dl class="mt-5 space-y-4">
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-start gap-3">
                  <CalendarCheck class="mt-0.5 h-4 w-4 text-brand-dark/70" />
                  <div class="min-w-0">
                    <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">
                      Appointment Schedule
                    </dt>
                    <dd class="mt-2 text-sm leading-6 text-slate-800">
                      {{ formatScheduleRange(appointment.start_time, appointment.end_time) }}
                    </dd>
                  </div>
                </div>
              </div>
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-start gap-3">
                  <FileText class="mt-0.5 h-4 w-4 text-brand-dark/70" />
                  <div class="min-w-0">
                    <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">
                      Appointment Description
                    </dt>
                    <dd class="mt-2 text-sm leading-6 text-slate-800">
                      {{ appointment.notes ?? "No appointment description provided." }}
                    </dd>
                  </div>
                </div>
              </div>
            </dl>
          </article>

          <article class="rounded-[1.75rem] border border-brand-light/20 bg-white p-5 shadow-sm">
            <div class="flex items-start gap-3">
              <div class="rounded-2xl bg-brand-lighter/35 p-3 text-brand-darker">
                <FlaskConical class="h-5 w-5" />
              </div>
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">
                  Lab Result
                </p>
                <h3 class="mt-2 text-lg font-semibold tracking-tight text-brand-darker">
                  View and download your PDF
                </h3>
              </div>
            </div>

            <div class="mt-5 space-y-4">
              <div v-if="hasReleasedLabResult" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex flex-wrap gap-3">
                  <Button type="button" class="w-auto bg-brand-dark text-white hover:bg-brand-darker"
                    :disabled="isLoadingLabResult" @click="handleViewLabResult">
                    <LoaderCircle v-if="isLoadingLabResult" class="mr-2 h-4 w-4 animate-spin" />
                    <ScanEye v-else class="mr-2 h-4 w-4" />
                    View PDF
                  </Button>

                  <Button type="button" variant="outline"
                    class="w-auto border-brand-light/40 text-brand-darker hover:bg-brand-lighter/20"
                    :disabled="isLoadingLabResult" @click="handleDownloadLabResult">
                    <Download class="mr-2 h-4 w-4" />
                    Download PDF
                  </Button>
                </div>

                <p v-if="labResultError" class="mt-3 text-sm text-red-600">
                  {{ labResultError }}
                </p>

                <div v-if="labResultUrl" class="mt-4 overflow-hidden rounded-2xl border border-brand-light/20 bg-white">
                  <iframe :src="labResultUrl" title="Lab Result PDF Preview" class="h-[28rem] w-full" />
                </div>
              </div>

              <div v-else class="rounded-2xl border border-dashed border-brand-light/30 bg-brand-lighter/10 p-5">
                <div class="flex items-start gap-3">
                  <FileUp class="mt-0.5 h-4 w-4 text-brand-dark/70" />
                  <div>
                    <p class="text-sm font-semibold text-brand-darker">
                      No released PDF yet
                    </p>
                    <p class="mt-2 text-sm leading-6 text-brand-dark/80">
                      Your lab result PDF will appear here once the clinic releases it to your account.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </article>
        </div>
      </div>

      <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="translate-x-10 opacity-0"
        enter-to-class="translate-x-0 opacity-100" leave-active-class="transition-all duration-250 ease-in"
        leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-10 opacity-0">
        <div v-if="isEditFormVisible" class="xl:sticky xl:top-6 xl:self-start">
          <AppointmentEditFormCard :appointment-types="appointmentTypes" :available-time-options="availableTimeOptions"
            :date-placeholder="datePlaceholder" :form="editForm" :is-loading-availability="isLoadingAvailability"
            :is-loading-appointment-types="isLoadingAppointmentTypes" :is-submitting="isUpdatingAppointment"
            :selected-date="selectedEditDate" @update:selected-date="setSelectedEditDate"
            @save="handleUpdateAppointmentDetails" />
        </div>
      </Transition>
    </div>
  </section>
</template>
