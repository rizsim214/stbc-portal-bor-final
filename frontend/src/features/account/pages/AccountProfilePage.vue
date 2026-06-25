<script setup lang="ts">
import axios from "axios";
import { computed, reactive, ref } from "vue";
import {
  BadgeCheck,
  KeyRound,
  ShieldCheck,
  UserRound,
} from "lucide-vue-next";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import { accountApi } from "@/features/account/api/accountApi";
import type { PasswordFormErrors } from "@/features/account/types";
import PageHeader from "@/shared/components/PageHeader/PageHeader.vue";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";
import { Button } from "@/shared/ui/button";
import { Input } from "@/shared/ui/input";

const authStore = useAuthStore();

const form = reactive({
  oldPassword: "",
  newPassword: "",
  confirmNewPassword: "",
});

const formErrors = reactive<PasswordFormErrors>({});
const submitError = ref("");
const submitSuccess = ref("");
const isSubmitting = ref(false);

const roleLabel = computed(() => {
  const role = authStore.user?.role?.name ?? "patient";
  return role.slice(0, 1).toUpperCase() + role.slice(1);
});

const userInitial = computed(() => {
  const source = authStore.user?.name?.trim() || authStore.user?.email || "U";
  return source.slice(0, 1).toUpperCase();
});

function clearFieldError(
  field: keyof PasswordFormErrors,
): void {
  formErrors[field] = "";
  submitError.value = "";
  submitSuccess.value = "";
}

function clearAllErrors(): void {
  formErrors.old_password = "";
  formErrors.new_password = "";
  formErrors.new_password_confirmation = "";
  submitError.value = "";
}

async function submitPasswordChange(): Promise<void> {
  clearAllErrors();
  submitSuccess.value = "";
  isSubmitting.value = true;

  try {
    const { data } = await accountApi.updatePassword({
      old_password: form.oldPassword,
      new_password: form.newPassword,
      new_password_confirmation: form.confirmNewPassword,
    });

    form.oldPassword = "";
    form.newPassword = "";
    form.confirmNewPassword = "";
    submitSuccess.value = data.message;
  } catch (error) {
    if (axios.isAxiosError(error)) {
      const errors = error.response?.data?.errors as
        | Record<string, string[]>
        | undefined;

      formErrors.old_password = errors?.old_password?.[0] ?? "";
      formErrors.new_password = errors?.new_password?.[0] ?? "";
      formErrors.new_password_confirmation =
        errors?.new_password_confirmation?.[0] ?? "";
      submitError.value =
        error.response?.data?.message ?? "Unable to update password.";
      return;
    }

    submitError.value = "Unable to update password.";
  } finally {
    isSubmitting.value = false;
  }
}
</script>

