<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
  userId: string;
}>();

const profiles = [
  { id: 101, name: "Maria Dela Cruz", email: "maria.delacruz@example.com", phone: "+63 917 000 0101", status: "Active", lastVisit: "2026-05-12" },
  { id: 102, name: "John Reyes", email: "john.reyes@example.com", phone: "+63 917 000 0102", status: "Active", lastVisit: "2026-05-09" },
  { id: 103, name: "Patricia Gomez", email: "patricia.gomez@example.com", phone: "+63 917 000 0103", status: "Pending", lastVisit: "2026-04-30" },
  { id: 104, name: "Ramon Santos", email: "ramon.santos@example.com", phone: "+63 917 000 0104", status: "Inactive", lastVisit: "2026-03-22" },
];

const profile = computed(() => {
  const userId = Number(props.userId);
  return profiles.find((entry) => entry.id === userId) ?? profiles[0];
});
</script>

<template>
  <section class="rounded-xl border border-brand-light/30 bg-white p-6">
    <p class="text-xs uppercase tracking-[0.12em] text-brand-dark/65">User Profile</p>
    <h1 class="mt-2 text-2xl font-semibold text-brand-darker">{{ profile.name }}</h1>
    <p class="mt-2 text-sm text-brand-dark">Profile summary for the selected patient record.</p>

    <div class="mt-6 grid gap-4 md:grid-cols-2">
      <div class="rounded-lg border border-brand-light/25 bg-brand-lighter/10 p-4">
        <p class="text-xs uppercase tracking-widest text-brand-dark/65">Contact</p>
        <dl class="mt-3 space-y-2 text-sm text-brand-darker">
          <div class="flex justify-between gap-4">
            <dt class="text-brand-dark/70">Email</dt>
            <dd class="font-medium">{{ profile.email }}</dd>
          </div>
          <div class="flex justify-between gap-4">
            <dt class="text-brand-dark/70">Phone</dt>
            <dd class="font-medium">{{ profile.phone }}</dd>
          </div>
        </dl>
      </div>

      <div class="rounded-lg border border-brand-light/25 bg-brand-lighter/10 p-4">
        <p class="text-xs uppercase tracking-widest text-brand-dark/65">Status</p>
        <dl class="mt-3 space-y-2 text-sm text-brand-darker">
          <div class="flex justify-between gap-4">
            <dt class="text-brand-dark/70">Account State</dt>
            <dd class="font-medium">{{ profile.status }}</dd>
          </div>
          <div class="flex justify-between gap-4">
            <dt class="text-brand-dark/70">Last Visit</dt>
            <dd class="font-medium">{{ profile.lastVisit }}</dd>
          </div>
        </dl>
      </div>
    </div>
  </section>
</template>
