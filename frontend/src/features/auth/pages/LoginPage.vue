<script setup lang="ts">
import { reactive } from "vue";
import { Button } from "@/shared/ui/button";
import { Input } from "@/shared/ui/input";
import { useRouter } from "vue-router";

const form = reactive({
  email: "",
  password: "",
});

const onSubmit = (): void => {
  // Connect to auth API here.
  console.log("Login submit", { ...form });
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
        <Input id="email" v-model="form.email" type="email" label="Email" placeholder="you@example.com"
          autocomplete="email" />

        <Input id="password" v-model="form.password" type="password" label="Password" placeholder="Enter your password"
          autocomplete="current-password" />

        <div class="flex justify-end">
          <button type="button"
            class="text-sm font-medium text-brand-highlight transition hover:text-brand-darker hover:underline"
            @click="goToForgotPassword">
            Forgot password?
          </button>
        </div>
        <Button type="submit" class="bg-brand-dark hover:bg-brand-darker">Submit</Button>
      </form>
    </section>
  </main>
</template>
