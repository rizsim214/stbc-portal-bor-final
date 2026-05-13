<script setup lang="ts">
import {
  NavigationMenuRoot,
  NavigationMenuList,
  NavigationMenuItem,
  NavigationMenuLink,
} from "radix-vue";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { Button } from "@/shared/ui/button";
import stbcLogo from "@/assets/resources/stbc-logo.jpg";

const router = useRouter();
const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);

const headerClass = computed(() =>
  isScrolled.value
    ? "border-white/10 bg-brand-darker text-white"
    : "border-brand-light/30 bg-white/95 backdrop-blur"

);

const linkClass = computed(() =>
  isScrolled.value
    ? "text-white/90 hover:bg-white/10 hover:text-white"
    : "text-brand-dark hover:bg-brand-lighter/30 hover:text-brand-darker"
);

function onScroll() {
  isScrolled.value = globalThis.scrollY > 12;
}

onMounted(() => {
  onScroll();
  globalThis.addEventListener("scroll", onScroll, { passive: true });
});

onBeforeUnmount(() => {
  globalThis.removeEventListener("scroll", onScroll);
});

const navItems = [
  {
    id: 'about',
    title: 'About',
    link: "/about"
  },
  {
    id: 'services',
    title: 'Services',
    link: "/services"
  },
  {
    id: 'appointments',
    title: 'Appointments',
    link: "/appointments"
  },
];

function navigateTo(link: string) {
  router.push(link);
  isMobileMenuOpen.value = false;
}

</script>

<template>
  <header :class="headerClass" class="sticky top-0 z-50 border-b transition-colors duration-300">
    <nav class="mx-auto flex h-16 w-full max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
      <RouterLink to="/" class="flex items-center gap-3 transition hover:opacity-90" aria-label="STBC Home">
        <img :src="stbcLogo" alt="STBC Clinic Logo" class="h-10 w-auto rounded-sm object-contain" />
        <div class="hidden leading-tight sm:block">
          <p :class="!isScrolled ? 'text-brand-darker' : 'text-white'"
            class="text-xs font-semibold tracking-wide lg:text-sm">
            ST. BENEDICT'S BLOOD CLINIC
          </p>
          <p :class="!isScrolled ? 'text-brand-dark/80' : 'text-white/75'" class="text-xs">
            Trusted Care, Clear Results
          </p>
        </div>
      </RouterLink>

      <NavigationMenuRoot class="hidden md:block">
        <NavigationMenuList class="flex items-center gap-1">
          <NavigationMenuItem v-for="navItem in navItems" :key="navItem.id">
            <NavigationMenuLink as-child>
              <RouterLink :to="navItem.link" :class="linkClass"
                class="rounded-md px-3 py-2 text-sm font-medium transition">
                {{ navItem.title }}
              </RouterLink>
            </NavigationMenuLink>
          </NavigationMenuItem>

        </NavigationMenuList>
      </NavigationMenuRoot>

      <div class="hidden items-center gap-2 md:flex">
        <Button variant="outline" size="sm"
          :class="!isScrolled ? 'border-brand-light text-brand-dark hover:bg-brand-lighter/30' : 'border-white/40 text-white hover:bg-white/10'"
          @click="router.push('/login')">
          Login
        </Button>
        <Button size="sm"
          :class="!isScrolled ? 'bg-brand-highlight text-white hover:bg-brand-dark' : 'bg-white text-brand-darker hover:bg-brand-lighter'"
          @click="router.push('/register')">
          Register
        </Button>
      </div>

      <button type="button"
        class="inline-flex items-center rounded-md border px-3 py-2 text-xs font-semibold transition md:hidden"
        :class="!isScrolled ? 'border-brand-light text-brand-dark hover:bg-brand-lighter/30' : 'border-white/30 text-white hover:bg-white/10'"
        @click="isMobileMenuOpen = !isMobileMenuOpen">
        {{ isMobileMenuOpen ? "Close" : "Menu" }}
      </button>
    </nav>

    <div v-if="isMobileMenuOpen" class="border-t border-brand-light/20 bg-white/95 px-4 py-3 backdrop-blur md:hidden">
      <ul class="space-y-2">
        <li v-for="navItem in navItems" :key="navItem.id">
          <button type="button"
            class="w-full rounded-md bg-brand-lighter/20 px-3 py-2 text-left text-sm font-medium text-brand-dark transition hover:bg-brand-lighter/35"
            @click="navigateTo(navItem.link)">
            {{ navItem.title }}
          </button>
        </li>
      </ul>
      <div class="mt-3 grid grid-cols-2 gap-2">
        <Button variant="outline" size="sm" class="border-brand-light text-brand-dark hover:bg-brand-lighter/30"
          @click="navigateTo('/login')">
          Login
        </Button>
        <Button size="sm" class="bg-brand-highlight text-white hover:bg-brand-dark" @click="navigateTo('/register')">
          Register
        </Button>
      </div>
    </div>
  </header>
</template>
