<script setup lang="ts">
const clinicInfo = {
  name: "St. Benedict Laboratory, Drug Testing & Medical Clinic",
  address: "Sabate Bldg, Real St., Brgy. Songco, Borongan City, Eastern Samar",
};
const currentYear = new Date().getFullYear();
</script>

<template>
  <footer class="mt-10 border-t border-brand-light/25 bg-linear-to-r from-brand-darker to-brand-dark px-4 py-5 sm:px-6 lg:px-8">
    <div class="mx-auto w-full max-w-6xl text-center text-white/90">
      <p class="text-sm font-semibold tracking-wide text-white">{{ clinicInfo.name }}</p>
      <p class="mt-1 text-xs sm:text-sm text-white/80">{{ clinicInfo.address }}</p>
      <p class="mt-3 text-[11px] sm:text-xs text-white/70">
        &copy; {{ currentYear }} {{ clinicInfo.name }}. All rights reserved.
      </p>
    </div>
  </footer>
</template>