<template>
  <section class="space-y-6">
    <PageHeader
      title="My Profile"
      subtitle="Review your account details and replace your temporary password with a personal one."
      heading-tag="h1"
    />

    <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
      <article
        class="relative overflow-hidden rounded-[1.9rem] border border-brand-light/25 bg-linear-to-br from-white via-sky-50 to-brand-lighter/35 p-6 shadow-sm"
      >
        <div class="absolute right-0 top-0 h-36 w-36 rounded-full bg-brand-light/10 blur-3xl" />
        <div class="absolute bottom-0 left-12 h-28 w-28 rounded-full bg-sky-300/10 blur-3xl" />

        <div class="relative space-y-6">
          <div
            class="inline-flex items-center gap-2 rounded-full bg-white/85 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-darker shadow-sm ring-1 ring-brand-light/15"
          >
            <ShieldCheck class="h-3.5 w-3.5" />
            Account Overview
          </div>

          <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
              <div class="flex items-center gap-4">
                <div
                  class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-brand-dark text-2xl font-semibold text-white shadow-[0_20px_40px_-28px_rgba(15,23,42,0.85)]"
                >
                  {{ userInitial }}
                </div>
                <div class="min-w-0">
                  <h2 class="text-2xl font-semibold tracking-tight text-brand-darker md:text-3xl">
                    {{ authStore.user?.name ?? "User" }}
                  </h2>
                  <p class="mt-2 max-w-2xl text-sm leading-6 text-brand-dark/80">
                    Keep your sign-in credentials current so your appointment history and records stay secure.
                  </p>
                </div>
              </div>
            </div>

            <span
              class="inline-flex items-center gap-2 self-start rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-brand-darker ring-1 ring-brand-light/20"
            >
              <BadgeCheck class="h-3.5 w-3.5" />
              {{ roleLabel }}
            </span>
          </div>

          <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-white/80 bg-white/88 p-4 shadow-sm backdrop-blur">
              <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Email</p>
              <p class="mt-2 text-sm font-semibold text-slate-900 break-all">
                {{ authStore.user?.email ?? "Unknown" }}
              </p>
            </div>

            <div class="rounded-2xl border border-white/80 bg-white/88 p-4 shadow-sm backdrop-blur">
              <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Access</p>
              <p class="mt-2 text-lg font-semibold tracking-tight text-slate-900">
                {{ roleLabel }}
              </p>
            </div>

            <div class="rounded-2xl border border-white/80 bg-white/88 p-4 shadow-sm backdrop-blur">
              <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">Account ID</p>
              <p class="mt-2 text-lg font-semibold tracking-tight text-slate-900">
                #{{ authStore.user?.id ?? "N/A" }}
              </p>
            </div>
          </div>

          <div class="rounded-[1.4rem] border border-white/80 bg-white/90 p-5 shadow-sm backdrop-blur">
            <div class="flex items-start gap-3">
              <div class="rounded-2xl bg-brand-lighter/35 p-3 text-brand-darker">
                <KeyRound class="h-5 w-5" />
              </div>
              <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">
                  Password Guidance
                </p>
                <p class="mt-2 text-sm leading-6 text-slate-700">
                  Use the temporary password you received earlier as the old password, then save a personal password
                  with at least 8 characters.
                </p>
              </div>
            </div>
          </div>
        </div>
      </article>

      <section class="rounded-[1.9rem] border border-brand-light/20 bg-white p-6 shadow-sm">
        <div class="flex items-start gap-4">
          <div class="rounded-2xl bg-brand-lighter/30 p-3 text-brand-darker">
            <UserRound class="h-5 w-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-dark/60">
              Password Update
            </p>
            <h2 class="mt-2 text-xl font-semibold tracking-tight text-brand-darker">
              Change your account password
            </h2>
            <p class="mt-2 text-sm leading-6 text-brand-dark/80">
              Enter your old temporary password, then confirm the new password before saving.
            </p>
          </div>
        </div>

        <div class="mt-5 space-y-4">
          <StatusBanner
            v-if="submitError"
            :message="submitError"
            tone="error"
            @dismiss="submitError = ''"
          />
          <StatusBanner
            v-if="submitSuccess"
            :message="submitSuccess"
            tone="success"
            @dismiss="submitSuccess = ''"
          />

          <form class="space-y-4" @submit.prevent="submitPasswordChange">
            <Input
              id="old-password"
              v-model="form.oldPassword"
              type="password"
              label="Old temporary password"
              placeholder="Enter your previous password"
              autocomplete="current-password"
              :error="formErrors.old_password"
              @clear-error="clearFieldError('old_password')"
            />

            <Input
              id="new-password"
              v-model="form.newPassword"
              type="password"
              label="New password"
              placeholder="Create a new password"
              autocomplete="new-password"
              :error="formErrors.new_password"
              @clear-error="clearFieldError('new_password')"
            />

            <Input
              id="confirm-new-password"
              v-model="form.confirmNewPassword"
              type="password"
              label="Confirm new password"
              placeholder="Repeat the new password"
              autocomplete="new-password"
              :error="formErrors.new_password_confirmation"
              @clear-error="clearFieldError('new_password_confirmation')"
            />

            <div class="rounded-2xl border border-brand-light/20 bg-brand-lighter/10 p-4 text-sm text-brand-dark">
              Password changes apply to your next login immediately. Keep the new password private and store it
              somewhere secure.
            </div>

            <Button
              type="submit"
              class="bg-brand-dark text-white hover:bg-brand-darker"
              :loading="isSubmitting"
            >
              Save New Password
            </Button>
          </form>
        </div>
      </section>
    </div>
  </section>
</template>
