import { createRouter, createWebHistory } from "vue-router";
import { installAuthGuards } from "./guards";
import { guestRoutes } from "./routes/guest.routes";
import { privateRoutes } from "./routes/private.routes";
import { publicRoutes } from "./routes/public.routes";

export const router = createRouter({
  history: createWebHistory(),
  routes: [...publicRoutes, ...guestRoutes, ...privateRoutes],
});

installAuthGuards(router);
