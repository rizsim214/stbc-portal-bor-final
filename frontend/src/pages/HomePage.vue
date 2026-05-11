<script setup lang="ts">
import { onMounted, ref } from "vue";

const status = ref("Checking backend connection...");

onMounted(async () => {
  try {
    const response = await fetch("/api/health");
    if (!response.ok) {
      status.value = `Backend error: ${response.status}`;
      return;
    }

    const payload = (await response.json()) as { status?: string };
    status.value =
      payload.status === "ok"
        ? "Frontend is connected to backend API."
        : "Backend responded with unexpected payload.";
  } catch {
    status.value = "Cannot reach backend API.";
  }
});
</script>

<template>
  <main class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
    <section class="rounded-2xl border border-brand-light/30 bg-linear-to-r from-white via-brand-lighter/10 to-white p-5 shadow-sm sm:p-8">
      <h1 class="text-2xl font-bold text-brand-darker sm:text-4xl">STBC Medical Care</h1>
      <p class="mt-3 text-sm text-brand-dark sm:text-base">{{ status }}</p>
    </section>
  </main>
</template>
