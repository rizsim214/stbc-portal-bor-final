<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import { Button } from "@/shared/ui/button";
import { Input } from "@/shared/ui/input";
import { useAuthForms } from "../composables/useAuthForm";
import { authApi } from "../api/authApi";
import { useAuthStore } from "../stores/useAuthStore";
import { handleAuthApiError } from "../utils/authError";


const {
  registerForm,
  registerErrors,
  validateRegister
} = useAuthForms();

const router = useRouter();
const authStore = useAuthStore();
const isSubmitting = ref(false);
const submitError = ref("");

function clearRegisterError(field: "name" | "email" | "password" | "passwordConfirm"): void {
  registerErrors[field] = "";
}

function onPasswordInput(): void {
  clearRegisterError("password");
  if (registerForm.passwordConfirm === registerForm.password) {
    clearRegisterError("passwordConfirm");
  }
}

const onSubmit = async (): Promise<void> => {
  submitError.value = "";

  if (!validateRegister()) {
    return;
  }

  isSubmitting.value = true;

  try {
    const { data } = await authApi.register(registerForm);
    authStore.setSession(data.data.token, data.data.user);
    await router.push(authStore.getDashboardPath());
  } catch (error: unknown) {
    const message = handleAuthApiError<"name" | "email" | "password" | "passwordConfirm">({
      error,
      fallbackMessage: "Registration failed. Please try again.",
      fieldMap: {
        password_confirmation: "passwordConfirm",
      },
      setFieldError: (field, message) => {
        registerErrors[field] = message;
      },
    });

    submitError.value =
      registerErrors.name ||
      registerErrors.email ||
      registerErrors.password ||
      registerErrors.passwordConfirm
        ? ""
        : message;
  } finally {
    isSubmitting.value = false;
  }
};

</script>

<template>
  <main
    class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-6xl items-center justify-center px-4 py-6 sm:px-6 lg:px-8">
    <section
      class="w-full max-w-md rounded-2xl border border-brand-light/30 bg-white p-6 shadow-[0_12px_28px_-16px_rgba(21,5,120,0.45)] sm:p-8">
      <h1 class="text-2xl font-semibold text-brand-darker">Sign Up</h1>
      <p class="mt-2 text-sm text-brand-dark">Sign up to create a user account.</p>

      <form class="mt-6 space-y-4" @submit.prevent="onSubmit">
        <Input id="name" v-model="registerForm.name" :error="registerErrors.name" type="text" label="Name"
          placeholder="John Doe" autocomplete="name" @clear-error="clearRegisterError('name')" />
        <Input id="email" v-model="registerForm.email" :error="registerErrors.email" type="email" label="Email"
          placeholder="you@example.com" autocomplete="email" @clear-error="clearRegisterError('email')" />
        <Input id="password" v-model="registerForm.password" :error="registerErrors.password" type="password"
          label="Password" placeholder="Enter your password" autocomplete="current-password"
          @clear-error="onPasswordInput" />
        <Input id="passwordConfirm" v-model="registerForm.passwordConfirm" :error="registerErrors.passwordConfirm"
          type="password" label="Confirm Password" placeholder="Enter password again"
          @clear-error="clearRegisterError('passwordConfirm')" />

        <p v-if="submitError" class="text-sm text-red-500">{{ submitError }}</p>
        <Button type="submit" class="bg-brand-dark hover:bg-brand-darker" :loading="isSubmitting">Register</Button>

        <div class="flex flex-col items-center gap-3 pt-1">
          <div class="flex w-80 items-center gap-3">
            <span class="h-px flex-1 bg-brand-dark/50"></span>
            <span class="text-xs font-semibold uppercase tracking-[0.12em] text-brand-dark/80">Or</span>
            <span class="h-px flex-1 bg-brand-dark/50"></span>
          </div>
          <p class="text-sm">Already have an account? <RouterLink to="/login" class="text-brand-dark/55 font-bold">Sign
              In!
            </RouterLink>
          </p>
        </div>
      </form>
    </section>
  </main>
</template>
