<script setup lang="ts">
import { Plus } from "lucide-vue-next";
import { Button } from "@/shared/ui/button";
import { Input } from "@/shared/ui/input";
import type {
  ManagedRole,
  UserManagementFormErrors,
  UserManagementFormState,
} from "@/features/user-management/types";

defineProps<{
  isOpen: boolean;
  roles: ManagedRole[];
  form: UserManagementFormState;
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
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/45 p-4"
    @click.self="emit('close')">
    <dialog class="w-full max-w-2xl rounded-xl border border-brand-light/30 bg-white p-5 shadow-2xl" aria-modal="true"
      aria-labelledby="user-create-modal-title" aria-describedby="user-create-modal-description" tabindex="-1">
      <div class="mb-4 flex items-start justify-between gap-4">
        <div>
          <h3 id="user-create-modal-title" class="text-base font-semibold text-brand-darker">Add user</h3>
          <p id="user-create-modal-description" class="text-sm text-brand-dark/80">
            Create a new admin or patient account and assign a role immediately.
          </p>
        </div>
        <button type="button"
          class="rounded-md px-2 py-1 text-sm text-brand-dark/70 transition hover:bg-brand-lighter/30 hover:text-brand-darker"
          aria-label="Close add user modal" @click="emit('close')">
          Close
        </button>
      </div>

      <form class="grid gap-4 md:grid-cols-2" @submit.prevent="emit('submit')">
        <Input v-model="form.name" label="Full name" placeholder="Jane Doe" :error="formErrors.name" />
        <Input v-model="form.email" label="Email address" type="email" placeholder="jane@example.com"
          :error="formErrors.email" autocomplete="email" />
        <Input v-model="form.password" label="Password" type="password" placeholder="Enter password"
          :error="formErrors.password" autocomplete="new-password" />
        <Input v-model="form.passwordConfirmation" label="Confirm password" type="password"
          placeholder="Repeat password" :error="formErrors.password_confirmation" autocomplete="new-password" />

        <div class="space-y-1 md:col-span-2">
          <label for="role_id" class="text-sm font-medium text-brand-darker">Role</label>
          <select id="role_id" v-model="form.roleId"
            class="flex h-10 w-full rounded-md border border-brand-light/50 px-3 py-2 text-sm text-brand-darker transition placeholder:text-brand-dark/60 focus:border-brand-highlight focus:outline-none focus:ring-2 focus:ring-brand-highlight/40">
            <option value="" disabled>Select a role</option>
            <option v-for="role in roles" :key="role.id" :value="String(role.id)">
              {{ role.name }}
            </option>
          </select>
          <p v-if="formErrors.role_id" class="text-sm text-red-500">{{ formErrors.role_id }}</p>
        </div>

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
              <Plus class="mr-1 h-4 w-4" />
              Save User
            </Button>
          </div>
        </div>
      </form>
    </dialog>
  </div>
</template>
