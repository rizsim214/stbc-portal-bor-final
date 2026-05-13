<script setup lang="ts">
import { useRouter } from "vue-router";
import { Button } from "@/shared/ui/button";
import { Input } from "@/shared/ui/input";
import { useAuthForms } from "../composables/useAuthForm";

const router = useRouter();

const {
  forgotForm,
  forgotErrors,
  validateForgot
} = useAuthForms();

const onSubmit = (): void => {
  // API is submitted here
  if (validateForgot()) {
    console.log("Forgot Password Submit", { ...forgotForm });
  }
};

const onCancel = (): void => {
  router.push("/login");
};

</script>

<template>
  <main
    class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-6xl items-center justify-center px-4 py-6 sm:px-6 lg:px-8">
    <section
      class="w-full max-w-md rounded-2xl border border-brand-light/30 bg-white p-6 shadow-[0_12px_28px_-16px_rgba(21,5,120,0.45)] sm:p-8">
      <h1 class="text-2xl font-semibold text-brand-darker">Remember Password</h1>
      <p class="mt-2 text-sm text-brand-dark">New password will be submitted to your email address.</p>

      <form class="mt-6 space-y-4" @submit.prevent="onSubmit">
        <Input id="email" v-model="forgotForm.email" type="email" :error="forgotErrors.email" label="Retrieval Email"
          placeholder="you@example.com" autocomplete="email" />

        <Button type="submit" class="bg-brand-dark hover:bg-brand-darker">Submit</Button>
        <Button type="button" variant="outline" @click="onCancel">Cancel</Button>
      </form>
    </section>
  </main>
</template>
