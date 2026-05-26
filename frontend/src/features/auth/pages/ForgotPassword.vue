<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import { Button } from "@/shared/ui/button";
import { Input } from "@/shared/ui/input";
import { useAuthForms } from "../composables/useAuthForm";
import { authApi } from "../api/authApi";
import { handleAuthApiError } from "../utils/authError";

const router = useRouter();

const {
  forgotForm,
  forgotErrors,
  validateForgot
} = useAuthForms();

const isSubmitting = ref(false);
const submitError = ref("");
const submitSuccess = ref("");

function clearForgotError(): void {
  forgotErrors.email = "";
}

const onSubmit = async (): Promise<void> => {
  submitError.value = "";
  submitSuccess.value = "";

  if (!validateForgot()) {
    return;
  }

  isSubmitting.value = true;

  try {
    await authApi.forgotPassword(forgotForm);
    submitSuccess.value = "If the account exists, password reset instructions were sent to your email.";
  } catch (error: unknown) {
    const message = handleAuthApiError<"email">({
      error,
      fallbackMessage: "Failed to submit forgot password request.",
      setFieldError: (field, message) => {
        forgotErrors[field] = message;
      },
    });

    submitError.value = forgotErrors.email ? "" : message;
  } finally {
    isSubmitting.value = false;
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
          placeholder="you@example.com" autocomplete="email" @clear-error="clearForgotError" />

        <p v-if="submitSuccess" class="text-sm text-green-700">{{ submitSuccess }}</p>
        <p v-if="submitError" class="text-sm text-red-500">{{ submitError }}</p>
        <Button type="submit" class="bg-brand-dark hover:bg-brand-darker" :loading="isSubmitting">Submit</Button>
        <Button type="button" variant="outline" @click="onCancel">Cancel</Button>
      </form>
    </section>
  </main>
</template>
