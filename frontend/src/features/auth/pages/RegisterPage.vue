<script setup lang="ts">
import { useRouter } from "vue-router";
import { Button } from "@/shared/ui/button";
import { Input } from "@/shared/ui/input";
import { useAuthForms } from "../composables/useAuthForm";

const router = useRouter();

const {
  registerForm,
  registerErrors,
  validateRegister
} = useAuthForms();

const onSubmit = (): void => {
  // Submit registration to API here
  if (validateRegister()) {
    console.log("Register Submit", { ...registerForm });
    console.log("validate login ", validateRegister());
  }
};

const goToLogin = (): void => {
  router.push("/login");
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
        <Input id="email" v-model="registerForm.email" :error="registerErrors.email" type="email" label="Email"
          placeholder="you@example.com" autocomplete="email" />
        <Input id="password" v-model="registerForm.password" :error="registerErrors.password" type="password"
          label="Password" placeholder="Enter your password" autocomplete="current-password" />
        <Input id="passwordConfirm" v-model="registerForm.passwordConfirm" :error="registerErrors.passwordConfirm"
          type="password" label="Confirm Password" placeholder="Enter password again" />

        <Button type="submit" class="bg-brand-dark hover:bg-brand-darker">Register</Button>

        <div class="flex flex-col items-center gap-3 pt-1">
          <div class="flex w-80 items-center gap-3">
            <span class="h-px flex-1 bg-brand-dark/50"></span>
            <span class="text-xs font-semibold uppercase tracking-[0.12em] text-brand-dark/80">Or</span>
            <span class="h-px flex-1 bg-brand-dark/50"></span>
          </div>
          <Button type="button" variant="outline" size="sm" class="max-w-full" @click="goToLogin">
            Login
          </Button>
        </div>
      </form>
    </section>
  </main>
</template>
