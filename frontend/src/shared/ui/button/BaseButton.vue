<script setup lang="ts">
import { cn } from "@/shared/lib/utils";
import { cva } from "class-variance-authority";

const buttonVariants = cva(
  "inline-flex items-center justify-center rounded-md text-sm font-medium transition-all focus:outline-none focus:ring-2 focus:ring-black disabled:pointer-events-none disabled:opacity-50",
  {
    variants: {
      variant: {
        default: "bg-black text-white hover:bg-gray-800",
        outline: "border border-gray-300 hover:bg-gray-100",
        ghost: "hover:bg-gray-100",
      },
      size: {
        sm: "h-8 px-3",
        md: "h-10 px-4",
        lg: "h-12 px-6",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "md",
    },
  }
);

type ButtonVariant = "default" | "outline" | "ghost";
type ButtonSize = "sm" | "md" | "lg";

const props = withDefaults(
  defineProps<{
    class?: string;
    variant?: ButtonVariant;
    size?: ButtonSize;
    type?: "button" | "submit" | "reset";
    disabled?: boolean;
    loading?: boolean;
  }>(),
  {
    variant: "default",
    size: "md",
    type: "button",
    disabled: false,
    loading: false,
  }
);
</script>

<template>
  <button :type="props.type" :disabled="props.disabled || props.loading" :class="cn(
    buttonVariants({
      variant: props.variant,
      size: props.size,
    }),
    'w-full',
    props.class
  )
    ">
    <span v-if="props.loading" class="mr-2 animate-spin">⏳</span>

    <slot>
      {{ props.loading ? "Loading..." : "Submit" }}
    </slot>
  </button>
</template>