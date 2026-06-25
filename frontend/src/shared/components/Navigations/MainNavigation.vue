<script setup lang="ts">
import {
  AvatarFallback,
  AvatarImage,
  AvatarRoot,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuRoot,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
  NavigationMenuRoot,
  NavigationMenuList,
  NavigationMenuItem,
  NavigationMenuLink,
} from "radix-vue";
import { CalendarDays, Menu, CircleUser, X } from "lucide-vue-next";
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import { Button } from "@/shared/ui/button";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import stbcLogo from "@/assets/resources/stbc-logo.jpg";

const router = useRouter();
const authStore = useAuthStore();
const isMobileMenuOpen = ref(false);

const headerClass = "border-brand-light/30 bg-white/95 backdrop-blur";
const linkClass = "text-brand-dark hover:bg-brand-lighter/30 hover:text-brand-darker";
const appointmentCtaClass =
  "inline-flex items-center justify-center gap-2 rounded-full bg-brand-dark px-4 py-2 text-sm font-semibold text-white shadow-[0_10px_24px_-16px_rgba(21,5,120,0.8)] transition hover:bg-brand-darker focus:outline-none focus:ring-2 focus:ring-brand-highlight/50";
const appointmentRedirectPath = "/appointments";


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

const userAvatarUrl = computed(() => {
  const user = authStore.user as Record<string, unknown> | null;
  const avatarValue =
    user?.avatar_url ??
    user?.avatarUrl ??
    user?.profile_photo_url ??
    user?.profilePhotoUrl;
  return typeof avatarValue === "string" ? avatarValue : "";
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
  await authStore.logout();
  router.push("/login");
  isMobileMenuOpen.value = false;
}

function navigateIfRouteExists(path: string, missingMessage: string) {
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
    router.push({ name: "accountProfile" });
    return;
  }

  navigateIfRouteExists("/settings", "Settings page is not available yet.");
}

</script>

<template>
  <header :class="headerClass" class="relative z-300 border-b transition-colors duration-300">
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
          <DropdownMenuRoot>
            <div class="flex items-center gap-2">
              <span class="max-w-40 truncate text-xs font-semibold text-brand-darker">{{ userDisplayName }}</span>
              <DropdownMenuTrigger as-child>
                <button type="button" class="flex items-center justify-center text-sm font-semibold transition"
                  aria-label="Open user menu">
                  <AvatarRoot class="h-9 w-9 overflow-hidden rounded-full" :class="avatarClass">
                    <AvatarImage class="h-full w-full object-cover" :src="userAvatarUrl" :alt="userDisplayName" />
                    <AvatarFallback class="flex h-full w-full items-center justify-center">
                      {{ userInitials }}
                    </AvatarFallback>
                  </AvatarRoot>
                </button>
              </DropdownMenuTrigger>
            </div>
            <DropdownMenuContent
              class="z-200 mt-2 w-44 rounded-md border border-brand-light/30 bg-white p-1 shadow-lg outline-none"
              align="end" :side-offset="8">
              <template v-for="item in userMenuItems" :key="item.id">
                <DropdownMenuSeparator v-if="item.id === 'logout'" class="my-1 h-px bg-brand-light/40" />
                <DropdownMenuItem class="w-full cursor-pointer rounded px-3 py-2 text-left text-sm outline-none" :class="item.danger
                  ? 'text-red-600 focus:bg-red-50'
                  : 'text-brand-dark focus:bg-brand-lighter/30'" @select="onUserMenuAction(item.id)">
                  {{ item.label }}
                </DropdownMenuItem>
              </template>
            </DropdownMenuContent>
          </DropdownMenuRoot>
        </template>
        <template v-else>
          <Button size="sm" class="h-10 px-4" :class="appointmentCtaClass" @click="navigateTo(appointmentRedirectPath)">
            <CalendarDays class="h-4 w-4 shrink-0" aria-hidden="true" />
            <span>Set Appointment</span>
          </Button>
          <RouterLink :to="{ path: '/login' }"
            class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm font-medium text-brand-dark transition hover:bg-brand-lighter/30 hover:text-brand-darker">
            <span class="inline-flex items-center gap-2 leading-none">
              <CircleUser class="h-6 w-6 shrink-0" aria-hidden="true" />
              <span>Login</span>
            </span>
          </RouterLink>
        </template>
      </div>

      <div v-if="authStore.isAuthenticated" class="md:hidden">
        <DropdownMenuRoot>
          <div class="flex items-center gap-2">
            <span class="max-w-28 truncate text-xs font-semibold text-brand-darker">{{ userDisplayName }}</span>
            <DropdownMenuTrigger as-child>
              <button type="button" class="flex items-center justify-center text-sm font-semibold transition"
                aria-label="Open user menu">
                <AvatarRoot class="h-9 w-9 overflow-hidden rounded-full" :class="avatarClass">
                  <AvatarImage class="h-full w-full object-cover" :src="userAvatarUrl" :alt="userDisplayName" />
                  <AvatarFallback class="flex h-full w-full items-center justify-center">
                    {{ userInitials }}
                  </AvatarFallback>
                </AvatarRoot>
              </button>
            </DropdownMenuTrigger>
          </div>
          <DropdownMenuContent
            class="z-200 mt-2 w-44 rounded-md border border-brand-light/30 bg-white p-1 shadow-lg outline-none"
            align="end" :side-offset="8">
            <template v-for="item in userMenuItems" :key="item.id">
              <DropdownMenuSeparator v-if="item.id === 'logout'" class="my-1 h-px bg-brand-light/40" />
              <DropdownMenuItem class="w-full cursor-pointer rounded px-3 py-2 text-left text-sm outline-none" :class="item.danger
                ? 'text-red-600 focus:bg-red-50'
                : 'text-brand-dark focus:bg-brand-lighter/30'" @select="onUserMenuAction(item.id)">
                {{ item.label }}
              </DropdownMenuItem>
            </template>
          </DropdownMenuContent>
        </DropdownMenuRoot>
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
        <Button size="sm" class="h-11" :class="appointmentCtaClass" @click="navigateTo(appointmentRedirectPath)">
          <span class="inline-flex items-center gap-2 leading-none">
            <CalendarDays class="h-4 w-4 shrink-0" aria-hidden="true" />
            <span>Set Appointment</span>
          </span>
        </Button>
        <Button variant="outline" size="sm" class="border-brand-light text-brand-dark hover:bg-brand-lighter/30"
          @click="navigateTo('/login')">
          <span class="inline-flex items-center gap-2 leading-none">
            <CircleUser class="h-4 w-4 shrink-0" aria-hidden="true" />
            <span>Login</span>
          </span>
        </Button>
      </div>
    </div>
  </header>
</template>
