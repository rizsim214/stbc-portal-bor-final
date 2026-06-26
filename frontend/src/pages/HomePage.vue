<script setup lang="ts">
import { ArrowRight, CalendarDays } from "lucide-vue-next";
import stbcLogo from "@/assets/resources/stbc-logo.jpg";

const quickActions = [
  {
    title: "Set Appointment",
    description: "Send an appointment request online.",
    to: "/appointments",
  },
  {
    title: "View Services",
    description: "Review laboratory tests, checkups, and home services.",
    to: "/services",
  },
  {
    title: "About the Clinic",
    description: "See the clinic's approach and what patients can expect.",
    to: "/about",
  },
];

const patientFlow = [
  "Choose the service or concern you need help with.",
  "Send an appointment request before your clinic visit.",
  "Let the staff guide the request to the right doctor or service area.",
];
</script>

<template>
  <main
    class="min-h-screen bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-brand-lighter)_18%,white),white_26%,color-mix(in_srgb,var(--color-brand-light)_8%,white))]">
    <div class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
      <section class="relative overflow-hidden border-b border-brand-light/20 pb-12">
        <div class="absolute right-0 top-0 hidden h-56 w-56 rounded-full bg-brand-lighter/30 blur-3xl lg:block" />

        <div class="relative grid gap-10 lg:grid-cols-[1.25fr_0.75fr] lg:items-end">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-highlight">
              St. Benedict Medical Care
            </p>
            <h1 class="mt-4 max-w-4xl text-4xl font-semibold tracking-tight text-brand-darker sm:text-5xl lg:text-6xl">
              Trusted care, clear results, and a simpler way to begin.
            </h1>
            <p class="mt-5 max-w-2xl text-sm leading-7 text-brand-dark/82 sm:text-lg">
              A clinic experience built to help patients book earlier, visit with better direction, and avoid
              unnecessary return trips whenever possible.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
              <RouterLink to="/appointments"
                class="inline-flex items-center justify-center gap-2 rounded-full bg-brand-dark px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-darker">
                <CalendarDays class="h-4 w-4" />
                Set Appointment
              </RouterLink>
              <RouterLink to="/services"
                class="inline-flex items-center justify-center gap-2 rounded-full border border-brand-light/35 px-5 py-3 text-sm font-semibold text-brand-darker transition hover:bg-brand-lighter/12">
                Explore Services
              </RouterLink>
            </div>
          </div>

        </div>
      </section>

      <section class="grid gap-12 py-12 lg:grid-cols-[0.95fr_1.05fr]">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-highlight">Start Here</p>
          <h2 class="mt-3 text-2xl font-semibold text-brand-darker sm:text-3xl">
            Use the homepage to move quickly, not to read everything.
          </h2>
          <p class="mt-4 max-w-xl text-sm leading-7 text-brand-dark/82 sm:text-base">
            The detailed breakdown already lives on the Services page. This page keeps the next steps clear: book,
            review services, or learn about the clinic.
          </p>
        </div>

        <div class="divide-y divide-brand-light/20 border-t border-brand-light/20">
          <RouterLink v-for="item in quickActions" :key="item.title" :to="item.to"
            class="group grid gap-2 py-5 transition first:pt-4 hover:translate-x-1">
            <div class="flex items-center justify-between gap-4">
              <p class="text-lg font-semibold text-brand-darker">{{ item.title }}</p>
              <ArrowRight class="h-4 w-4 shrink-0 text-brand-highlight transition group-hover:translate-x-1" />
            </div>
            <p class="max-w-xl text-sm leading-6 text-brand-dark/78">{{ item.description }}</p>
          </RouterLink>
        </div>
      </section>

      <section class="grid gap-12 border-t border-brand-light/20 py-12 lg:grid-cols-[1.05fr_0.95fr]">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-highlight">Patient Flow</p>
          <h2 class="mt-3 text-2xl font-semibold text-brand-darker sm:text-3xl">
            A clearer path from request to clinic visit.
          </h2>
        </div>

        <ol class="space-y-6">
          <li v-for="(step, index) in patientFlow" :key="step" class="grid grid-cols-[auto_1fr] gap-4">
            <span
              class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-darker text-sm font-semibold text-white">
              {{ index + 1 }}
            </span>
            <p class="pt-2 text-sm leading-7 text-brand-dark/84 sm:text-base">{{ step }}</p>
          </li>
        </ol>
      </section>
    </div>
  </main>
</template>
