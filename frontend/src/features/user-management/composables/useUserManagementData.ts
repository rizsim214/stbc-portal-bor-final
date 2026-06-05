import { computed, ref } from "vue";
import { usersApi } from "../api/usersApi";
import type { ManagedRole, ManagedUser, ManagedUserRow } from "../types";

export function useUserManagementData() {
  const users = ref<ManagedUser[]>([]);
  const roles = ref<ManagedRole[]>([]);
  const isLoadingUsers = ref(false);
  const isLoadingRoles = ref(false);
  const dataError = ref("");

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
    isLoadingUsers.value = true;
    try {
      const { data } = await usersApi.listUsers();
      users.value = data.data;
    } catch (error) {
      dataError.value = "Failed to load users.";
      throw error;
    } finally {
      isLoadingUsers.value = false;
    }
  }

  async function loadRoles(): Promise<void> {
    isLoadingRoles.value = true;
    try {
      const { data } = await usersApi.listRoles();
      roles.value = data.data;
    } catch {
      if (!roles.value.length) {
        dataError.value = "Failed to load role options.";
      }
    } finally {
      isLoadingRoles.value = false;
    }
  }

  async function loadInitialData(): Promise<void> {
    await Promise.allSettled([loadUsers(), loadRoles()]);
  }

  function clearDataError(): void {
    dataError.value = "";
  }

  function addUser(user: ManagedUser): void {
    users.value = [user, ...users.value.filter((currentUser) => currentUser.id !== user.id)];
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
