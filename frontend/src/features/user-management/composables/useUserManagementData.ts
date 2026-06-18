import { computed, ref } from "vue";
import { useQuery, useQueryClient } from "@tanstack/vue-query";
import { usersApi } from "../api/usersApi";
import type { ManagedUser, ManagedUserRow } from "../types";

function getErrorMessage(error: unknown, fallback: string): string {
  if (error instanceof Error && error.message) {
    return error.message;
  }

  return fallback;
}

export function useUserManagementData() {
  const queryClient = useQueryClient();
  const errorDismissed = ref(false);

  const usersQuery = useQuery({
    queryKey: ["users", "management", "list"],
    queryFn: async () => {
      const { data } = await usersApi.listUsers();
      return data.data;
    },
  });

  const rolesQuery = useQuery({
    queryKey: ["users", "management", "roles"],
    queryFn: async () => {
      const { data } = await usersApi.listRoles();
      return data.data;
    },
  });

  const users = computed(() => usersQuery.data.value ?? []);
  const roles = computed(() => rolesQuery.data.value ?? []);
  const isLoadingUsers = computed(() => usersQuery.isFetching.value || usersQuery.isPending.value);
  const isLoadingRoles = computed(() => rolesQuery.isFetching.value || rolesQuery.isPending.value);
  const dataError = computed(() => {
    if (errorDismissed.value) {
      return "";
    }

    if (usersQuery.isError.value) {
      return getErrorMessage(usersQuery.error.value, "Failed to load users.");
    }

    if (rolesQuery.isError.value && !roles.value.length) {
      return getErrorMessage(rolesQuery.error.value, "Failed to load role options.");
    }

    return "";
  });

  const normalizedUsers = computed<ManagedUserRow[]>(() =>
    users.value.map((user) => ({
      id: user.id,
      name: user.name,
      email: user.email,
      role: user.role?.name ?? "unassigned",
    })),
  );

  const availableRoles = computed(() => {
    if (roles.value.length > 0) {
      return roles.value.map((role) => role.name);
    }

    return Array.from(new Set(normalizedUsers.value.map((user) => user.role).filter(Boolean)));
  });

  async function loadUsers(): Promise<void> {
    errorDismissed.value = false;
    await usersQuery.refetch();
  }

  async function loadRoles(): Promise<void> {
    errorDismissed.value = false;
    await rolesQuery.refetch();
  }

  async function loadInitialData(): Promise<void> {
    await Promise.allSettled([loadUsers(), loadRoles()]);
  }

  function clearDataError(): void {
    errorDismissed.value = true;
  }

  function addUser(user: ManagedUser): void {
    queryClient.setQueryData<ManagedUser[]>(
      ["users", "management", "list"],
      (currentUsers = []) => [user, ...currentUsers.filter((currentUser) => currentUser.id !== user.id)],
    );
  }

  return {
    users,
    roles,
    isLoadingUsers,
    isLoadingRoles,
    dataError,
    normalizedUsers,
    availableRoles,
    loadUsers,
    loadRoles,
    loadInitialData,
    clearDataError,
    addUser,
  };
}
