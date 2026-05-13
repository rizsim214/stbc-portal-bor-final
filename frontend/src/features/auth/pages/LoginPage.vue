<script setup lang="ts">
import { Button } from "@/shared/ui/button";
import { Input } from "@/shared/ui/input";
import { useRouter } from "vue-router";
import { useAuthForms } from "../composables/useAuthForm";

const {
  loginForm,
  loginErrors,
  validateLogin
} = useAuthForms();

const onSubmit = (): void => {
  if (validateLogin()) {
    // Submitted Here
    console.log("Login submit ", { ...loginForm });
    console.log("validate login ", validateLogin());
  }
};

const router = useRouter();

const goToForgotPassword = (): void => {
  router.push("/forgot-password"); // change path to your route
};

</script>

<template>
  <main
    class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-6xl items-center justify-center px-4 py-6 sm:px-6 lg:px-8">
    <section
      class="w-full max-w-md rounded-2xl border border-brand-light/30 bg-white p-6 shadow-[0_12px_28px_-16px_rgba(21,5,120,0.45)] sm:p-8">
      <h1 class="text-2xl font-semibold text-brand-darker">Sign In</h1>
      <p class="mt-2 text-sm text-brand-dark">Sign in to continue to your account.</p>

      <form class="mt-6 space-y-4" @submit.prevent="onSubmit">
        <Input id="email" v-model="loginForm.email" :error="loginErrors.email" type="email" label="Email"
          placeholder="you@example.com" autocomplete="email" />

        <Input id="password" v-model="loginForm.password" :error="loginErrors.password" type="password" label="Password"
          placeholder="Enter your password" autocomplete="current-password" />

        <div class="flex justify-end">
          <button type="button"
            class="text-sm font-medium text-brand-highlight transition hover:text-brand-darker hover:underline"
            @click="goToForgotPassword">
            Forgot password?
          </button>
        </div>
        <Button type="submit" class="bg-brand-dark hover:bg-brand-darker">Submit</Button>
        <p class="text-sm text-center">Don't have an account yet? <RouterLink to="/register"
            class="text-brand-dark/55 font-bold">
            Register
            Now!</RouterLink>
        </p>
      </form>
    </section>
  </main>
</template>
