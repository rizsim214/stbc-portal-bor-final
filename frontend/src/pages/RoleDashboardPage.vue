<script setup lang="ts">
import { computed } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";

const route = useRoute();
const authStore = useAuthStore();

const roleLabel = computed(() => {
  const role = String(route.meta.role ?? "patient");
  return role.charAt(0).toUpperCase() + role.slice(1);
});
</script>

<template>
  <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <section class="rounded-xl border border-brand-light/30 bg-white p-6 shadow-sm">
      <h1 class="text-2xl font-bold text-brand-darker">
        {{ roleLabel }} Dashboard
      </h1>
      <p class="mt-2 text-sm text-brand-dark">
        Placeholder: dynamic dashboard content for the authenticated
        <span class="font-semibold">{{ roleLabel }}</span> role will be rendered here.
      </p>
      <p class="mt-3 text-sm text-brand-dark/80">
        Signed in as: {{ authStore.user?.email }}
      </p>
    </section>
  </main>
</template>
