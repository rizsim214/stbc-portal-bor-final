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
  PopoverContent,
  PopoverPortal,
  PopoverRoot,
  PopoverTrigger,
  ScrollAreaCorner,
  ScrollAreaRoot,
  ScrollAreaScrollbar,
  ScrollAreaThumb,
  ScrollAreaViewport,
} from "radix-vue";
import { Bell, CalendarDays, CircleUser, Menu, X } from "lucide-vue-next";
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import { usePatientAppointmentStatusNotifications } from "@/features/appointment/composables/usePatientAppointmentStatusNotifications";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import stbcLogo from "@/assets/resources/stbc-logo.jpg";
import { useAppointmentStatusNotificationStore } from "@/shared/stores/useAppointmentStatusNotificationStore";
import { Button } from "@/shared/ui/button";

const router = useRouter();
const authStore = useAuthStore();
const appointmentStatusNotificationStore = useAppointmentStatusNotificationStore();
const isMobileMenuOpen = ref(false);
const isDesktopNotificationMenuOpen = ref(false);
const isMobileNotificationMenuOpen = ref(false);

usePatientAppointmentStatusNotifications();

const headerClass = "border-brand-light/30 bg-white/95 backdrop-blur";
const linkClass = "text-brand-dark hover:bg-brand-lighter/30 hover:text-brand-darker";
const appointmentCtaClass =
  "inline-flex items-center justify-center gap-2 rounded-full bg-brand-dark px-4 py-2 text-sm font-semibold text-white shadow-[0_10px_24px_-16px_rgba(21,5,120,0.8)] transition hover:bg-brand-darker focus:outline-none focus:ring-2 focus:ring-brand-highlight/50";
const appointmentRedirectPath = "/appointments";
const avatarClass = "bg-brand-highlight text-white hover:bg-brand-dark";

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

const navItems = [
  {
    id: "home",
    title: "Home",
    link: "/",
  },
  {
    id: "about",
    title: "About",
    link: "/about",
  },
  {
    id: "services",
    title: "Services",
    link: "/services",
  },
] as const;

