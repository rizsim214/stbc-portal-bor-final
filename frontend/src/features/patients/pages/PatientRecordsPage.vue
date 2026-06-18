<script setup lang="ts">
import { computed } from "vue";
import { ChevronLeft, FilePlusCorner } from "lucide-vue-next";

const props = defineProps<{
  userId: string;
}>();

const records = [
  {
    id: 101,
    name: "Maria Dela Cruz",
    visits: [
      { date: "2026-05-12", note: "Routine follow-up and medication refill." },
      { date: "2026-04-03", note: "Blood pressure review." },
      { date: "2026-02-18", note: "Vaccination and lab request." },
    ],
  },
  {
    id: 102,
    name: "John Reyes",
    visits: [
      { date: "2026-05-09", note: "Post-procedure review." },
      { date: "2026-03-14", note: "Condition monitoring." },
    ],
  },
  {
    id: 103,
    name: "Patricia Gomez",
    visits: [{ date: "2026-04-30", note: "Initial consultation." }],
  },
];

const record = computed(() => {
  const userId = Number(props.userId);
  return records.find((entry) => entry.id === userId) ?? records[0];
});

const isFrequentVisitor = computed(() => record.value.visits.length >= 3);
</script>

<template>
  <div class="mb-2 ">
    <RouterLink :to="{ name: 'userList' }"
      class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-brand-darker transition no-underline underline-offset-2 hover:underline  tracking-widest">
      <ChevronLeft class="h-4 w-4" />
      Back
    </RouterLink>
  </div>
  <section class="rounded-xl border border-brand-light/30 bg-white p-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <p class="text-xs uppercase tracking-[0.12em] text-brand-dark/65">User Records</p>
        <div class="mt-2 flex flex-wrap items-center gap-3">
          <h1 class="text-2xl font-semibold text-brand-darker">{{ record.name }}</h1>
          <span class="rounded-full px-3 py-1 text-xs font-medium"
            :class="isFrequentVisitor ? 'bg-brand-dark text-white' : 'bg-brand-lighter/50 text-brand-darker'">
            {{ isFrequentVisitor ? "Frequent Visitor" : "Standard Visit History" }}
          </span>
        </div>
      </div>

      <RouterLink to="#"
        class="inline-flex items-center gap-2 rounded-md bg-brand-dark border border-brand-light/40 px-3 py-2 text-sm font-medium text-white  transition hover:bg-brand-darker">
        <FilePlusCorner class="h-4 w-4" />
        Add Record
      </RouterLink>
    </div>
    <p class="mt-2 text-sm text-brand-dark">
      {{ isFrequentVisitor
        ? "Past visits are shown for continuity of care."
        : "Only the most recent record is shown for this patient." }}
    </p>

    <div class="mt-6 space-y-3">
      <div v-for="visit in record.visits" :key="visit.date"
        class="rounded-lg border border-brand-light/25 bg-brand-lighter/10 p-4">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-sm font-semibold text-brand-darker">{{ visit.date }}</p>
            <p class="mt-1 text-sm text-brand-dark">{{ visit.note }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
