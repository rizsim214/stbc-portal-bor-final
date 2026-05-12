import { createRouter, createWebHistory } from "vue-router";
import HomePage from "@/pages/HomePage.vue";
import AppointmentPage from "@/pages/AppointmentPage.vue";
import ServicesPage from "@/pages/ServicesPage.vue";
import AboutPage from "@/pages/AboutPage.vue";
import LoginPage from "@/features/auth/pages/LoginPage.vue";
import RegisterPage from "@/features/auth/pages/RegisterPage.vue";
import ForgotPassword from "@/features/auth/pages/ForgotPassword.vue";

const routes = [
  {
    path: "/",
    name: "home",
    component: HomePage,
  },
  {
    path: "/about",
    name: "about",
    component: AboutPage,
  },
  {
    path: "/services",
    name: "services",
    component: ServicesPage,
  },
  {
    path: "/appointments",
    name: "appointments",
    component: AppointmentPage,
  },
  {
    path: "/login",
    name: "login",
    component: LoginPage,
  },
  {
    path: "/register",
    name: "register",
    component: RegisterPage,
  },
  {
    path: "/forgot-password",
    name: "forgotPassword",
    component: ForgotPassword,
  },
];

export const router = createRouter({
  history: createWebHistory(),
  routes,
});
