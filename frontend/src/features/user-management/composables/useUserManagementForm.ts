import axios from "axios";
import { reactive, ref } from "vue";
import { usersApi } from "../api/usersApi";
import type {
  UserFormPayload,
  UserManagementFormErrors,
  UserManagementFormState,
} from "../types";

type SubmitUserOptions = {
  addUser: (user: import("../types").ManagedUser) => void;
};

function getErrorMessage(error: unknown): string {
  if (axios.isAxiosError(error)) {
    const response = error.response?.data as { message?: string } | undefined;
    return response?.message || "Unable to save the user.";
  }

  return "Unable to save the user.";
}

export function useUserManagementForm({ addUser }: SubmitUserOptions) {
  const isSubmitting = ref(false);
  const isCreateModalOpen = ref(false);
  const pageError = ref("");
  const pageMessage = ref("");
  const formErrors = ref<UserManagementFormErrors>({});

  const form = reactive<UserManagementFormState>({
    name: "",
    email: "",
    password: "",
    passwordConfirmation: "",
    roleId: "",
    subRole: "",
  });

  function clearFeedback(): void {
    pageError.value = "";
  }

  function resetForm(): void {
    form.name = "";
    form.email = "";
    form.password = "";
    form.passwordConfirmation = "";
    form.roleId = "";
    form.subRole = "";
    formErrors.value = {};
  }

  function openCreateModal(): void {
    isCreateModalOpen.value = true;
    clearFeedback();
    pageMessage.value = "";
  }

  function closeCreateModal(): void {
    isCreateModalOpen.value = false;
    resetForm();
    clearFeedback();
  }

  function toggleCreateModal(): void {
    if (isCreateModalOpen.value) {
      closeCreateModal();
      return;
    }

    openCreateModal();
  }

  function getFormErrors(error: unknown): UserManagementFormErrors {
    if (!axios.isAxiosError(error)) {
      return {};
    }

    const response = error.response?.data as
      | { errors?: Record<string, string[]> }
      | undefined;
    const errors = response?.errors ?? {};

    return {
      name: errors.name?.[0],
      email: errors.email?.[0],
      password: errors.password?.[0],
      password_confirmation: errors.password_confirmation?.[0],
      role_id: errors.role_id?.[0],
      sub_role: errors.sub_role?.[0],
    };
  }

  async function submitUser(): Promise<void> {
    clearFeedback();
    formErrors.value = {};
    isSubmitting.value = true;

    try {
      const payload: UserFormPayload = {
        name: form.name.trim(),
        email: form.email.trim(),
        password: form.password,
        password_confirmation: form.passwordConfirmation,
        role_id: Number(form.roleId),
        sub_role: form.subRole.trim() || undefined,
      };

      const { data } = await usersApi.createUser(payload);
      addUser(data.data);
      pageMessage.value = "User created successfully.";
      closeCreateModal();
    } catch (error) {
      formErrors.value = getFormErrors(error);
      pageError.value = getErrorMessage(error);
    } finally {
      isSubmitting.value = false;
    }
  }

  return {
    form,
    formErrors,
    isSubmitting,
    isCreateModalOpen,
    pageError,
    pageMessage,
    openCreateModal,
    closeCreateModal,
    toggleCreateModal,
    submitUser,
    resetForm,
    clearPageError: clearFeedback,
    clearPageMessage: () => {
      pageMessage.value = "";
    },
  };
}
