<script setup lang="ts">
import BaseDataTable from "@/shared/components/DataTable/BaseDataTable.vue";
import type { ManagedUserRow } from "@/features/user-management/types";
import {
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuRoot,
  DropdownMenuTrigger,
} from "radix-vue";
import { EllipsisVertical, Trash2, UserRoundPen } from "lucide-vue-next";

const props = defineProps<{
  users: ManagedUserRow[];
  isUpdatingStatus?: boolean;
}>();

const emit = defineEmits<{
  (e: "toggle-status", userId: number): void;
}>();

function asManagedUserRow(row: object): ManagedUserRow {
  return row as ManagedUserRow;
}

const columns = [
  { key: "id", label: "ID" },
  { key: "name", label: "Name" },
  { key: "email", label: "Email Address" },
  { key: "role", label: "Authority" },
  { key: "status", label: "Status", align: "center" as const },
  { key: "actions", label: "Options", align: "right" as const },
];
</script>

<template>
  <BaseDataTable :rows="props.users" :columns="columns" :page-size="15">
    <template #cell-id="{ row }">
      <span class="font-mono text-xs text-brand-dark/80">{{ asManagedUserRow(row).id }}</span>
    </template>
    <template #cell-role="{ row }">
      <span class="rounded-full bg-brand-lighter/50 px-2 py-1 text-xs font-medium text-brand-darker">
        {{ asManagedUserRow(row).role }}
      </span>
    </template>
    <template #cell-status="{ row }">
      <div class="flex w-full justify-center">
        <span class="inline-flex items-center gap-2 text-sm font-medium text-brand-darker">
          <span :class="[
            'inline-flex h-2 w-2 rounded-full',
            asManagedUserRow(row).status === 'inactive' ? 'bg-red-500' : 'bg-emerald-500',
          ]"></span>
          {{ asManagedUserRow(row).status === 'inactive' ? 'Inactive' : 'Active' }}
        </span>
      </div>
    </template>
    <template #cell-actions="{ row }">
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
            class="flex items-center gap-1 w-full cursor-pointer rounded px-3 py-2 text-left text-sm text-brand-dark outline-none focus:bg-brand-lighter/30">
            <UserRoundPen class="h-4 w-4" />
            Modify
          </DropdownMenuItem>
          <DropdownMenuItem :disabled="props.isUpdatingStatus" @select="emit('toggle-status', asManagedUserRow(row).id)"
            class="flex items-center gap-1 w-full cursor-pointer rounded px-3 py-2 text-left text-sm outline-none"
            :class="asManagedUserRow(row).status === 'inactive' ? 'text-emerald-600 focus:bg-emerald-50' : 'text-red-600 focus:bg-red-50'">
            <Trash2 class="h-4 w-4" />
            {{ asManagedUserRow(row).status === 'inactive' ? 'Activate' : 'Deactivate' }}
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenuRoot>
    </template>
  </BaseDataTable>
</template>