const homeLink = computed(() =>
  authStore.isAuthenticated ? authStore.getDashboardPath() : "/",
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

const patientAppointmentNotifications = computed(
  () => appointmentStatusNotificationStore.items,
);

const unreadAppointmentNotificationCount = computed(
  () => appointmentStatusNotificationStore.unreadCount,
);

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

function formatNotificationTimestamp(value: string): string {
  const parsed = new Date(value);

  if (Number.isNaN(parsed.getTime())) {
    return "Just now";
  }

  return new Intl.DateTimeFormat("en-US", {
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
  }).format(parsed);
}

function handleDesktopNotificationMenuOpenChange(open: boolean): void {
  isDesktopNotificationMenuOpen.value = open;

  if (open) {
    isMobileNotificationMenuOpen.value = false;
    appointmentStatusNotificationStore.markAllRead();
  }
}

function handleMobileNotificationMenuOpenChange(open: boolean): void {
  isMobileNotificationMenuOpen.value = open;

  if (open) {
    isDesktopNotificationMenuOpen.value = false;
    appointmentStatusNotificationStore.markAllRead();
  }
}

function openAppointmentFromNotification(appointmentId: number): void {
  router.push({
    name: "MyAppointmentView",
    params: { appointmentId: String(appointmentId) },
  });
  isDesktopNotificationMenuOpen.value = false;
  isMobileNotificationMenuOpen.value = false;
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
          <div class="flex items-center gap-2">
            <span class="max-w-40 truncate text-xs font-semibold text-brand-darker">{{ userDisplayName }}</span>

            <PopoverRoot
              :open="isDesktopNotificationMenuOpen"
              @update:open="handleDesktopNotificationMenuOpenChange"
            >
              <PopoverTrigger as-child>
                <button type="button"
                  class="relative inline-flex h-9 w-9 items-center justify-center rounded-full bg-white text-brand-darker transition hover:bg-brand-lighter/25"
                  aria-label="Open notifications">
                  <Bell class="h-4.5 w-4.5" />
                  <span v-if="unreadAppointmentNotificationCount > 0"
                    class="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-brand-dark px-1 text-[10px] font-semibold text-white">
                    {{ unreadAppointmentNotificationCount > 9 ? "9+" : unreadAppointmentNotificationCount }}
                  </span>
                </button>
              </PopoverTrigger>
              <PopoverPortal>
                <PopoverContent
                  class="z-200 mt-2 w-88 rounded-md border border-brand-light/30 bg-white p-1 shadow-lg outline-none"
                  align="end" :side-offset="8">
                  <div class="flex items-center justify-between px-3 py-2">
                    <div>
                      <p class="text-sm font-semibold text-brand-darker">Notifications</p>
                      <p class="text-xs text-brand-dark/70">Appointment workflow updates</p>
                    </div>
                  </div>
                  <div class="my-1 h-px bg-brand-light/40" />

                  <div v-if="patientAppointmentNotifications.length === 0" class="px-3 py-4 text-sm text-brand-dark/70">
                    No appointment notifications yet.
                  </div>

                  <ScrollAreaRoot v-else class="max-h-96 overflow-hidden">
                    <ScrollAreaViewport class="max-h-96">
                      <div class="space-y-1 pr-2">
                        <button v-for="notification in patientAppointmentNotifications" :key="notification.id"
                          type="button"
                          class="w-full cursor-pointer rounded px-3 py-3 text-left outline-none transition hover:bg-brand-lighter/20 focus:bg-brand-lighter/30"
                          @click="openAppointmentFromNotification(notification.appointmentId)">
                          <div class="w-full">
                            <div class="flex items-start justify-between gap-3">
                              <p class="text-sm font-medium text-brand-darker">
                                {{ notification.message }}
                              </p>
                              <span v-if="!notification.isRead"
                                class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-brand-highlight" />
                            </div>
                            <p class="mt-2 text-xs text-brand-dark/65">
                              {{ formatNotificationTimestamp(notification.occurredAt) }}
                            </p>
                          </div>
                        </button>
                      </div>
                    </ScrollAreaViewport>
                    <ScrollAreaScrollbar
                      class="flex w-2.5 touch-none select-none bg-transparent p-0.5 transition-colors"
                      orientation="vertical">
                      <ScrollAreaThumb class="relative flex-1 rounded-full bg-brand-light/40" />
                    </ScrollAreaScrollbar>
                    <ScrollAreaCorner class="bg-transparent" />
                  </ScrollAreaRoot>
                </PopoverContent>
              </PopoverPortal>
            </PopoverRoot>

            <DropdownMenuRoot>
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
              <DropdownMenuContent
                class="z-200 mt-2 w-44 rounded-md border border-brand-light/30 bg-white p-1 shadow-lg outline-none"
                align="end" :side-offset="8">
                <template v-for="item in userMenuItems" :key="item.id">
                  <DropdownMenuSeparator v-if="item.id === 'logout'" class="my-1 h-px bg-brand-light/40" />
                  <DropdownMenuItem class="w-full cursor-pointer rounded px-3 py-2 text-left text-sm outline-none"
                    :class="item.danger
                      ? 'text-red-600 focus:bg-red-50'
                      : 'text-brand-dark focus:bg-brand-lighter/30'" @select="onUserMenuAction(item.id)">
                    {{ item.label }}
                  </DropdownMenuItem>
                </template>
              </DropdownMenuContent>
            </DropdownMenuRoot>
          </div>
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
        <div class="flex items-center gap-2">
          <span class="max-w-28 truncate text-xs font-semibold text-brand-darker">{{ userDisplayName }}</span>

          <PopoverRoot
            :open="isMobileNotificationMenuOpen"
            @update:open="handleMobileNotificationMenuOpenChange"
          >
            <PopoverTrigger as-child>
              <button type="button"
                class="relative inline-flex h-9 w-9 items-center justify-center rounded-full border border-brand-light/30 bg-white text-brand-darker transition hover:bg-brand-lighter/25"
                aria-label="Open notifications">
                <Bell class="h-4.5 w-4.5" />
                <span v-if="unreadAppointmentNotificationCount > 0"
                  class="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-brand-dark px-1 text-[10px] font-semibold text-white">
                  {{ unreadAppointmentNotificationCount > 9 ? "9+" : unreadAppointmentNotificationCount }}
                </span>
              </button>
            </PopoverTrigger>
            <PopoverPortal>
              <PopoverContent
                class="z-200 mt-2 w-80 rounded-md border border-brand-light/30 bg-white p-1 shadow-lg outline-none"
                align="end" :side-offset="8">
                <div class="flex items-center justify-between px-3 py-2">
                  <div>
                    <p class="text-sm font-semibold text-brand-darker">Notifications</p>
                    <p class="text-xs text-brand-dark/70">Appointment workflow updates</p>
                  </div>
                </div>
                <div class="my-1 h-px bg-brand-light/40" />

                <div v-if="patientAppointmentNotifications.length === 0" class="px-3 py-4 text-sm text-brand-dark/70">
                  No appointment notifications yet.
                </div>

                <ScrollAreaRoot v-else class="max-h-80 overflow-hidden">
                  <ScrollAreaViewport class="max-h-80">
                    <div class="space-y-1 pr-2">
                      <button v-for="notification in patientAppointmentNotifications" :key="notification.id"
                        type="button"
                        class="w-full cursor-pointer rounded px-3 py-3 text-left outline-none transition hover:bg-brand-lighter/20 focus:bg-brand-lighter/30"
                        @click="openAppointmentFromNotification(notification.appointmentId)">
                        <div class="w-full">
                          <div class="flex items-start justify-between gap-3">
                            <p class="text-sm font-medium text-brand-darker">
                              {{ notification.message }}
                            </p>
                            <span v-if="!notification.isRead"
                              class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-brand-highlight" />
                          </div>
                          <p class="mt-2 text-xs text-brand-dark/65">
                            {{ formatNotificationTimestamp(notification.occurredAt) }}
                          </p>
                        </div>
                      </button>
                    </div>
                  </ScrollAreaViewport>
                  <ScrollAreaScrollbar
                    class="flex w-2.5 touch-none select-none bg-transparent p-0.5 transition-colors"
                    orientation="vertical">
                    <ScrollAreaThumb class="relative flex-1 rounded-full bg-brand-light/40" />
                  </ScrollAreaScrollbar>
                  <ScrollAreaCorner class="bg-transparent" />
                </ScrollAreaRoot>
              </PopoverContent>
            </PopoverPortal>
          </PopoverRoot>

          <DropdownMenuRoot>
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
      </div>

      <button v-if="!authStore.isAuthenticated" type="button"
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
