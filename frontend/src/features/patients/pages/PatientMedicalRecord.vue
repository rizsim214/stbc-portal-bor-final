<script setup lang="ts">
import { computed } from "vue";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";

const authStore = useAuthStore();

const visits = [
  { date: "2026-05-12", note: "Routine follow-up and medication refill." },
  { date: "2026-04-03", note: "Blood pressure review." },
  { date: "2026-02-18", note: "Vaccination and lab request." },
];

const isFrequentVisitor = computed(() => visits.length >= 3);
</script>

<template>
  <section class="rounded-xl border border-brand-light/30 bg-white p-6">
    <p class="text-xs uppercase tracking-[0.12em] text-brand-dark/65">My Record</p>
    <h1 class="mt-2 text-2xl font-semibold text-brand-darker">
      {{ authStore.user?.name ?? "Patient" }}
    </h1>
    <p class="mt-2 text-sm text-brand-dark">
      {{ isFrequentVisitor ? "Your past visits are available below." : "Your latest record is available below." }}
    </p>

    <div class="mt-6 space-y-3">
      <div
        v-for="visit in visits"
        :key="visit.date"
        class="rounded-lg border border-brand-light/25 bg-brand-lighter/10 p-4"
      >
        <p class="text-sm font-semibold text-brand-darker">{{ visit.date }}</p>
        <p class="mt-1 text-sm text-brand-dark">{{ visit.note }}</p>
      </div>
    </div>
  </section>
</template>
