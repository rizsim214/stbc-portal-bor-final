<script setup lang="ts">
import { Check, ChevronDown, Plus } from "lucide-vue-next";
import {
  SelectContent,
  SelectIcon,
  SelectItem,
  SelectItemIndicator,
  SelectItemText,
  SelectPortal,
  SelectRoot,
  SelectTrigger,
  SelectValue,
  SelectViewport,
} from "radix-vue";
import { Button } from "@/shared/ui/button";
import { Input } from "@/shared/ui/input";
import BaseModal from "@/shared/components/Modal/BaseModal.vue";
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
  <BaseModal :is-open="isOpen" title="Add User"
    description="Create a new admin or patient account and assign a role immediately."
    close-label="Close add user modal" @close="emit('close')">
    <form class="grid gap-4 md:grid-cols-2" @submit.prevent="emit('submit')">
      <Input v-model="form.name" label="Full name" placeholder="Jane Doe" :error="formErrors.name" />
      <Input v-model="form.email" label="Email address" type="email" placeholder="jane@example.com"
        :error="formErrors.email" autocomplete="email" />
      <Input v-model="form.password" label="Password" type="password" placeholder="Enter password"
        :error="formErrors.password" autocomplete="new-password" />
      <Input v-model="form.passwordConfirmation" label="Confirm password" type="password" placeholder="Repeat password"
        :error="formErrors.password_confirmation" autocomplete="new-password" />

      <div class="space-y-1 md:col-span-2">
        <label for="role_id" class="text-sm font-medium text-brand-darker">Role</label>
        <SelectRoot v-model="form.roleId">
          <SelectTrigger id="role_id"
            class="inline-flex h-10 w-full items-center justify-between rounded-md border border-brand-light/50 bg-white px-3 py-2 text-sm text-brand-darker outline-none transition focus:border-brand-highlight focus:ring-2 focus:ring-brand-highlight/40"
            aria-label="Role">
            <SelectValue placeholder="Select a role" />
            <SelectIcon>
              <ChevronDown class="h-4 w-4 text-brand-dark/70" />
            </SelectIcon>
          </SelectTrigger>

          <SelectPortal>
            <SelectContent
              class="z-80 min-w-(--radix-select-trigger-width) overflow-hidden rounded-xl border border-brand-light/30 bg-white shadow-xl"
              position="popper" :side-offset="8">
              <SelectViewport class="max-h-60 overflow-y-auto p-1">
                <SelectItem v-for="role in roles" :key="role.id" :value="String(role.id)"
                  class="relative flex cursor-pointer select-none items-center rounded-lg px-3 py-2 text-sm text-brand-darker outline-none data-highlighted:bg-brand-lighter/35 data-[state=checked]:bg-brand-lighter/45">
                  <SelectItemText class="capitalize">{{ role.name }}</SelectItemText>
                  <SelectItemIndicator class="ml-auto">
                    <Check class="h-4 w-4 text-brand-highlight" />
                  </SelectItemIndicator>
                </SelectItem>
              </SelectViewport>
            </SelectContent>
          </SelectPortal>
        </SelectRoot>
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
            Save
          </Button>
        </div>
      </div>
    </form>
  </BaseModal>
</template>
