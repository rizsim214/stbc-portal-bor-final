<script setup lang="ts">
import {
  TableCaption,
  Table,
  TableBody,
  TableCell,
  TableFooter,
  TableHead,
  TableHeader,
  TableRow,
} from '@/shared/ui/table'
import {
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuRoot,
  DropdownMenuTrigger,
} from "radix-vue";
import { computed, ref, watch } from "vue";
import { Button } from "@/shared/ui/button";
import { Input } from "@/shared/ui/input";
import { EllipsisVertical } from "lucide-vue-next";

const users = [
  {
    name: 'Alice Johnson',
    email: 'alice.johnson@stbc.com',
    role: 'Doctor',
  },
  {
    name: 'Brian Cruz',
    email: 'brian.cruz@stbc.com',
    role: 'Doctor',
  },
  {
    name: 'Carla Santos',
    email: 'carla.santos@stbc.com',
    role: 'Radiologist',
  },
  {
    name: 'Santos Lee',
    email: 'santos.lee@stbc.com',
    role: 'Nurse',
  },
  {
    name: 'Daniel Cruz',
    email: 'daniel.cruz@stbc.com',
    role: 'Nurse',
  },
  {
    name: 'Brian Lee',
    email: 'brian.lee@stbc.com',
    role: 'Doctor',
  },
  {
    name: 'Elaine Cruz',
    email: 'elaine.cruz@stbc.com',
    role: 'Secretary',
  },
  {
    name: 'Francis Tan',
    email: 'francis.tan@stbc.com',
    role: 'Lab Operator',
  },
]

const currentPage = ref(1);
const pageSize = 10;
const searchTerm = ref("");
const searchField = ref<"name" | "email" | "role">("name");
const positionFilter = ref("all");

const availablePositions = computed(() =>
  Array.from(new Set(users.map((user) => user.role))),
);

const filteredUsers = computed(() => {
  const query = searchTerm.value.trim().toLowerCase();

  return users.filter((user) => {
    const matchesQuery = !query
      || user[searchField.value].toLowerCase().includes(query);
    const matchesPosition =
      positionFilter.value === "all" || user.role === positionFilter.value;

    return matchesQuery && matchesPosition;
  });
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredUsers.value.length / pageSize)));

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return filteredUsers.value.slice(start, start + pageSize);
});

const rangeLabel = computed(() => {
  if (!filteredUsers.value.length) return "0-0 of 0";

  const start = (currentPage.value - 1) * pageSize + 1;
  const end = Math.min(currentPage.value * pageSize, filteredUsers.value.length);
  return `${start}-${end} of ${filteredUsers.value.length}`;
});

function nextPage() {
  currentPage.value = Math.min(currentPage.value + 1, totalPages.value);
}

function previousPage() {
  currentPage.value = Math.max(currentPage.value - 1, 1);
}

watch([searchTerm, searchField, positionFilter], () => {
  currentPage.value = 1;
});
</script>

<template>
  <div class="mb-4 grid gap-3 rounded-lg border border-brand-light/25 bg-brand-lighter/10 p-3 sm:grid-cols-[minmax(0,1fr)_180px_180px]">
    <Input
      v-model="searchTerm"
      label="Search"
      :placeholder="`Search by ${searchField}...`"
    />
    <div class="space-y-1">
      <label for="search-field" class="text-sm font-medium text-brand-darker">Filter Field</label>
      <select
        id="search-field"
        v-model="searchField"
        class="flex h-10 w-full rounded-md border border-brand-light/50 px-3 py-2 text-sm text-brand-darker transition placeholder:text-brand-dark/60 focus:border-brand-highlight focus:outline-none focus:ring-2 focus:ring-brand-highlight/40"
      >
        <option value="name">Name</option>
        <option value="email">Email</option>
        <option value="role">Position</option>
      </select>
    </div>
    <div class="space-y-1">
      <label for="position-filter" class="text-sm font-medium text-brand-darker">Position</label>
      <select
        id="position-filter"
        v-model="positionFilter"
        class="flex h-10 w-full rounded-md border border-brand-light/50 px-3 py-2 text-sm text-brand-darker transition placeholder:text-brand-dark/60 focus:border-brand-highlight focus:outline-none focus:ring-2 focus:ring-brand-highlight/40"
      >
        <option value="all">All positions</option>
        <option v-for="position in availablePositions" :key="position" :value="position">{{ position }}</option>
      </select>
    </div>
  </div>

  <Table>
    <TableCaption class="sr-only">User accounts table</TableCaption>
    <TableHeader>
      <TableRow>
        <TableHead scope="col">Name</TableHead>
        <TableHead scope="col">Email Address</TableHead>
        <TableHead scope="col">Position</TableHead>
        <TableHead scope="col" class="text-right">Options</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow v-for="(user, index) in paginatedUsers" :key="`${user.email}-${index}`"
        class="hover:bg-brand-lighter/20">
        <TableCell class="font-medium text-brand-darker">{{ user.name }}</TableCell>
        <TableCell>{{ user.email }}</TableCell>
        <TableCell>
          <span class="rounded-full bg-brand-lighter/50 px-2 py-1 text-xs font-medium text-brand-darker">{{ user.role
          }}</span>
        </TableCell>
        <TableCell>
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
              class="z-50 min-w-32 rounded-md border border-brand-light/30 bg-white p-1 shadow-lg outline-none"
              align="end" :side-offset="8">
              <DropdownMenuItem
                class="cursor-pointer rounded px-3 py-2 text-sm text-brand-dark outline-none focus:bg-brand-lighter/30">
                Edit
              </DropdownMenuItem>
              <DropdownMenuItem
                class="cursor-pointer rounded px-3 py-2 text-sm text-red-600 outline-none focus:bg-red-50">
                Deactivate
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenuRoot>
        </TableCell>
      </TableRow>
    </TableBody>
    <TableFooter>
      <TableRow>
        <TableCell colspan="2" class="text-xs text-brand-dark/75">
          Showing {{ rangeLabel }}
        </TableCell>
        <TableCell colspan="2" class="text-right">
          <div class="inline-flex items-center gap-2">
            <Button variant="outline" size="sm" class="w-auto" :disabled="currentPage === 1"
              @click="previousPage">Previous</Button>
            <span class="text-xs text-brand-dark/80">Page {{ currentPage }} of {{ totalPages }}</span>
            <Button variant="outline" size="sm" class="w-auto" :disabled="currentPage >= totalPages"
              @click="nextPage">Next</Button>
          </div>
        </TableCell>
      </TableRow>
    </TableFooter>
  </Table>
</template>
