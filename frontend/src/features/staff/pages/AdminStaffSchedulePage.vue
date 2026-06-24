<script setup lang="ts">
import { computed } from "vue";
import { ChevronLeft } from "lucide-vue-next";
import { useRoute } from "vue-router";
import { useStaffSchedule } from "../composables/useStaffSchedule";
import StaffScheduleEditorCard from "../components/StaffScheduleEditorCard.vue";

const route = useRoute();
const userId = computed(() => String(route.params.userId ?? ""));

const {
  schedule,
  draftDays,
  isLoadingSchedule,
  scheduleError,
  pageMessage,
  isSavingSchedule,
  setDraftDay,
  saveSchedule,
  clearPageMessage,
} = useStaffSchedule("admin", userId.value);

function dismissBanner(): void {
  clearPageMessage();
}
</script>

<template>
  <div class="space-y-4">
    <RouterLink
      :to="{ name: 'userManagement' }"
      class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-brand-darker transition hover:underline"
    >
      <ChevronLeft class="h-4 w-4" />
      Back to user administration
    </RouterLink>

    <StaffScheduleEditorCard
      :title="`Staff Schedule${schedule?.user?.name ? `: ${schedule.user.name}` : ''}`"
      :subtitle="schedule?.user?.sub_role
        ? `Adjust the weekly assignment hours for this ${schedule.user.sub_role.toLowerCase()}.`
        : 'Adjust the weekly assignment hours for this staff account.'"
      :days="draftDays"
      :is-loading="isLoadingSchedule"
      :is-saving="isSavingSchedule"
      :error="scheduleError"
      :message="pageMessage"
      @dismiss-message="dismissBanner"
      @save="saveSchedule"
      @update-day="setDraftDay"
    />
  </div>
</template>
