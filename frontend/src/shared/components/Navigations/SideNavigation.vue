<script setup lang="ts">
import {
  AccordionContent,
  AccordionHeader,
  AccordionItem,
  AccordionRoot,
  AccordionTrigger,
} from "radix-vue";
import {
  ChevronDown,
  ClipboardList,
  House,
  Menu,
  ShieldCheck,
  UserRoundSearch,
  UserSquare2,
  Users,
  X,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import type { Component } from "vue";
import { useRoute } from "vue-router";
import type { RouteLocationRaw } from "vue-router";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import stbcLogo from "@/assets/resources/stbc-logo.jpg";

type SubItem = {
  id: string;
  label: string;
  to: RouteLocationRaw;
  routeName: string;
  icon: Component;
  roles?: Array<"admin" | "staff" | "patient">;
};

type SideNavGroup = {
  value: string;
  title: string;
  icon: Component;
  items: SubItem[];
};

const route = useRoute();
const authStore = useAuthStore();
const isMobileNavOpen = ref(false);

const currentRole = computed<"admin" | "staff" | "patient">(() => {
  const role = authStore.user?.role?.name?.toLowerCase();
  if (role === "admin" || role === "staff") return role;
  return "patient";
});

const accordionItems: SideNavGroup[] = [
  {
    value: "patients",
    title: "Patients",
    icon: Users,
    items: [
      {
        id: "patient-list",
        label: "Patient List",
        to: { name: "patientList" },
        routeName: "patientList",
        icon: UserRoundSearch,
        roles: ["staff", "admin"],
      },
      {
        id: "my-medical-record",
        label: "My Medical Records",
        to: { name: "patientMedicalRecord" },
        routeName: "patientMedicalRecord",
        icon: ClipboardList,
        roles: ["patient"],
      },
    ],
  },
  {
    value: "users",
    title: "Users",
    icon: ShieldCheck,
    items: [
      {
        id: "users-admin",
        label: "User Management",
        to: { name: "userManagement" },
        routeName: "userManagement",
        icon: UserSquare2,
        roles: ["admin"],
      },
    ],
  },
];

const visibleGroups = computed(() =>
  accordionItems
    .map((group) => ({
      ...group,
      items: group.items.filter(
        (item) => !item.roles || item.roles.includes(currentRole.value),
      ),
    }))
    .filter((group) => group.items.length > 0),
);

function getLinkClass(routeName: string): string {
  const isActive = route.name === routeName;
  return isActive
    ? "bg-brand-lighter/45 text-brand-darker"
    : "text-brand-dark/85 hover:bg-brand-lighter/30 hover:text-brand-darker";
}

function getDashboardLinkClass(): string {
  const dashboardRouteNames = [
    "dashboard",
    "dashboardOverview",
    "patientDashboard",
    "staffDashboard",
    "adminDashboard",
  ];
  const isActive =
    typeof route.name === "string" && dashboardRouteNames.includes(route.name);
  return isActive
    ? "bg-brand-lighter/45 text-brand-darker"
    : "text-brand-dark/85 hover:bg-brand-lighter/30 hover:text-brand-darker";
}

function closeMobileNav(): void {
  isMobileNavOpen.value = false;
}
</script>

<template>
  <button type="button"
    class="fixed left-4 top-20 z-40 inline-flex h-10 w-10 items-center justify-center rounded-md bg-white text-brand-darker shadow-md ring-1 ring-brand-light/40 md:hidden"
    aria-label="Open menu" @click="isMobileNavOpen = true">
    <Menu class="h-5 w-5" />
  </button>

  <button v-if="isMobileNavOpen" type="button" class="fixed inset-0 z-40 bg-black/35 md:hidden"
    aria-label="Close menu overlay" @click="closeMobileNav" />

  <aside :class="isMobileNavOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 w-70 border-r border-brand-light/30 bg-white p-5 transition-transform duration-300 md:sticky md:top-0 md:h-screen md:w-auto md:self-start md:overflow-y-auto md:p-6">
    <button type="button"
      class="mb-3 inline-flex h-9 w-9 items-center justify-center rounded-md text-brand-dark/75 transition hover:bg-brand-lighter/35 md:hidden"
      aria-label="Close menu" @click="closeMobileNav">
      <X class="h-4 w-4" />
    </button>

    <div v-if="authStore.isAuthenticated" class="mb-4 flex items-center gap-3">
      <img :src="stbcLogo" alt="STBC Clinic Logo" class="h-10 w-auto rounded-sm object-contain" />
      <div class="leading-tight">
        <p class="text-xs font-semibold tracking-wide text-brand-darker">
          ST. BENEDICT'S CLINIC
        </p>
        <p class="text-xs text-brand-dark/80">
          Trusted Care, Clear Results
        </p>
      </div>
    </div>
    <p class="mb-5 text-xs uppercase tracking-[0.12em] text-brand-dark/65">Navigation</p>
    <RouterLink :to="authStore.getDashboardPath()" :class="getDashboardLinkClass()" @click="closeMobileNav"
      class="mb-3 flex items-center gap-2 rounded-md px-2 py-2 text-sm font-semibold transition">
      <House class="h-4 w-4 shrink-0" />
      <span>Dashboard</span>
    </RouterLink>

    <AccordionRoot class="w-full" type="multiple">
      <AccordionItem v-for="item in visibleGroups" :key="item.value" :value="item.value"
        class="overflow-hidden border-t border-brand-light/35 first:border-t-0 focus-within:relative focus-within:z-10">
        <AccordionHeader class="flex">
          <AccordionTrigger
            class="group flex h-11 flex-1 items-center justify-between px-2 text-left text-sm font-semibold text-brand-darker outline-none transition hover:bg-brand-lighter/30">
            <span class="flex items-center gap-2">
              <component :is="item.icon" class="h-4 w-4 text-brand-dark/80" />
              <span>{{ item.title }}</span>
            </span>
            <ChevronDown
              class="h-4 w-4 text-brand-dark/80 transition-transform duration-300 group-data-[state=open]:rotate-180"
              aria-label="Expand or collapse section" />
          </AccordionTrigger>
        </AccordionHeader>

        <AccordionContent class="dashboard-accordion-content overflow-hidden">
          <ul class="space-y-1 px-1 pb-2">
            <li v-for="sub in item.items" :key="sub.id">
              <RouterLink :to="sub.to" :class="getLinkClass(sub.routeName)" @click="closeMobileNav"
                class="flex items-center gap-2 rounded-md px-2 py-2 text-sm transition">
                <component :is="sub.icon" class="h-4 w-4 shrink-0" />
                <span>{{ sub.label }}</span>
              </RouterLink>
            </li>
          </ul>
        </AccordionContent>
      </AccordionItem>
    </AccordionRoot>
  </aside>
</template>

<style scoped>
.dashboard-accordion-content[data-state="open"] {
  animation: dashboard-slide-down 200ms ease-out;
}

.dashboard-accordion-content[data-state="closed"] {
  animation: dashboard-slide-up 180ms ease-in;
}

@keyframes dashboard-slide-down {
  from {
    max-height: 0;
    opacity: 0.4;
  }

  to {
    max-height: 360px;
    opacity: 1;
  }
}

@keyframes dashboard-slide-up {
  from {
    max-height: 360px;
    opacity: 1;
  }

  to {
    max-height: 0;
    opacity: 0.4;
  }
}
</style>
