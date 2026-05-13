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
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import stbcLogo from "@/assets/resources/stbc-logo.jpg";

const router = useRouter();
const authStore = useAuthStore();
const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);
const isUserMenuOpen = ref(false);
const desktopUserMenuRef = ref<HTMLElement | null>(null);
const mobileUserMenuRef = ref<HTMLElement | null>(null);

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

const homeLink = computed(() =>
  authStore.isAuthenticated ? authStore.getDashboardPath() : "/"
);

const userInitials = computed(() => {
  const email = authStore.user?.email ?? "";
  if (!email) return "U";
  return email.slice(0, 1).toUpperCase();
});

const avatarClass = computed(() =>
  isScrolled.value
    ? "bg-white text-brand-darker hover:bg-brand-lighter"
    : "bg-brand-highlight text-white hover:bg-brand-dark"
);

type UserMenuAction = "profile" | "settings" | "logout";

type UserMenuItem = {
  id: UserMenuAction;
  label: string;
  danger?: boolean;
};

const userMenuItems: UserMenuItem[] = [
  { id: "profile", label: "Profile" },
  { id: "settings", label: "Settings" },
  { id: "logout", label: "Logout", danger: true },
];

function onScroll() {
  isScrolled.value = globalThis.scrollY > 12;
}

function onDocumentClick(event: MouseEvent) {
  if (!isUserMenuOpen.value) return;
  const target = event.target as Node | null;
  const insideDesktop =
    Boolean(target) &&
    Boolean(desktopUserMenuRef.value) &&
    desktopUserMenuRef.value!.contains(target as Node);
  const insideMobile =
    Boolean(target) &&
    Boolean(mobileUserMenuRef.value) &&
    mobileUserMenuRef.value!.contains(target as Node);

  if (!insideDesktop && !insideMobile) {
    isUserMenuOpen.value = false;
  }
}

onMounted(() => {
  onScroll();
  globalThis.addEventListener("scroll", onScroll, { passive: true });
  globalThis.addEventListener("click", onDocumentClick);
});

onBeforeUnmount(() => {
  globalThis.removeEventListener("scroll", onScroll);
  globalThis.removeEventListener("click", onDocumentClick);
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

async function onLogout() {
  isUserMenuOpen.value = false;
  await authStore.logout();
  router.push("/login");
  isMobileMenuOpen.value = false;
}

function navigateIfRouteExists(path: string, missingMessage: string) {
  isUserMenuOpen.value = false;
  const target = router.resolve(path);
  if (target.matched.length) {
    router.push(path);
    return;
  }
  globalThis.alert(missingMessage);
}

function onUserMenuAction(action: UserMenuAction) {
  if (action === "logout") {
    onLogout();
    return;
  }

  if (action === "profile") {
    navigateIfRouteExists("/profile", "Profile page is not available yet.");
    return;
  }

  navigateIfRouteExists("/settings", "Settings page is not available yet.");
}

</script>

<template>
  <header :class="headerClass" class="sticky top-0 z-50 border-b transition-colors duration-300">
    <nav class="mx-auto flex h-16 w-full max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
      <RouterLink :to="homeLink" class="flex items-center gap-3 transition hover:opacity-90" aria-label="STBC Home">
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

      <NavigationMenuRoot v-if="!authStore.isAuthenticated" class="hidden md:block">
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
        <template v-if="authStore.isAuthenticated">
          <button type="button" class="rounded-md px-2 py-1 text-sm font-medium transition"
            :class="!isScrolled ? 'text-brand-dark hover:bg-brand-lighter/30' : 'text-white/90 hover:bg-white/10'"
            @click="router.push(authStore.getDashboardPath())">
            Dashboard
          </button>
          <div ref="desktopUserMenuRef" class="relative">
            <button type="button"
              class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold transition"
              :class="avatarClass" @click="isUserMenuOpen = !isUserMenuOpen">
              {{ userInitials }}
            </button>
            <div v-if="isUserMenuOpen"
              class="absolute right-0 z-50 mt-2 w-44 rounded-md border border-brand-light/30 bg-white p-1 shadow-lg">
              <template v-for="item in userMenuItems" :key="item.id">
                <hr v-if="item.id === 'logout'" class="my-1 border-brand-light/40">
                <button type="button" class="w-full rounded px-3 py-2 text-left text-sm transition" :class="item.danger
                  ? 'text-red-600 hover:bg-red-50'
                  : 'text-brand-dark hover:bg-brand-lighter/30'" @click="onUserMenuAction(item.id)">
                  {{ item.label }}
                </button>
              </template>
            </div>
          </div>
        </template>
        <template v-else>
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
        </template>
      </div>

      <div v-if="authStore.isAuthenticated" ref="mobileUserMenuRef" class="relative md:hidden">
        <button type="button"
          class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold transition"
          :class="avatarClass" @click="isUserMenuOpen = !isUserMenuOpen">
          {{ userInitials }}
        </button>
        <div v-if="isUserMenuOpen"
          class="absolute right-0 z-50 mt-2 w-44 rounded-md border border-brand-light/30 bg-white p-1 shadow-lg">
          <template v-for="item in userMenuItems" :key="item.id">
            <hr v-if="item.id === 'logout'" class="my-1 border-brand-light/40">
            <button type="button" class="w-full rounded px-3 py-2 text-left text-sm transition" :class="item.danger
              ? 'text-red-600 hover:bg-red-50'
              : 'text-brand-dark hover:bg-brand-lighter/30'" @click="onUserMenuAction(item.id)">
              {{ item.label }}
            </button>
          </template>
        </div>
      </div>

      <button type="button" v-if="!authStore.isAuthenticated"
        class="inline-flex items-center rounded-md border px-3 py-2 text-xs font-semibold transition md:hidden"
        :class="!isScrolled ? 'border-brand-light text-brand-dark hover:bg-brand-lighter/30' : 'border-white/30 text-white hover:bg-white/10'"
        @click="isMobileMenuOpen = !isMobileMenuOpen">
        {{ isMobileMenuOpen ? "Close" : "Menu" }}
      </button>
    </nav>

    <div v-if="!authStore.isAuthenticated && isMobileMenuOpen"
      class="border-t border-brand-light/20 bg-white/95 px-4 py-3 backdrop-blur md:hidden">
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
