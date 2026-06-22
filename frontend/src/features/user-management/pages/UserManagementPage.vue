<script setup lang="ts">
import { Plus } from "lucide-vue-next";
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
  <section class="rounded-xl border border-brand-light/30 bg-white p-5 shadow-[0_12px_28px_-18px_rgba(21,5,120,0.35)]">
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

    <div class="mb-4">
      <UserTableFilters :search-term="searchTerm" :search-field="searchField" :status-filter="statusFilter"
        @update:search-term="searchTerm = $event"
        @update:search-field="searchField = $event" @update:status-filter="statusFilter = $event" />
      <ListMeta :shown-count="filteredUsers.length" :total-count="normalizedUsers.length" label="users"
        :is-loading="isLoadingUsers" />
    </div>

    <DataTable
      :users="filteredUsers"
      :is-updating-status="isTogglingUserStatus"
      @toggle-status="toggleUserStatus"
    />
    <UserCreateModal :is-open="isCreateModalOpen" :roles="roles" :form="form" :form-errors="formErrors"
      :is-submitting="isSubmitting" :page-error="pageError" :page-message="pageMessage" @close="closeCreateModal"
      @submit="submitUser" />
  </section>
</template>
