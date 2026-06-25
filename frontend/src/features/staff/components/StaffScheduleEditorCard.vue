<script setup lang="ts">
import { computed } from "vue";
import { Clock3, Save } from "lucide-vue-next";
import type { StaffScheduleDay } from "../types";
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";
import { Button } from "@/shared/ui/button";
import { Input } from "@/shared/ui/input";

const DAY_LABELS = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"] as const;

const props = defineProps<{
  title: string;
  subtitle: string;
  days: StaffScheduleDay[];
  isLoading: boolean;
  isSaving: boolean;
  error: string;
  message: string;
}>();

const emit = defineEmits<{
  (e: "update-day", dayOfWeek: number, updates: Partial<StaffScheduleDay>): void;
  (e: "save"): void;
  (e: "dismiss-message"): void;
}>();

const orderedDays = computed(() =>
  [...props.days].sort((left, right) => left.day_of_week - right.day_of_week),
);

function onAvailabilityToggle(dayOfWeek: number, event: Event): void {
  const checked = (event.target as HTMLInputElement).checked;
  emit("update-day", dayOfWeek, { is_enabled: checked });
}
</script>

<template>
  <section class="rounded-[1.5rem] border border-brand-light/20 bg-white p-6 shadow-sm">
    <PageHeader :title="title" :subtitle="subtitle" />

    <StatusBanner
      v-if="error || message"
      :message="error || message"
      :tone="error ? 'error' : 'success'"
      @dismiss="emit('dismiss-message')"
    />

    <div v-if="isLoading" class="mt-4 text-sm text-brand-dark/75">
      Loading weekly schedule...
    </div>

    <div v-else class="mt-4 space-y-3">
      <article
        v-for="day in orderedDays"
        :key="day.day_of_week"
        class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
      >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-900">{{ DAY_LABELS[day.day_of_week] }}</p>
            <p class="mt-1 text-xs text-slate-600">
              {{ day.is_enabled ? "Assignments can be matched during this time range." : "Marked unavailable for the whole day." }}
            </p>
          </div>

          <div class="flex flex-col gap-3 md:flex-row md:items-end">
            <label class="inline-flex items-center gap-2 text-sm font-medium text-brand-darker">
              <input
                :checked="day.is_enabled"
                type="checkbox"
                class="h-4 w-4 rounded border-brand-light/50 text-brand-dark focus:ring-brand-highlight/40"
                @change="onAvailabilityToggle(day.day_of_week, $event)"
              />
              Available
            </label>

            <Input
              :model-value="day.start_time ?? ''"
              type="time"
              label="Start"
              class="md:w-36"
              :disabled="!day.is_enabled"
              @update:model-value="emit('update-day', day.day_of_week, { start_time: $event })"
            />

            <Input
              :model-value="day.end_time ?? ''"
              type="time"
              label="End"
              class="md:w-36"
              :disabled="!day.is_enabled"
              @update:model-value="emit('update-day', day.day_of_week, { end_time: $event })"
            />
          </div>
        </div>
      </article>

      <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-4 text-sm text-slate-600">
        <div class="flex items-start gap-3">
          <Clock3 class="mt-0.5 h-4 w-4 shrink-0 text-brand-dark" />
          <p>
            These weekly hours define when appointment assignments are allowed for this staff resource.
          </p>
        </div>
      </div>

      <div class="flex justify-end">
        <Button
          type="button"
          class="w-auto bg-brand-dark text-white hover:bg-brand-darker"
          :loading="isSaving"
          @click="emit('save')"
        >
          <Save class="mr-2 h-4 w-4" />
          Save weekly schedule
        </Button>
      </div>
    </div>
  </section>
</template>
