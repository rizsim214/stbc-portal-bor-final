<script setup lang="ts">
import PatientDataTable from "@/features/patients/components/PatientDataTable/PatientDataTable.vue";

const patients = [
  { name: "Maria Dela Cruz", email: "maria.delacruz@example.com", status: "Active", lastVisit: "2026-05-12" },
  { name: "John Reyes", email: "john.reyes@example.com", status: "Active", lastVisit: "2026-05-09" },
  { name: "Patricia Gomez", email: "patricia.gomez@example.com", status: "Pending", lastVisit: "2026-04-30" },
  { name: "Ramon Santos", email: "ramon.santos@example.com", status: "Inactive", lastVisit: "2026-03-22" },
  { name: "Leah Navarro", email: "leah.navarro@example.com", status: "Active", lastVisit: "2026-05-02" },
  { name: "Simon Tan", email: "simon.tan@example.com", status: "Pending", lastVisit: "2026-04-18" },
];
</script>

<template>
  <section class="rounded-xl border border-brand-light/30 bg-white p-6">
    <h1 class="text-2xl font-semibold text-brand-darker">Patients List</h1>
    <p class="mt-2 text-sm text-brand-dark">Patient directory and quick actions.</p>
    <div class="mt-5">
      <PatientDataTable :patients="patients" />
    </div>
  </section>
</template>
