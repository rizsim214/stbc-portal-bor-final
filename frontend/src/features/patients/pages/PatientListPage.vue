<script setup lang="ts">
import PatientDataTable from "@/features/patients/components/PatientDataTable/PatientDataTable.vue";
import PatientTableFilters from "@/features/patients/components/PatientTableFilters/PatientTableFilters.vue";
import { computed, ref } from "vue";

const users = [
  { id: 101, name: "Maria Dela Cruz", email: "maria.delacruz@example.com", status: "Active", lastVisit: "2026-05-12" },
  { id: 102, name: "John Reyes", email: "john.reyes@example.com", status: "Active", lastVisit: "2026-05-09" },
  { id: 103, name: "Patricia Gomez", email: "patricia.gomez@example.com", status: "Pending", lastVisit: "2026-04-30" },
  { id: 104, name: "Ramon Santos", email: "ramon.santos@example.com", status: "Inactive", lastVisit: "2026-03-22" },
  { id: 105, name: "Leah Navarro", email: "leah.navarro@example.com", status: "Active", lastVisit: "2026-05-02" },
  { id: 106, name: "Simon Tan", email: "simon.tan@example.com", status: "Pending", lastVisit: "2026-04-18" },
];

const searchTerm = ref("");
const searchField = ref<"name" | "email" | "status">("name");
const statusFilter = ref("all");

const availableStatuses = computed(() =>
  Array.from(new Set(users.map((user) => user.status))),
);

const filteredUsers = computed(() => {
  const query = searchTerm.value.trim().toLowerCase();

  return users.filter((user) => {
    const searchableValue = user[searchField.value].toLowerCase();
    const matchesQuery = !query || searchableValue.includes(query);
    const matchesStatus = statusFilter.value === "all" || user.status === statusFilter.value;
    return matchesQuery && matchesStatus;
  });
});
</script>

<template>
  <section class="rounded-xl border border-brand-light/30 bg-white p-6">
    <h1 class="text-2xl font-semibold text-brand-darker">User List</h1>
    <p class="mt-2 text-sm text-brand-dark">User directory and quick actions.</p>
    <div class="mt-5">
      <PatientTableFilters
        :search-term="searchTerm"
        :search-field="searchField"
        :status-filter="statusFilter"
        :available-statuses="availableStatuses"
        @update:search-term="searchTerm = $event"
        @update:search-field="searchField = $event"
        @update:status-filter="statusFilter = $event"
      />
      <PatientDataTable :patients="filteredUsers" />
    </div>
  </section>
</template>
