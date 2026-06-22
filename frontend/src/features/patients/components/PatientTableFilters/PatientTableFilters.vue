<script setup lang="ts">
import { Input } from "@/shared/ui/input";
import type { PatientListSearchField } from "@/features/patients/types";

defineProps<{
  searchTerm: string;
  searchField: PatientListSearchField;
}>();

const emit = defineEmits<{
  (e: "update:searchTerm", value: string): void;
  (e: "update:searchField", value: PatientListSearchField): void;
}>();
</script>

<template>
  <div
    class="mb-4 grid gap-3 rounded-lg border border-brand-light/25 bg-brand-lighter/10 p-3 sm:grid-cols-[minmax(0,1fr)_180px]">
    <Input :model-value="searchTerm" id="user-search" label="Search" :placeholder="`Search by ${searchField}...`"
      @update:model-value="emit('update:searchTerm', $event)" />

    <div class="space-y-1">
      <label for="search-field" class="text-sm font-medium text-brand-darker">Search Field</label>
      <select id="search-field" :value="searchField"
        class="flex h-10 w-full rounded-md border border-brand-light/50 px-3 py-2 text-sm text-brand-darker transition placeholder:text-brand-dark/60 focus:border-brand-highlight focus:outline-none focus:ring-2 focus:ring-brand-highlight/40"
        @change="
          emit('update:searchField', ($event.target as HTMLSelectElement).value as PatientListSearchField)
          ">
        <option value="name">Name</option>
        <option value="email">Email</option>
      </select>
    </div>
  </div>
</template>
