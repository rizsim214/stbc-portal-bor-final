<script setup lang="ts">
import { Plus, ShieldCheck, Stethoscope, UserRound, UserRoundCog } from "lucide-vue-next";
import { computed } from "vue";
import DataTable from "@/features/user-management/components/UserDataTable/UserDataTable.vue";
import UserCreateModal from "@/features/user-management/components/UserCreateModal/UserCreateModal.vue";
import UserTableFilters from "@/features/user-management/components/UserTableFilters/UserTableFilters.vue";
import { useUserManagementData } from "@/features/user-management/composables/useUserManagementData";
import { useUserManagementFilters } from "@/features/user-management/composables/useUserManagementFilters";
import { useUserManagementForm } from "@/features/user-management/composables/useUserManagementForm";
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import ListMeta from "@/shared/components/ListMeta/ListMeta.vue";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";
import { Button } from "@/shared/ui/button";

const {
  roles,
  dataError,
  clearDataError,
  isLoadingUsers,
  normalizedUsers,
  addUser,
  toggleUserStatus,
  isTogglingUserStatus,
} = useUserManagementData();

const { searchTerm, searchField, statusFilter, filteredUsers } = useUserManagementFilters(normalizedUsers);

const activeUsersCount = computed(
  () => normalizedUsers.value.filter((user) => user.status === "active").length,
);
const inactiveUsersCount = computed(
  () => normalizedUsers.value.filter((user) => user.status === "inactive").length,
);
const adminUsersCount = computed(
  () => normalizedUsers.value.filter((user) => user.role === "admin").length,
);
const nextReviewUser = computed(() => filteredUsers.value[0] ?? normalizedUsers.value[0] ?? null);

const {
  form,
  formErrors,
  isSubmitting,
  isCreateModalOpen,
  pageError,
  pageMessage,
  clearPageError,
  clearPageMessage,
  toggleCreateModal,
  closeCreateModal,
  submitUser,
} = useUserManagementForm({ addUser });

function dismissStatusBanner(): void {
  clearDataError();
  clearPageError();
  clearPageMessage();
}
</script>

<template>
  <section class="rounded-[1.75rem] border border-brand-light/25 bg-white p-6 shadow-sm">
    <PageHeader title="User Administration" subtitle="Create users and review every account from one place.">
      <template #actions>
        <Button class="w-auto bg-brand-dark hover:bg-brand-darker" @click="toggleCreateModal">
          <Plus class="mr-1 h-4 w-4" />
          Add Account
        </Button>
      </template>
    </PageHeader>

    <StatusBanner v-if="dataError || pageError || pageMessage" :message="dataError || pageError || pageMessage"
      :tone="dataError || pageError ? 'error' : 'success'" @dismiss="dismissStatusBanner" />

    <div class="mt-6 space-y-6">
      <article
        class="relative overflow-hidden rounded-[1.75rem] border border-brand-light/20 bg-linear-to-br from-white via-sky-50 to-brand-lighter/35 p-6">
        <div class="absolute right-0 top-0 h-28 w-28 rounded-full bg-brand-light/10 blur-3xl" />
        <div class="absolute bottom-0 left-10 h-24 w-24 rounded-full bg-sky-300/10 blur-3xl" />

        <div class="relative grid gap-4 lg:grid-cols-[1.3fr_0.7fr]">
          <div class="space-y-4">
            <div
              class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-darker shadow-sm ring-1 ring-brand-light/15">
              <UserRoundCog class="h-3.5 w-3.5" />
              Account Overview
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
              <div class="rounded-2xl border border-white/80 bg-white/85 p-4 shadow-sm backdrop-blur">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Visible</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{{ filteredUsers.length }}</p>
                <p class="mt-1 text-sm text-slate-600">Users in current view</p>
              </div>

              <div class="rounded-2xl border border-white/80 bg-white/85 p-4 shadow-sm backdrop-blur">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Active</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{{ activeUsersCount }}</p>
                <p class="mt-1 text-sm text-slate-600">Accounts ready to use</p>
              </div>

              <div class="rounded-2xl border border-white/80 bg-white/85 p-4 shadow-sm backdrop-blur">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Inactive</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{{ inactiveUsersCount }}</p>
                <p class="mt-1 text-sm text-slate-600">Require reactivation</p>
              </div>
            </div>
          </div>

          <div class="rounded-[1.5rem] border border-white/80 bg-white/90 p-5 shadow-sm backdrop-blur">
            <div class="flex items-center gap-3">
              <div class="rounded-2xl bg-brand-lighter/35 p-3 text-brand-darker">
                <ShieldCheck class="h-5 w-5" />
              </div>
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Current Focus</p>
                <p class="mt-1 text-sm font-medium text-slate-900">
                  {{ nextReviewUser?.name ?? "No users available" }}
                </p>
              </div>
            </div>

            <div class="mt-4 space-y-2 text-sm text-slate-600">
              <p v-if="nextReviewUser">{{ nextReviewUser.email }}</p>
              <p v-if="nextReviewUser" class="capitalize">{{ nextReviewUser.role }}</p>
              <p v-if="nextReviewUser" class="capitalize">
                {{ nextReviewUser.subRole || "No sub role" }}
              </p>
              <p v-else>No accounts available in the current view.</p>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                <div class="flex items-center gap-2 text-brand-dark/70">
                  <Stethoscope class="h-4 w-4" />
                  <span class="text-[11px] font-semibold uppercase tracking-[0.12em]">Admins</span>
                </div>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ adminUsersCount }}</p>
              </div>
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                <div class="flex items-center gap-2 text-brand-dark/70">
                  <UserRound class="h-4 w-4" />
                  <span class="text-[11px] font-semibold uppercase tracking-[0.12em]">Total</span>
                </div>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ normalizedUsers.length }}</p>
              </div>
            </div>
          </div>
        </div>
      </article>

      <article class="overflow-hidden rounded-[1.5rem] border border-brand-light/20 bg-white shadow-sm">
        <div
          class="flex flex-col gap-4 border-b border-brand-light/15 px-5 py-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Account Directory</p>
            <h2 class="mt-2 text-xl font-semibold tracking-tight text-brand-darker">Search, filter, and manage user
              access
            </h2>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
            <ListMeta :shown-count="filteredUsers.length" :total-count="normalizedUsers.length" label="users"
              :is-loading="isLoadingUsers" />
          </div>
        </div>

        <div class="px-5 py-5">
          <UserTableFilters :search-term="searchTerm" :search-field="searchField" :status-filter="statusFilter"
            @update:search-term="searchTerm = $event" @update:search-field="searchField = $event"
            @update:status-filter="statusFilter = $event" />

          <div class="mt-5">
            <DataTable :users="filteredUsers" :is-updating-status="isTogglingUserStatus"
              @toggle-status="toggleUserStatus" />
          </div>
        </div>
      </article>
    </div>
    <UserCreateModal :is-open="isCreateModalOpen" :roles="roles" :form="form" :form-errors="formErrors"
      :is-submitting="isSubmitting" :page-error="pageError" :page-message="pageMessage" @close="closeCreateModal"
      @submit="submitUser" />
  </section>
</template>
