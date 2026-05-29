<script setup lang="ts">
import {
  NavigationMenuRoot,
  NavigationMenuList,
  NavigationMenuItem,
  NavigationMenuLink,
} from "radix-vue";
import { Menu, X } from "lucide-vue-next";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { Button } from "@/shared/ui/button";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import stbcLogo from "@/assets/resources/stbc-logo.jpg";

const router = useRouter();
const authStore = useAuthStore();
const isMobileMenuOpen = ref(false);
const isUserMenuOpen = ref(false);
const desktopUserMenuRef = ref<HTMLElement | null>(null);
const mobileUserMenuRef = ref<HTMLElement | null>(null);

const headerClass = "border-brand-light/30 bg-white/95 backdrop-blur";
const linkClass = "text-brand-dark hover:bg-brand-lighter/30 hover:text-brand-darker";

const homeLink = computed(() =>
  authStore.isAuthenticated ? authStore.getDashboardPath() : "/"
);

const userInitials = computed(() => {
  const email = authStore.user?.email ?? "";
  if (!email) return "U";
  return email.slice(0, 1).toUpperCase();
});

const userDisplayName = computed(() => {
  const name = authStore.user?.name?.trim();
  if (name) return name;
  return authStore.user?.email ?? "User";
});

const avatarClass = "bg-brand-highlight text-white hover:bg-brand-dark";

type UserMenuAction = "profile" | "settings" | "logout";

type UserMenuItem = {
  id: UserMenuAction;
  label: string;
  danger?: boolean;
};

const userMenuItems: UserMenuItem[] = [
  { id: "profile", label: "Profile" }, // purpose: updating user related info (e.g name, password, phone, etc... )
  { id: "settings", label: "Settings" },
  { id: "logout", label: "Logout", danger: true },
];

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
  globalThis.addEventListener("click", onDocumentClick);
});

onBeforeUnmount(() => {
  globalThis.removeEventListener("click", onDocumentClick);
});

const navItems = [
  {
    id: 'home',
    title: 'Home',
    link: "/"
  },
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
  <header :class="headerClass" class="z-50 border-b transition-colors duration-300">
    <nav class="mx-auto flex h-16 w-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
      <RouterLink v-if="!authStore.isAuthenticated" :to="homeLink"
        class="flex items-center gap-3 transition hover:opacity-90" aria-label="STBC Home">
        <img :src="stbcLogo" alt="STBC Clinic Logo" class="h-10 w-auto rounded-sm object-contain" />
        <div class="leading-tight">
          <p class="text-xs font-semibold tracking-wide text-brand-darker lg:text-sm">
            ST. BENEDICT'S CLINIC
          </p>
          <p class="text-xs text-brand-dark/80">
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

      <div v-if="authStore.isAuthenticated" class="flex-1" />

      <div class="hidden items-center gap-2 md:flex">
        <template v-if="authStore.isAuthenticated">
          <div ref="desktopUserMenuRef" class="relative">
            <div class="flex items-center gap-2">
              <span class="max-w-40 truncate text-xs font-semibold text-brand-darker">{{ userDisplayName }}</span>
              <button type="button"
                class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold transition"
                :class="avatarClass" @click="isUserMenuOpen = !isUserMenuOpen">
                {{ userInitials }}
              </button>
            </div>
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
          <Button variant="outline" size="sm" class="border-brand-light text-brand-dark hover:bg-brand-lighter/30"
            @click="router.push('/login')">
            Login
          </Button>
        </template>
      </div>

      <div v-if="authStore.isAuthenticated" ref="mobileUserMenuRef" class="relative md:hidden">
        <div class="flex items-center gap-2">
          <span class="max-w-28 truncate text-xs font-semibold text-brand-darker">{{ userDisplayName }}</span>
          <button type="button"
            class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold transition"
            :class="avatarClass" @click="isUserMenuOpen = !isUserMenuOpen">
            {{ userInitials }}
          </button>
        </div>
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
        class="inline-flex items-center rounded-md border border-brand-light px-3 py-2 text-xs font-semibold text-brand-dark transition hover:bg-brand-lighter/30 md:hidden"
        :aria-label="isMobileMenuOpen ? 'Close navigation menu' : 'Open navigation menu'"
        @click="isMobileMenuOpen = !isMobileMenuOpen">
        <Menu v-if="!isMobileMenuOpen" class="h-5 w-5" aria-hidden="true" />
        <X v-else class="h-5 w-5" aria-hidden="true" />
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
      <div class="mt-3 grid grid-cols-1 gap-2">
        <Button variant="outline" size="sm" class="border-brand-light text-brand-dark hover:bg-brand-lighter/30"
          @click="navigateTo('/login')">
          Login
        </Button>
      </div>
    </div>
  </header>
</template>
