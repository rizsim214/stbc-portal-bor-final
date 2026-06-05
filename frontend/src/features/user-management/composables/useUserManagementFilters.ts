import { computed, ref, type ComputedRef } from "vue";
import type { ManagedUserRow, UserManagementSearchField } from "../types";

export function useUserManagementFilters(users: ComputedRef<ManagedUserRow[]>) {
  const searchTerm = ref("");
  const searchField = ref<UserManagementSearchField>("name");
  const roleFilter = ref("all");

  const filteredUsers = computed(() => {
    const query = searchTerm.value.trim().toLowerCase();

    return users.value.filter((user) => {
      const matchesQuery = !query || user[searchField.value].toLowerCase().includes(query);
      const matchesRole = roleFilter.value === "all" || user.role === roleFilter.value;
      return matchesQuery && matchesRole;
    });
  });

  return {
    searchTerm,
    searchField,
    roleFilter,
    filteredUsers,
  };
}
