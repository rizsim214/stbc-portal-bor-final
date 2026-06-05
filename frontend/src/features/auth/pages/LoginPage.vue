<script setup lang="ts">
import { Button } from "@/shared/ui/button";
import { Input } from "@/shared/ui/input";
import { useRouter } from "vue-router";
import { ref } from "vue";
import { useAuthForms } from "../composables/useAuthForm";
import { useAuthStore } from "../stores/useAuthStore";
import { handleAuthApiError } from "../utils/authError";

const {
  loginForm,
  loginErrors,
  validateLogin
} = useAuthForms();

const authStore = useAuthStore();
const submitError = ref("");
const router = useRouter();

function clearLoginError(field: "email" | "password"): void {
  loginErrors[field] = "";
}

const onSubmit = async (): Promise<void> => {
  submitError.value = "";

  if (!validateLogin()) {
    return;
  }

  try {
    await authStore.login({
      email: loginForm.email,
      password: loginForm.password,
    });

    const redirect = (router.currentRoute.value.query.redirect as string) || authStore.getDashboardPath();
    await router.push(redirect);
  } catch (error: unknown) {
    const message = handleAuthApiError<"email" | "password">({
      error,
      fallbackMessage: "Login failed. Please try again.",
      setFieldError: (field, message) => {
        loginErrors[field] = message;
      },
    });

    submitError.value = loginErrors.email || loginErrors.password ? "" : message;
  }
};

</script>

<template>
  <main
    class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-6xl items-center justify-center px-4 py-6 sm:px-6 lg:px-8">
    <section
      class="w-full max-w-md rounded-2xl border border-brand-light/30 bg-white p-6 shadow-[0_12px_28px_-16px_rgba(21,5,120,0.45)] sm:p-8">
      <h1 class="text-2xl font-semibold text-brand-darker">Sign in to your account</h1>
      <p class="mt-2 text-sm text-brand-dark">Enter your email below to login to your account</p>
      <p class="mt-1 text-xs text-brand-dark/80">
        Please use the email you used to book your appointment and the password sent to your email.
      </p>

      <form class="mt-6 space-y-4" @submit.prevent="onSubmit">
        <Input id="email" v-model="loginForm.email" :error="loginErrors.email" type="email" label="Email"
          placeholder="you@example.com" autocomplete="email" @clear-error="clearLoginError('email')" />

        <Input id="password" v-model="loginForm.password" :error="loginErrors.password" type="password" label="Password"
          placeholder="Enter your password" autocomplete="current-password"
          @clear-error="clearLoginError('password')" />

        <p v-if="submitError" class="text-sm text-red-500">{{ submitError }}</p>
        <Button type="submit" :loading="authStore.isLoading" class="bg-brand-dark hover:bg-brand-darker">
          Sign In
        </Button>
      </form>
    </section>
  </main>
</template>
