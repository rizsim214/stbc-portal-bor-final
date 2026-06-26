<script setup lang="ts">
import { Save } from "lucide-vue-next";
import { Button } from "@/shared/ui/button";
import { Input } from "@/shared/ui/input";
import BaseModal from "@/shared/components/Modal/BaseModal.vue";
import type {
  UserManagementEditFormState,
  UserManagementFormErrors,
} from "@/features/user-management/types";

defineProps<{
  isOpen: boolean;
  form: UserManagementEditFormState;
  formErrors: UserManagementFormErrors;
  isSubmitting: boolean;
  pageError: string;
  pageMessage: string;
}>();

const emit = defineEmits<{
  (e: "close"): void;
  (e: "submit"): void;
}>();
</script>

<template>
  <BaseModal :is-open="isOpen" title="Modify Patient"
    description="Update the selected patient's name or set a new password." close-label="Close edit patient modal"
    @close="emit('close')">
    <form class="grid gap-4 md:grid-cols-2" @submit.prevent="emit('submit')">
      <Input v-model="form.name" label="Full name" placeholder="Jane Doe" :error="formErrors.name" />
      <Input v-model="form.email" label="Email address" type="email" placeholder="jane@example.com" disabled />
      <Input v-model="form.password" label="New password" type="password"
        placeholder="Leave blank to keep the current password" :error="formErrors.password"
        autocomplete="new-password" />
      <Input v-model="form.passwordConfirmation" label="Confirm new password" type="password"
        placeholder="Repeat new password" :error="formErrors.password_confirmation" autocomplete="new-password" />

      <div class="md:col-span-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-sm">
          <p v-if="pageError" class="text-red-600">{{ pageError }}</p>
          <p v-else-if="pageMessage" class="text-emerald-700">{{ pageMessage }}</p>
        </div>
        <div class="flex gap-2 sm:ml-auto">
          <Button type="button" variant="outline" class="w-auto" @click="emit('close')">
            Cancel
          </Button>
          <Button type="submit" class="w-auto bg-brand-dark hover:bg-brand-darker" :loading="isSubmitting">
            <Save class="mr-1 h-4 w-4" />
            Save Changes
          </Button>
        </div>
      </div>
    </form>
  </BaseModal>
</template>
