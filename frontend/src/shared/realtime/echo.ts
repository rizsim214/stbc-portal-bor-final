import Echo from "laravel-echo";
import Pusher from "pusher-js";
import { http } from "@/shared/api/http";

type EchoInstance = Echo<"reverb">;

declare global {
  interface Window {
    Echo?: EchoInstance;
    Pusher?: typeof Pusher;
  }
}

function getRequiredEnv(name: string): string | null {
  const value = import.meta.env[name as keyof ImportMetaEnv];

  return typeof value === "string" && value.length > 0 ? value : null;
}

export function initializeEcho(): EchoInstance | null {
  if (typeof globalThis.window === "undefined") {
    return null;
  }

  if (globalThis.window.Echo) {
    return globalThis.window.Echo;
  }

  const appKey = getRequiredEnv("VITE_REVERB_APP_KEY");
  const wsHost = getRequiredEnv("VITE_REVERB_HOST");

  if (!appKey || !wsHost) {
    return null;
  }

  const wsPort = Number(import.meta.env.VITE_REVERB_PORT ?? 8080);
  const scheme = import.meta.env.VITE_REVERB_SCHEME ?? "http";

  globalThis.window.Pusher = Pusher;
  globalThis.window.Echo = new Echo({
    broadcaster: "reverb",
    key: appKey,
    wsHost,
    wsPort,
    wssPort: wsPort,
    forceTLS: scheme === "https",
    enabledTransports: ["ws", "wss"],
    authEndpoint: "/api/broadcasting/auth",
    auth: {
      headers: {},
    },
    authorizer: (channel) => ({
      authorize: async (socketId, callback) => {
        try {
          const { data } = await http.post("/broadcasting/auth", {
            socket_id: socketId,
            channel_name: channel.name,
          });

          callback(null, data);
        } catch (error) {
          callback(
            error instanceof Error ? error : new Error("Broadcast auth failed."),
            null,
          );
        }
      },
    }),
  });

  return globalThis.window.Echo;
}

export function getEcho(): EchoInstance | null {
  return initializeEcho();
}

export function disconnectEcho(): void {
  if (typeof globalThis.window === "undefined") {
    return;
  }

  globalThis.window.Echo?.disconnect();
  globalThis.window.Echo = undefined;
  globalThis.window.Pusher = undefined;
}
