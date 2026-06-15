<script setup lang="ts">
import BaseDataTable from "@/shared/components/DataTable/BaseDataTable.vue";
import type { ManagedUserRow } from "@/features/user-management/types";
import {
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuRoot,
  DropdownMenuTrigger,
} from "radix-vue";
import { EllipsisVertical } from "lucide-vue-next";

const props = defineProps<{
  users: ManagedUserRow[];
}>();

const columns = [
  { key: "id", label: "ID" },
  { key: "name", label: "Name" },
  { key: "email", label: "Email Address" },
  { key: "role", label: "Authorization" },
  { key: "actions", label: "Options", align: "right" as const },
];
</script>

<template>
  <BaseDataTable
    :rows="props.users as unknown as Record<string, unknown>[]"
    :columns="columns"
    :page-size="15"
  >
    <template #cell-id="{ row }">
      <span class="font-mono text-xs text-brand-dark/80">{{ row.id }}</span>
    </template>
    <template #cell-role="{ row }">
      <span class="rounded-full bg-brand-lighter/50 px-2 py-1 text-xs font-medium text-brand-darker">
        {{ row.role }}
      </span>
    </template>
    <template #cell-actions>
      <DropdownMenuRoot>
        <div class="text-right">
          <DropdownMenuTrigger as-child>
            <button type="button" class="rounded-md p-1 text-brand-dark transition hover:bg-brand-lighter/35"
              aria-label="Open actions menu">
              <EllipsisVertical class="h-4 w-4" />
            </button>
          </DropdownMenuTrigger>
        </div>
        <DropdownMenuContent
          class="z-50 flex min-w-32 flex-col gap-1 rounded-md border border-brand-light/30 bg-white p-1 shadow-lg outline-none"
          align="end" :side-offset="8">
          <DropdownMenuItem
            class="flex w-full cursor-pointer rounded px-3 py-2 text-left text-sm text-brand-dark outline-none focus:bg-brand-lighter/30">
            Edit
          </DropdownMenuItem>
          <DropdownMenuItem
            class="flex w-full cursor-pointer rounded px-3 py-2 text-left text-sm text-red-600 outline-none focus:bg-red-50">
            Deactivate
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenuRoot>
    </template>
  </BaseDataTable>
</template>
