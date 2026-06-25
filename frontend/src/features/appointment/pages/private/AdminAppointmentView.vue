<script setup lang="ts">
import axios from "axios";
import {
  Activity,
  ArrowRight,
  Check,
  ChevronDown,
  ClipboardList,
  Clock3,
  Download,
  Dot,
  FileImage,
  FileText,
  FlaskConical,
  LoaderCircle,
  Mail,
  ScanEye,
  Sparkles,
  UserRoundCog,
} from "lucide-vue-next";
import {
  SelectContent,
  SelectIcon,
  SelectItem,
  SelectItemIndicator,
  SelectItemText,
  SelectPortal,
  SelectRoot,
  SelectTrigger,
  SelectValue,
  SelectViewport,
} from "radix-vue";
import { computed, ref } from "vue";
import { useRoute } from "vue-router";
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";
import { Button } from "@/shared/ui/button";
import { appointmentsApi } from "../../api/appointmentsApi";
import { useAppointmentDetailData } from "../../composables/useAppointmentDetailData";
import { formatScheduleRange } from "../../utils/schedule";
import {
  formatAppointmentStatus,
  getAppointmentStatusActionLabel,
  getAppointmentStatusClasses,
} from "../../utils/status";

const route = useRoute();
const appointmentId = computed(() => String(route.params.appointmentId ?? ""));
const {
  appointment,
  assignableResources,
  selectedResourceId,
  isLoadingAppointment,
  isLoadingResources,
  isAssigningResource,
  isUpdatingStatus,
  dataError,
  pageMessage,
  dismissPageState,
  assignSelectedResource,
  refreshAppointmentDetail,
  updateAppointmentStatus,
} = useAppointmentDetailData(appointmentId.value, "admin");

const nextStatus = computed(() => appointment.value?.allowed_next_statuses?.[0] ?? "");
const canAdvanceStatus = computed(() => Boolean(nextStatus.value));
const requiresStaffAssignment = computed(
  () => appointment.value?.status === "pending" && !appointment.value?.resources?.length,
);

const workflowStages = [
  { key: "pending", title: "Pending", note: "Waiting for staff assignment." },
  { key: "assigned", title: "Assigned", note: "Staff has been assigned." },
  { key: "checkup_ongoing", title: "Checkup Ongoing", note: "Procedure is in progress." },
  { key: "awaiting_result", title: "Awaiting Result", note: "Result is being processed." },
  { key: "releasing_lab_result", title: "Releasing Lab Result", note: "Result is being released." },
  { key: "completed", title: "Completed", note: "Workflow is complete." },
] as const;

const currentWorkflowIndex = computed(() =>
  workflowStages.findIndex((stage) => stage.key === appointment.value?.status),
);
const selectedLabResultFile = ref<File | null>(null);
const labUploadError = ref("");
const labUploadMessage = ref("");
const labResultUrl = ref("");
const isUploadingLabResult = ref(false);
const isLoadingLabResult = ref(false);
const acceptedLabResultTypes = [
  "application/pdf",
  "image/jpeg",
  "image/png",
] as const;
const acceptedLabResultTypesLabel = ".pdf,.jpg,.jpeg,.png";
const maxLabResultFileSizeBytes = 10 * 1024 * 1024;
const hasLabResult = computed(() => Boolean(appointment.value?.lab_result?.id));
const canUploadLabResult = computed(
  () => appointment.value?.status === "releasing_lab_result",
);
const requiresLabResultBeforeCompletion = computed(
  () =>
    appointment.value?.status === "releasing_lab_result" &&
    nextStatus.value === "completed" &&
    !hasLabResult.value,
);

function getWorkflowStageState(index: number): "complete" | "current" | "upcoming" {
  if (currentWorkflowIndex.value === -1) {
    return "upcoming";
  }

  if (index < currentWorkflowIndex.value) {
    return "complete";
  }

  if (index === currentWorkflowIndex.value) {
    return "current";
  }

  return "upcoming";
}

