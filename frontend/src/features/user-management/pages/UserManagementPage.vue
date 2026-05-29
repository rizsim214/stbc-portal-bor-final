<script setup lang="ts">
import DataTable from '@/features/user-management/components/UserDataTable/UserDataTable.vue';
import UserTableFilters from "@/features/user-management/components/UserTableFilters/UserTableFilters.vue";
import { Button } from "@/shared/ui/button";
import { Plus } from "lucide-vue-next";
import { computed, ref } from "vue";

const users = [
  { name: 'Alice Johnson', email: 'alice.johnson@stbc.com', role: 'Doctor' },
  { name: 'Brian Cruz', email: 'brian.cruz@stbc.com', role: 'Doctor' },
  { name: 'Carla Santos', email: 'carla.santos@stbc.com', role: 'Radiologist' },
  { name: 'Santos Lee', email: 'santos.lee@stbc.com', role: 'Nurse' },
  { name: 'Daniel Cruz', email: 'daniel.cruz@stbc.com', role: 'Nurse' },
  { name: 'Brian Lee', email: 'brian.lee@stbc.com', role: 'Doctor' },
  { name: 'Elaine Cruz', email: 'elaine.cruz@stbc.com', role: 'Secretary' },
  { name: 'Francis Tan', email: 'francis.tan@stbc.com', role: 'Lab Operator' },
];

const searchTerm = ref("");
const searchField = ref<"name" | "email" | "role">("name");
const positionFilter = ref("all");

const availablePositions = computed(() =>
  Array.from(new Set(users.map((user) => user.role))),
);

const filteredUsers = computed(() => {
  const query = searchTerm.value.trim().toLowerCase();

  return users.filter((user) => {
    const matchesQuery = !query || user[searchField.value].toLowerCase().includes(query);
    const matchesPosition = positionFilter.value === "all" || user.role === positionFilter.value;
    return matchesQuery && matchesPosition;
  });
});
</script>

<template>
  <section class="rounded-xl border border-brand-light/30 bg-white p-5 shadow-[0_12px_28px_-18px_rgba(21,5,120,0.35)]">
    <div class="mb-4 flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold text-brand-darker">User Administration</h2>
        <p class="text-xs text-brand-dark/80">Manage staff and admin access.</p>
      </div>
      <Button class="w-auto bg-brand-dark hover:bg-brand-darker">
        <Plus class="mr-1 h-4 w-4" />
        Add User
      </Button>
    </div>
    <UserTableFilters :search-term="searchTerm" :search-field="searchField" :position-filter="positionFilter"
      :available-positions="availablePositions" @update:search-term="searchTerm = $event"
      @update:search-field="searchField = $event" @update:position-filter="positionFilter = $event" />
    <DataTable :users="filteredUsers" />
  </section>
</template>
