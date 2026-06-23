<script setup lang="ts">
import { Check, ChevronDown, ChevronLeft, ChevronRight } from "lucide-vue-next";
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
import { useAppointmentDetailData } from "../../composables/useAppointmentDetailData";
import { formatScheduleRange } from "../../utils/schedule";
import { formatAppointmentStatus, getAppointmentStatusClasses } from "../../utils/status";

const route = useRoute();
const appointmentId = computed(() => String(route.params.appointmentId ?? ""));
const {
  appointment,
  assignableResources,
  selectedResourceId,
  isLoadingAppointment,
  isLoadingResources,
  isAssigningResource,
  dataError,
  pageMessage,
  dismissPageState,
  assignSelectedResource,
} = useAppointmentDetailData(appointmentId.value, "admin");
const isAssignmentVisible = ref(false);
const appointmentGridClass = computed(() =>
  isAssignmentVisible.value ? "xl:grid-cols-[1.3fr_0.8fr]" : "xl:grid-cols-1",
);
</script>

<template>
  <section class="rounded-xl border border-brand-light/30 bg-white p-6">
    <PageHeader title="Appointment Details" subtitle="Review the request and assign the appropriate staff member."
      heading-tag="h1">
      <template #actions>
        <Button type="button" variant="outline"
          class="border-brand-light/40 text-brand-darker hover:bg-brand-lighter/20"
          @click="isAssignmentVisible = !isAssignmentVisible">
          <ChevronLeft v-if="!isAssignmentVisible" class="mr-2 h-4 w-4" />
          <ChevronRight v-else class="mr-2 h-4 w-4" />
          {{ isAssignmentVisible ? "Hide" : "Show" }}
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
            <dt class="font-medium text-brand-darker">Patient</dt>
            <dd>{{ appointment.user?.name ?? "-" }}</dd>
            <dd>{{ appointment.user?.email ?? "-" }}</dd>
          </div>
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
            <dt class="font-medium text-brand-darker">Assigned Staff</dt>
            <dd v-if="appointment.resources?.length">
              {{appointment.resources.map((resource) => `${resource.name} (${resource.type})`).join(", ")}}
            </dd>
            <dd v-else>Not assigned yet</dd>
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
        <aside v-if="isAssignmentVisible" class="rounded-2xl border border-brand-light/25 bg-white p-5 shadow-sm">
          <h2 class="text-lg font-semibold text-brand-darker">Staff Assignment</h2>
          <p class="mt-2 text-sm text-brand-dark/80">
            Choose the doctor, radiologist, nurse, or other clinic staff who will handle this appointment.
          </p>

          <div class="mt-4 space-y-2">
            <label for="select-staff-member" class="text-sm font-medium text-brand-darker">Staff Member</label>
            <SelectRoot v-model="selectedResourceId" id="select-staff-member">
              <SelectTrigger
                class="inline-flex h-10 w-full items-center justify-between rounded-md border border-brand-light/50 bg-white px-3 py-2 text-sm text-brand-darker outline-none transition focus:border-brand-highlight focus:ring-2 focus:ring-brand-highlight/40"
                :disabled="isLoadingResources || !assignableResources.length">
                <SelectValue :placeholder="isLoadingResources ? 'Loading staff...' : 'Select staff member'" />
                <SelectIcon>
                  <ChevronDown class="h-4 w-4 text-brand-dark/70" />
                </SelectIcon>
              </SelectTrigger>

              <SelectPortal>
                <SelectContent
                  class="z-80 min-w-(--radix-select-trigger-width) overflow-hidden rounded-xl border border-brand-light/30 bg-white shadow-xl"
                  position="popper" :side-offset="8">
                  <SelectViewport class="max-h-72 overflow-y-auto p-1">
                    <SelectItem v-for="resource in assignableResources" :key="resource.id" :value="String(resource.id)"
                      class="relative flex cursor-pointer select-none items-center rounded-lg px-3 py-2 text-sm text-brand-darker outline-none data-highlighted:bg-brand-lighter/35 data-[state=checked]:bg-brand-lighter/45">
                      <div class="flex min-w-0 flex-col">
                        <SelectItemText>{{ resource.name }}</SelectItemText>
                        <span class="text-xs capitalize text-brand-dark/65">{{ resource.type }}</span>
                      </div>
                      <SelectItemIndicator class="ml-auto">
                        <Check class="h-4 w-4 text-brand-highlight" />
                      </SelectItemIndicator>
                    </SelectItem>
                  </SelectViewport>
                </SelectContent>
              </SelectPortal>
            </SelectRoot>
          </div>

          <div class="mt-5">
            <Button type="button" class="w-full bg-brand-dark text-white hover:bg-brand-darker"
              :loading="isAssigningResource" @click="assignSelectedResource">
              Update Assigned Staff
            </Button>
          </div>
        </aside>
      </Transition>
    </div>
  </section>
</template>
