import { computed, ref, type ComputedRef } from "vue";
import type {
  ManagedUserRow,
  UserManagementSearchField,
  UserManagementStatusFilter,
} from "../types";

export function useUserManagementFilters(users: ComputedRef<ManagedUserRow[]>) {
  const searchTerm = ref("");
  const searchField = ref<UserManagementSearchField>("name");
  const statusFilter = ref<UserManagementStatusFilter>("all");

  const filteredUsers = computed(() => {
    const query = searchTerm.value.trim().toLowerCase();

    return users.value.filter((user) => {
      const matchesQuery = !query || user[searchField.value].toLowerCase().includes(query);
      const matchesStatus =
        statusFilter.value === "all" || user.status === statusFilter.value;
      return matchesQuery && matchesStatus;
    });
  });

  return {
    searchTerm,
    searchField,
    statusFilter,
    filteredUsers,
  };
}
