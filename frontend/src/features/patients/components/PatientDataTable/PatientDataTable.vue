<script setup lang="ts">
import BaseDataTable from "@/shared/components/DataTable/BaseDataTable.vue";
import type { PatientRow } from "@/features/patients/types";
import {
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuRoot,
  DropdownMenuTrigger,
} from "radix-vue";
import { EllipsisVertical, Eye } from "lucide-vue-next";

const props = defineProps<{
  patients: PatientRow[];
}>();

function asPatientRow(row: object): PatientRow {
  return row as PatientRow;
}

const columns = [
  { key: "name", label: "User Name" },
  { key: "email", label: "Email Address" },
  { key: "role", label: "Authority" },
  { key: "actions", label: "Options", align: "right" as const },
];
</script>

<template>
  <BaseDataTable :rows="props.patients" :columns="columns" :page-size="15">
    <template #cell-role="{ row }">
      <span class="rounded-full bg-brand-lighter/50 px-2 py-1 text-xs font-medium text-brand-darker">
        {{ asPatientRow(row).role }}
      </span>
    </template>

    <template #cell-actions="{ row }">
      <DropdownMenuRoot>
        <div class="text-right">
          <DropdownMenuTrigger as-child>
            <button type="button" class="rounded-md p-1 text-brand-dark transition hover:bg-brand-lighter/35"
              aria-label="Open user actions menu">
              <EllipsisVertical class="h-4 w-4" />
            </button>
          </DropdownMenuTrigger>
        </div>
        <DropdownMenuContent
          class="z-50 flex min-w-36 flex-col gap-1 rounded-md border border-brand-light/30 bg-white p-1 shadow-lg outline-none"
          align="end" :side-offset="8">
          <DropdownMenuItem as-child
            class="flex w-full cursor-pointer rounded px-3 py-2 text-left text-sm text-brand-dark outline-none focus:bg-brand-lighter/30">
            <RouterLink :to="{ name: 'userDetailView', params: { userId: String(asPatientRow(row).id) } }"
              class="inline-flex w-full items-center gap-2">
              <Eye class="h-4 w-4" />
              View Details
            </RouterLink>
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenuRoot>
    </template>
  </BaseDataTable>
</template>