function getWorkflowStageClasses(index: number): string {
  const state = getWorkflowStageState(index);

  if (state === "complete") {
    return "border-emerald-200 bg-emerald-50 text-emerald-700";
  }

  if (state === "current") {
    return "border-brand-light/40 bg-brand-lighter/30 text-brand-darker shadow-sm";
  }

  return "border-slate-200 bg-white text-slate-500";
}

function clearLabResultState(): void {
  labUploadError.value = "";
  labUploadMessage.value = "";
}

function formatFileSize(sizeBytes: number): string {
  return `${(sizeBytes / (1024 * 1024)).toFixed(1)} MB`;
}

function onLabResultFileChange(event: Event): void {
  clearLabResultState();
  const input = event.target as HTMLInputElement;
  selectedLabResultFile.value = input.files?.[0] ?? null;
}

async function ensureLabResultUrl(): Promise<string> {
  if (!appointment.value?.lab_result?.id) {
    throw new Error("No uploaded lab result is available for this appointment.");
  }

  if (labResultUrl.value) {
    return labResultUrl.value;
  }

  isLoadingLabResult.value = true;
  labUploadError.value = "";

  try {
    const { data } = await appointmentsApi.getLabResultFileUrl(
      appointment.value.lab_result.id,
    );
    labResultUrl.value = data.data.download_url;
    return labResultUrl.value;
  } catch {
    labUploadError.value = "Unable to load the uploaded lab result.";
    throw new Error(labUploadError.value);
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

async function uploadLabResult(): Promise<void> {
  clearLabResultState();

  if (!appointment.value?.id) {
    labUploadError.value = "Appointment details are not available.";
    return;
  }

  if (!selectedLabResultFile.value) {
    labUploadError.value = "Please choose a PDF or scanned image file first.";
    return;
  }

  if (!acceptedLabResultTypes.includes(selectedLabResultFile.value.type as typeof acceptedLabResultTypes[number])) {
    labUploadError.value = "Only PDF, JPG, and PNG files are allowed.";
    return;
  }

  if (selectedLabResultFile.value.size > maxLabResultFileSizeBytes) {
    labUploadError.value = `File must be ${formatFileSize(maxLabResultFileSizeBytes)} or smaller.`;
    return;
  }

  isUploadingLabResult.value = true;

  try {
    const { data: uploadData } = await appointmentsApi.generateLabResultUploadUrl({
      appointment_id: appointment.value.id,
      file_name: selectedLabResultFile.value.name,
      content_type: selectedLabResultFile.value.type,
      size_bytes: selectedLabResultFile.value.size,
    });

    await axios.put(uploadData.data.upload_url, selectedLabResultFile.value, {
      headers: uploadData.data.headers,
    });

    await appointmentsApi.createLabResult({
      appointment_id: appointment.value.id,
      file_key: uploadData.data.file_key,
    });

    selectedLabResultFile.value = null;
    labResultUrl.value = "";
    labUploadMessage.value = "Lab result uploaded successfully.";
    await refreshAppointmentDetail();
  } catch (error) {
    if (axios.isAxiosError(error)) {
      labUploadError.value =
        error.response?.data?.message ?? "Unable to upload the lab result.";
      return;
    }

    labUploadError.value =
      error instanceof Error && error.message
        ? error.message
        : "Unable to upload the lab result.";
  } finally {
    isUploadingLabResult.value = false;
  }
}
</script>

<template>
  <section class="rounded-xl border border-brand-light/30 bg-white p-6">
    <PageHeader title="Appointment Details" subtitle="Manage assignment and progress." heading-tag="h1" />

    <StatusBanner v-if="dataError" :message="dataError" tone="error" @dismiss="dismissPageState" />
    <StatusBanner v-else-if="pageMessage" :message="pageMessage" tone="success" @dismiss="dismissPageState" />

    <div v-if="isLoadingAppointment" class="mt-6 text-sm text-brand-dark/75">
      Loading appointment details...
    </div>

    <div v-else-if="appointment" class="mt-6 space-y-6 overflow-hidden">
      <article
        class="relative overflow-hidden rounded-[1.75rem] border border-brand-light/20 bg-linear-to-br from-white via-sky-50 to-brand-lighter/35 p-6 shadow-sm">
        <div class="absolute right-0 top-0 h-28 w-28 rounded-full bg-brand-light/10 blur-3xl" />
        <div class="absolute bottom-0 left-8 h-24 w-24 rounded-full bg-sky-300/10 blur-3xl" />

        <div class="relative flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
          <div class="max-w-3xl space-y-4">
            <div class="flex flex-wrap items-center gap-2">
              <span
                class="inline-flex items-center gap-2 rounded-full bg-white/85 px-3 py-1 text-xs font-semibold tracking-[0.12em] text-brand-darker shadow-sm ring-1 ring-brand-light/15">
                <Sparkles class="h-3.5 w-3.5" />
                Appointment
              </span>
              <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                :class="getAppointmentStatusClasses(appointment.status)">
                {{ formatAppointmentStatus(appointment.status) }}
              </span>
            </div>

            <div>
              <h2 class="text-2xl font-semibold tracking-tight text-brand-darker md:text-3xl">
                {{ appointment.type?.name ?? "Appointment" }}
              </h2>
              <p class="mt-3 max-w-2xl text-sm leading-6 text-brand-dark/85">
                {{ appointment.type?.description ?? "No appointment type description available." }}
              </p>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
              <div class="rounded-2xl border border-white/80 bg-white/80 p-4 shadow-sm backdrop-blur">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Patient</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">{{ appointment.user?.name ?? "-" }}</p>
                <p class="mt-1 inline-flex items-center gap-2 text-xs text-slate-600">
                  <Mail class="h-3.5 w-3.5 text-brand-dark/70" />
                  {{ appointment.user?.email ?? "-" }}
                </p>
              </div>

              <div class="rounded-2xl border border-white/80 bg-white/80 p-4 shadow-sm backdrop-blur">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Schedule</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">{{ formatScheduleRange(appointment.start_time,
                  appointment.end_time) }}</p>
                <p class="mt-1 inline-flex items-center gap-2 text-xs text-slate-600">
                  <Clock3 class="h-3.5 w-3.5 text-brand-dark/70" />
                  Clinic appointment window
                </p>
              </div>

              <div class="rounded-2xl border border-white/80 bg-white/80 p-4 shadow-sm backdrop-blur">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Assigned Staff</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">
                  {{ appointment.resources?.length ? appointment.resources[0].name : "Unassigned" }}
                </p>
                <p class="mt-1 inline-flex items-center gap-2 text-xs text-slate-600">
                  <UserRoundCog class="h-3.5 w-3.5 text-brand-dark/70" />
                  {{ appointment.resources?.length ? appointment.resources[0].type : "Pending Assignment" }}
                </p>
              </div>
            </div>
          </div>

          <div
            class="relative w-full max-w-sm overflow-hidden rounded-[1.75rem] border border-brand-light/20 bg-linear-to-br from-white via-sky-50 to-brand-lighter/35 p-5 shadow-sm">
            <div class="absolute right-0 top-0 h-24 w-24 rounded-full bg-brand-light/10 blur-3xl" />
            <div class="absolute bottom-0 left-0 h-20 w-20 rounded-full bg-sky-300/10 blur-3xl" />

            <div class="relative">
              <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Next</p>
              <p class="mt-3 text-base font-semibold leading-4 text-slate-900">
                <span v-if="requiresStaffAssignment">Staff Assignment</span>
                <span v-else-if="canAdvanceStatus">{{ getAppointmentStatusActionLabel(appointment.status) }}</span>
                <span v-else>Completed</span>
              </p>
              <p class="mt-2 text-sm leading-6 text-slate-600">
                <span v-if="requiresStaffAssignment">Choose who handles this visit.</span>
                <span v-else-if="requiresLabResultBeforeCompletion">
                  Upload a lab result before completing this appointment.
                </span>
                <span v-else-if="canAdvanceStatus">Move to the next stage.</span>
                <span v-else>No action needed.</span>
              </p>
              <div class="mt-2 inline-flex items-center rounded-full border
             border-brand-light/20 bg-white/80 px-3 py-1 text-[11px] font-medium text-brand-dark/75 shadow-sm">
                {{ requiresStaffAssignment ?
                  "Assignment required"
                  : requiresLabResultBeforeCompletion
                    ? "Upload required"
                  : canAdvanceStatus
                    ? "Ready for update"
                    : "Final stage reached" }}
              </div>

              <div v-if="requiresStaffAssignment"
                class="mt-3 space-y-3 rounded-2xl border border-white/80 bg-white/90 p-3 shadow-sm backdrop-blur">
                <label for="select-staff-member" class="text-sm font-medium text-brand-darker">Select Specialist</label>
                <SelectRoot v-model="selectedResourceId" id="select-staff-member">
                  <SelectTrigger
                    class="inline-flex h-11 w-full items-center justify-between rounded-xl border border-brand-light/50 bg-white px-3 py-2 text-sm text-brand-darker outline-none transition focus:border-brand-highlight focus:ring-2 focus:ring-brand-highlight/40"
                    :disabled="isLoadingResources || !assignableResources.length">
                    <SelectValue :placeholder="isLoadingResources ? 'Loading staff...' : 'Select staff'" />
                    <SelectIcon>
                      <ChevronDown class="h-4 w-4 text-brand-dark/70" />
                    </SelectIcon>
                  </SelectTrigger>

                  <SelectPortal>
                    <SelectContent
                      class="z-80 min-w-(--radix-select-trigger-width) overflow-hidden rounded-xl border border-brand-light/30 bg-white shadow-xl"
                      position="popper" :side-offset="8">
                      <SelectViewport class="max-h-72 overflow-y-auto p-1">
                        <SelectItem v-for="resource in assignableResources" :key="resource.id"
                          :value="String(resource.id)" :disabled="!resource.is_available"
                          class="relative flex cursor-pointer select-none items-center rounded-lg px-3 py-2 text-sm text-brand-darker outline-none data-highlighted:bg-brand-lighter/35 data-[state=checked]:bg-brand-lighter/45 data-disabled:cursor-not-allowed data-disabled:opacity-50">
                          <div class="flex min-w-0 flex-col">
                            <SelectItemText>{{ resource.name }}</SelectItemText>
                            <span class="text-xs capitalize text-brand-dark/65">
                              {{ resource.type }}
                              {{ resource.is_available ? "" : " - unavailable" }}
                            </span>
                          </div>
                          <SelectItemIndicator class="ml-auto">
                            <Check class="h-4 w-4 text-brand-highlight" />
                          </SelectItemIndicator>
                        </SelectItem>
                      </SelectViewport>
                    </SelectContent>
                  </SelectPortal>
                </SelectRoot>

                <p v-if="!isLoadingResources && !assignableResources.length" class="text-xs text-brand-dark/70">
                  No staff found.
                </p>
                <p v-else-if="assignableResources.length && !assignableResources.some((resource) => resource.is_available)"
                  class="text-xs text-brand-dark/70">
                  No one is available for this slot.
                </p>

                <Button type="button" class="w-full bg-brand-dark text-white hover:bg-brand-darker"
                  :loading="isAssigningResource" @click="assignSelectedResource">
                  Update Staff
                </Button>
              </div>

              <div v-else
                class="mt-5 space-y-3 rounded-2xl border border-white/80 bg-white/90 p-4 shadow-sm backdrop-blur">
                <div class="flex items-start gap-3">
                  <ClipboardList class="mt-0.5 h-4 w-4 text-brand-dark" />
                  <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-900">Next step</p>
                    <p class="mt-1 text-sm text-slate-600">
                      <span v-if="requiresLabResultBeforeCompletion">
                        Upload a lab result before marking this appointment complete.
                      </span>
                      <span v-else-if="canAdvanceStatus">{{ getAppointmentStatusActionLabel(appointment.status) }}</span>
                      <span v-else-if="!requiresLabResultBeforeCompletion">No action needed.</span>
                    </p>
                  </div>
                </div>

                <Button v-if="canAdvanceStatus" type="button"
                  class="w-full bg-brand-dark text-white hover:bg-brand-darker" :loading="isUpdatingStatus"
                  :disabled="requiresLabResultBeforeCompletion"
                  @click="updateAppointmentStatus(nextStatus)">
                  <ArrowRight class="mr-2 h-4 w-4" />
                  {{ getAppointmentStatusActionLabel(appointment.status) }}
                </Button>
              </div>
            </div>
          </div>
        </div>
      </article>

      <aside class="rounded-3xl border border-brand-light/20 bg-linear-to-br from-white to-slate-50 p-6 shadow-sm"
        aria-label="description-section">
        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Description</p>
        <p class="mt-4 min-h-28 max-h-48 overflow-y-auto pr-2 text-sm leading-7 text-slate-700">
          {{ appointment?.notes ?? "No description provided." }}
        </p>
      </aside>

      <div class="grid gap-6 lg:grid-cols-[0.52fr_0.48fr]">

        <aside class="rounded-3xl border border-brand-light/20 bg-white p-6 shadow-sm" aria-label="lab-result-upload">
          <div class="flex items-start gap-3">
            <div class="rounded-2xl bg-brand-lighter/35 p-3 text-brand-darker">
              <FlaskConical class="h-5 w-5" />
            </div>
            <div class="min-w-0">
              <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Lab Result Upload</p>
              <h2 class="mt-2 text-lg font-semibold text-brand-darker">Attach result file</h2>
              <p class="mt-2 text-sm leading-6 text-brand-dark/80">
                Upload a PDF or scanned image for this appointment. Accepted formats are PDF, JPG, and PNG.
              </p>
            </div>
          </div>

          <div class="mt-5 space-y-4">
            <StatusBanner v-if="labUploadError" :message="labUploadError" tone="error" @dismiss="labUploadError = ''" />
            <StatusBanner v-if="labUploadMessage" :message="labUploadMessage" tone="success"
              @dismiss="labUploadMessage = ''" />

            <div v-if="hasLabResult" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <div class="flex items-start gap-3">
                <FileText class="mt-0.5 h-4 w-4 text-brand-dark/70" />
                <div class="min-w-0">
                  <p class="text-sm font-semibold text-slate-900">Lab result already uploaded</p>
                  <p class="mt-2 text-sm leading-6 text-slate-700">
                    This appointment already has a stored lab result file. View or download it below.
                  </p>
                </div>
              </div>

              <div class="mt-4 flex flex-wrap gap-3">
                <Button type="button" class="w-auto bg-brand-dark text-white hover:bg-brand-darker"
                  :disabled="isLoadingLabResult" @click="handleViewLabResult">
                  <LoaderCircle v-if="isLoadingLabResult" class="mr-2 h-4 w-4 animate-spin" />
                  <ScanEye v-else class="mr-2 h-4 w-4" />
                  View File
                </Button>

                <Button type="button" variant="outline"
                  class="w-auto border-brand-light/40 text-brand-darker hover:bg-brand-lighter/20"
                  :disabled="isLoadingLabResult" @click="handleDownloadLabResult">
                  <Download class="mr-2 h-4 w-4" />
                  Download
                </Button>
              </div>

              <div v-if="labResultUrl" class="mt-4 overflow-hidden rounded-2xl border border-brand-light/20 bg-white">
                <template v-if="appointment.lab_result?.file_path?.toLowerCase().endsWith('.pdf')">
                  <iframe :src="labResultUrl" title="Admin Lab Result Preview" class="h-96 w-full" />
                </template>
                <template v-else>
                  <img :src="labResultUrl" alt="Uploaded lab result" class="max-h-96 w-full object-contain" />
                </template>
              </div>
            </div>

            <div v-else class="space-y-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <div class="flex items-start gap-3">
                <FileImage class="mt-1.25 h-4 w-4 text-brand-dark/70" />
                <div class="min-w-0">
                  <label for="upload-lab-result" class="text-sm font-semibold text-slate-900 ">Choose result
                    file</label>
                  <p class="mt-2 text-sm leading-6 text-slate-700">
                    Upload one file per appointment. Use a PDF or scanned image under
                    {{ formatFileSize(maxLabResultFileSizeBytes) }}.
                  </p>
                  <p v-if="!canUploadLabResult" class="mt-2 text-sm leading-6 text-amber-600">
                    Upload is available only in Releasing Lab Result.
                  </p>
                </div>
              </div>

              <input type="file" :accept="acceptedLabResultTypesLabel" :disabled="!canUploadLabResult"
                class="block w-full rounded-xl border border-brand-light/40 bg-white px-3 py-2 text-sm text-brand-darker file:mr-3 file:rounded-lg file:border-0 file:bg-brand-lighter/35 file:px-3 file:py-2 file:text-sm file:font-medium file:text-brand-darker hover:file:bg-brand-lighter/45 disabled:cursor-not-allowed disabled:opacity-60"
                @change="onLabResultFileChange" id="upload-lab-result" />

              <p v-if="selectedLabResultFile" class="text-sm text-slate-700">
                Selected: <span class="font-medium text-slate-900">{{ selectedLabResultFile.name }}</span>
              </p>

              <Button type="button" class="w-full bg-brand-dark text-white hover:bg-brand-darker"
                :disabled="!canUploadLabResult" :loading="isUploadingLabResult" @click="uploadLabResult">
                Upload Lab Result
              </Button>
            </div>
          </div>
        </aside>

        <section class="rounded-3xl border border-brand-light/20 bg-white p-5 shadow-sm">
          <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
              <div class="rounded-2xl bg-brand-lighter/35 p-3 text-brand-darker">
                <ClipboardList class="h-5 w-5" />
              </div>
              <div>
                <h2 class="text-lg font-semibold text-brand-darker">Workflow</h2>
                <p class="mt-1 text-sm text-brand-dark/80">Track the current stage.</p>
              </div>
            </div>
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
              :class="getAppointmentStatusClasses(appointment.status)">
              {{ formatAppointmentStatus(appointment.status) }}
            </span>
          </div>

          <div class="mt-5 space-y-3">
            <article v-for="(stage, index) in workflowStages" :key="stage.key" :class="getWorkflowStageClasses(index)"
              class="rounded-2xl border p-3.5 transition">
              <div class="flex items-start gap-3">
                <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full" :class="getWorkflowStageState(index) === 'complete'
                  ? 'bg-emerald-600 text-white'
                  : getWorkflowStageState(index) === 'current'
                    ? 'bg-brand-dark text-white'
                    : 'bg-slate-200 text-slate-500'">
                  <Check v-if="getWorkflowStageState(index) === 'complete'" class="h-4 w-4" />
                  <Activity v-else-if="getWorkflowStageState(index) === 'current'" class="h-4 w-4" />
                  <Dot v-else class="h-4 w-4" />
                </div>

                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <p class="text-sm font-semibold">{{ stage.title }}</p>
                    <span v-if="getWorkflowStageState(index) === 'current'"
                      class="rounded-full bg-white/80 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-brand-darker">
                      Current
                    </span>
                  </div>
                  <p class="mt-1 text-xs leading-5 opacity-85">{{ stage.note }}</p>
                </div>
              </div>
            </article>
          </div>
        </section>
      </div>
    </div>
  </section>
</template>
