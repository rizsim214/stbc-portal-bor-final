<script setup lang="ts">
import bloodServices from "@/assets/resources/blood services.jpg";
import clinicTests from "@/assets/resources/clinic tests.jpg";
import homeService from "@/assets/resources/home service.jpg";
import labServices from "@/assets/resources/lab services.jpg";
import servicesOffered from "@/assets/resources/services offered.jpg";

const primaryService = {
  title: "Services Overview",
  description:
    "This is our primary service catalog, presenting the top healthcare offerings available at the clinic.",
  image: servicesOffered,
  alt: "Poster showing services offered by the clinic",
};

const services = [
  {
    title: "Laboratory Services",
    description: "Routine and requested laboratory diagnostics for patient care.",
    image: labServices,
    alt: "Clinic laboratory services information",
  },
  {
    title: "Blood Services",
    description: "Blood extraction and related services performed by trained staff.",
    image: bloodServices,
    alt: "Clinic blood services information",
  },
  {
    title: "Clinic Tests",
    description: "Essential clinic-based tests to support timely assessment.",
    image: clinicTests,
    alt: "Clinic tests information",
  },
  {
    title: "Home Service",
    description: "Selected services available for patients needing at-home support.",
    image: homeService,
    alt: "Clinic home service information",
  },
];
</script>

<template>
  <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <section
      class="rounded-2xl border border-brand-light/30 bg-gradient-to-r from-white via-brand-lighter/15 to-white p-6 shadow-sm sm:p-8">
      <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-highlight">STBC Medical Care</p>
      <h1 class="mt-3 text-3xl font-semibold tracking-tight text-brand-darker sm:text-4xl">Our Services</h1>
      <p class="mt-4 max-w-3xl text-base leading-7 text-brand-dark sm:text-lg">
        Explore the clinic services available to support preventive care, diagnostics, and patient follow-up.
        Information is presented in a clear format for easy reading.
      </p>
      <p class="mt-3 text-sm text-brand-dark/80 sm:text-base">Tip: You can zoom images for larger viewing when needed.
      </p>
    </section>

    <section class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2">
      <article
        class="overflow-hidden rounded-2xl border border-brand-light/30 bg-white shadow-[0_12px_28px_-14px_rgba(21,5,120,0.6)] md:col-span-2">
        <div class="bg-gradient-to-r from-brand-lighter/20 via-white to-brand-lighter/20 p-3 sm:p-4">
          <img :src="primaryService.image" :alt="primaryService.alt" class="w-full rounded-xl object-contain"
            loading="lazy" />
        </div>
        <div class="px-5 pb-6 pt-4 sm:px-6">
          <p class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-highlight sm:text-sm">
            Featured Main Services
          </p>
          <h2 class="mt-1 text-2xl font-semibold text-brand-darker sm:text-3xl">{{ primaryService.title }}</h2>
          <p class="mt-2 text-base leading-7 text-brand-dark sm:text-lg">{{ primaryService.description }}</p>
        </div>
      </article>

      <article v-for="service in services" :key="service.title"
        class="overflow-hidden rounded-2xl border border-brand-light/25 bg-white shadow-[0_8px_24px_-16px_rgba(21,5,120,0.45)] transition hover:shadow-[0_14px_30px_-16px_rgba(21,5,120,0.55)]">
        <div class="bg-brand-lighter/10 p-3">
          <img :src="service.image" :alt="service.alt" class="w-full rounded-xl object-contain" loading="lazy" />
        </div>
        <div class="px-5 pb-6 pt-4 sm:px-6">
          <h2 class="text-xl font-semibold text-brand-darker sm:text-2xl">{{ service.title }}</h2>
          <p class="mt-2 text-base leading-7 text-brand-dark">{{ service.description }}</p>
        </div>
      </article>
    </section>
  </main>
</template>
