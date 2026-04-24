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
  <main class="p-6 space-y-3">
    <h1 class="text-3xl font-bold text-blue-500">STBC-BOR-FINAL</h1>
    <p class="text-slate-700">{{ status }}</p>
  </main>
</template>
