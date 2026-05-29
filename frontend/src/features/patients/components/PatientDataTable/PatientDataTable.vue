<script setup lang="ts">
import BaseDataTable from "@/shared/components/DataTable/BaseDataTable.vue";
import {
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuRoot,
  DropdownMenuTrigger,
} from "radix-vue";
import { EllipsisVertical } from "lucide-vue-next";

type PatientRow = {
  name: string;
  email: string;
  status: string;
  lastVisit: string;
};

const props = defineProps<{
  patients: PatientRow[];
}>();

const columns = [
  { key: "name", label: "Patient Name" },
  { key: "email", label: "Email Address" },
  { key: "status", label: "Status" },
  { key: "lastVisit", label: "Last Visit" },
  { key: "actions", label: "Options", align: "right" as const },
];
</script>

<template>
  <BaseDataTable :rows="props.patients as unknown as Record<string, unknown>[]" :columns="columns" :page-size="5">
    <template #cell-status="{ row }">
      <span class="rounded-full bg-brand-lighter/50 px-2 py-1 text-xs font-medium text-brand-darker">
        {{ row.status }}
      </span>
    </template>

    <template #cell-actions="{ row }">
      <DropdownMenuRoot>
        <div class="text-right">
          <DropdownMenuTrigger as-child>
            <button type="button" class="rounded-md p-1 text-brand-dark transition hover:bg-brand-lighter/35"
              aria-label="Open patient actions menu">
              <EllipsisVertical class="h-4 w-4" />
            </button>
          </DropdownMenuTrigger>
        </div>
        <DropdownMenuContent
          class="z-50 min-w-36 rounded-md border border-brand-light/30 bg-white p-1 shadow-lg outline-none"
          align="end" :side-offset="8">
          <DropdownMenuItem
            class="cursor-pointer rounded px-3 py-2 text-sm text-brand-dark outline-none focus:bg-brand-lighter/30">
            View Profile
          </DropdownMenuItem>
          <DropdownMenuItem
            class="cursor-pointer rounded px-3 py-2 text-sm text-brand-dark outline-none focus:bg-brand-lighter/30">
            View Records
          </DropdownMenuItem>
          <DropdownMenuItem
            class="cursor-pointer rounded px-3 py-2 text-sm text-red-600 outline-none focus:bg-red-50">
            Deactivate
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenuRoot>
    </template>
  </BaseDataTable>
</template>
