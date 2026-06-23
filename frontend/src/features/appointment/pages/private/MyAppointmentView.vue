<script setup lang="ts">
import { ChevronLeft, ChevronRight } from "lucide-vue-next";
import { computed, ref } from "vue";
import { useRoute } from "vue-router";
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";
import { Button } from "@/shared/ui/button";
import AppointmentEditFormCard from "../../components/AppointmentEditFormCard.vue";
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
  isEditFormVisible.value ? "lg:grid-cols-[1.1fr_0.9fr]" : "lg:grid-cols-1",
);

</script>
<template>
  <section class="rounded-xl border border-brand-light/30 bg-white p-6">
    <PageHeader title="Appointment Details" subtitle="View the full details of your appointment request."
      heading-tag="h1">
      <template #actions>
        <Button type="button" variant="outline"
          class="border-brand-light/40 text-brand-darker hover:bg-brand-lighter/20"
          @click="isEditFormVisible = !isEditFormVisible">
          <ChevronLeft v-if="!isEditFormVisible" class="mr-2 h-4 w-4" />
          <ChevronRight v-else class="mr-2 h-4 w-4" />
          {{ isEditFormVisible ? "Hide" : "Show" }}
        </Button>
      </template>
    </PageHeader>

    <StatusBanner v-if="dataError" :message="dataError" tone="error" @dismiss="dismissPageState" />
    <StatusBanner v-else-if="pageMessage" :message="pageMessage" tone="success" @dismiss="dismissPageState" />

    <div v-if="isLoadingAppointment" class="mt-6 text-sm text-brand-dark/75">
      Loading appointment details...
    </div>

    <div v-else-if="appointment" :class="appointmentGridClass" class="mt-6 grid gap-6 overflow-hidden">
      <article class="rounded-2xl border border-brand-light/25 bg-brand-lighter/10 p-5">
        <h2 class="text-lg font-semibold text-brand-darker">{{ appointment.type?.name ?? "Appointment" }}</h2>
        <p class="mt-2 text-sm text-brand-dark/80">
          {{ appointment.type?.description ?? "No appointment type description available." }}
        </p>
        <dl class="mt-4 space-y-3 text-sm text-brand-dark/85">
          <div>
            <dt class="font-medium text-brand-darker">Schedule</dt>
            <dd>{{ formatScheduleRange(appointment.start_time, appointment.end_time) }}</dd>
          </div>
          <div>
            <dt class="font-medium text-brand-darker">Status</dt>
            <dd class="mt-2">
              <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                :class="getAppointmentStatusClasses(appointment.status)">
                {{ formatAppointmentStatus(appointment.status) }}
              </span>
            </dd>
          </div>
          <div>
            <dt class="font-medium text-brand-darker">Staff In-Charge</dt>
            <dd v-if="appointment.resources?.length">
              {{appointment.resources.map((resource) => `${resource.name} (${resource.type})`).join(", ")}}
            </dd>
            <dd v-else>Not yet assigned a clinic staff.</dd>
          </div>
          <div>
            <dt class="font-medium text-brand-darker">Appointment Description</dt>
            <dd>{{ appointment.notes ?? "No appointment description provided." }}</dd>
          </div>
        </dl>
      </article>

      <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="translate-x-10 opacity-0"
        enter-to-class="translate-x-0 opacity-100" leave-active-class="transition-all duration-250 ease-in"
        leave-from-class="translate-x-0 opacity-100" leave-to-class="translate-x-10 opacity-0">
        <AppointmentEditFormCard v-if="isEditFormVisible" :appointment-types="appointmentTypes"
          :available-time-options="availableTimeOptions" :date-placeholder="datePlaceholder" :form="editForm"
          :is-loading-availability="isLoadingAvailability" :is-loading-appointment-types="isLoadingAppointmentTypes"
          :is-submitting="isUpdatingAppointment" :selected-date="selectedEditDate"
          @update:selected-date="setSelectedEditDate" @save="updateAppointmentDetails" />
      </Transition>
    </div>
  </section>
</template>
