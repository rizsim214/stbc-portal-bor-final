<script setup lang="ts">
import {
  Table,
  TableBody,
  TableCell,
  TableFooter,
  TableHead,
  TableHeader,
  TableRow,
} from "@/shared/ui/table";
import {
  PaginationEllipsis,
  PaginationFirst,
  PaginationLast,
  PaginationList,
  PaginationListItem,
  PaginationNext,
  PaginationPrev,
  PaginationRoot,
} from "radix-vue";
import { computed, ref, watch } from "vue";
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from "lucide-vue-next";

type TableColumn = {
  key: string;
  label: string;
  align?: "left" | "right" | "center";
};

const props = withDefaults(defineProps<{
  rows: Record<string, unknown>[];
  columns: TableColumn[];
  pageSize?: number;
}>(), {
  pageSize: 5,
});

const currentPage = ref(1);

const totalPages = computed(() =>
  Math.max(1, Math.ceil(props.rows.length / props.pageSize)),
);

const pagedRows = computed(() => {
  const start = (currentPage.value - 1) * props.pageSize;
  return props.rows.slice(start, start + props.pageSize);
});

const rangeLabel = computed(() => {
  if (!props.rows.length) return "0-0 of 0";
  const start = (currentPage.value - 1) * props.pageSize + 1;
  const end = Math.min(currentPage.value * props.pageSize, props.rows.length);
  return `${start}-${end} of ${props.rows.length}`;
});

function alignmentClass(align?: "left" | "right" | "center"): string {
  if (align === "right") return "text-right";
  if (align === "center") return "text-center";
  return "text-left";
}

watch(() => props.rows, () => {
  currentPage.value = 1;
});
</script>

<template>
  <Table v-if="rows.length > 0">
    <TableHeader>
      <TableRow>
        <TableHead v-for="column in columns" :key="column.key" scope="col" :class="alignmentClass(column.align)">
          {{ column.label }}
        </TableHead>
      </TableRow>
    </TableHeader>

    <TableBody>
      <TableRow v-for="(row, rowIndex) in pagedRows" :key="rowIndex" class="hover:bg-brand-lighter/20">
        <TableCell v-for="column in columns" :key="`${rowIndex}-${column.key}`" :class="alignmentClass(column.align)">
          <slot :name="`cell-${column.key}`" :row="row">
            {{ row[column.key] }}
          </slot>
        </TableCell>
      </TableRow>
    </TableBody>

    <TableFooter>
      <TableRow>
        <TableCell :colspan="Math.max(1, columns.length - 2)" class="text-xs text-brand-dark/75">
          Showing {{ rangeLabel }}
        </TableCell>
        <TableCell :colspan="2" class="text-right">
          <div class="inline-flex items-center gap-3">
            <span class="text-xs text-brand-dark/80">Page {{ currentPage }} of {{ totalPages }}</span>
            <PaginationRoot v-model:page="currentPage" :total="rows.length" :items-per-page="pageSize"
              :sibling-count="1" show-edges>
              <PaginationList v-slot="{ items }" class="flex items-center gap-1">
                <PaginationFirst
                  class="inline-flex h-8 items-center rounded-md px-2 text-xs text-brand-dark transition hover:bg-brand-lighter/30 data-disabled:pointer-events-none data-disabled:opacity-45">
                  <ChevronsLeft class="h-4 w-4 stroke-[1.5]" />
                </PaginationFirst>
                <PaginationPrev
                  class="inline-flex h-8 items-center rounded-md px-2 text-xs text-brand-dark transition hover:bg-brand-lighter/30 data-disabled:pointer-events-none data-disabled:opacity-45">
                  <ChevronLeft class="h-4 w-4 stroke-[1.5]" />
                </PaginationPrev>

                <template v-for="(item, index) in items" :key="`${item.type}-${index}`">
                  <PaginationListItem v-if="item.type === 'page'" :value="item.value"
                    class="inline-flex h-8 min-w-8 items-center justify-center rounded-md border-brand-light/40 px-2 text-xs text-brand-dark transition hover:bg-brand-lighter/30 data-[selected]:border-brand-darker data-[selected]:bg-brand-darker data-[selected]:text-white">
                    {{ item.value }}
                  </PaginationListItem>
                  <PaginationEllipsis v-else
                    class="inline-flex h-8 min-w-8 items-center justify-center text-xs text-brand-dark/70"
                    :index="index">
                    ...
                  </PaginationEllipsis>
                </template>

                <PaginationNext
                  class="inline-flex h-8 items-center rounded-md px-2 text-xs text-brand-dark transition hover:bg-brand-lighter/30 data-[disabled]:pointer-events-none data-[disabled]:opacity-45">
                  <ChevronRight class="h-4 w-4 stroke-[1.5]" />
                </PaginationNext>
                <PaginationLast
                  class="inline-flex h-8 items-center rounded-md px-2 text-xs text-brand-dark transition hover:bg-brand-lighter/30 data-[disabled]:pointer-events-none data-[disabled]:opacity-45">
                  <ChevronsRight class="h-4 w-4 stroke-[1.5]" />
                </PaginationLast>
              </PaginationList>
            </PaginationRoot>
          </div>
        </TableCell>
      </TableRow>
    </TableFooter>
  </Table>
</template>
