import http from "k6/http";
import { check, sleep } from "k6";

const BASE_URL = __ENV.BASE_URL || "http://localhost:8000";
const ADMIN_EMAIL = __ENV.ADMIN_EMAIL || "admin@stbc.local";
const ADMIN_PASSWORD = __ENV.ADMIN_PASSWORD;

const APPOINTMENT_TYPE_ID = Number(__ENV.APPOINTMENT_TYPE_ID || "1");
const RESOURCE_IDS = (__ENV.RESOURCE_IDS || "1")
  .split(",")
  .map((id) => Number(id.trim()))
  .filter((id) => Number.isFinite(id) && id > 0);

const SCENARIO = __ENV.SCENARIO || "mixed";
const CONTENTION_RESOURCE_ID = Number(
  __ENV.CONTENTION_RESOURCE_ID || RESOURCE_IDS[0] || 1,
);
const CONTENTION_START_TIME =
  __ENV.CONTENTION_START_TIME || "2026-12-01 09:00:00";
const CONTENTION_END_TIME = __ENV.CONTENTION_END_TIME || "2026-12-01 09:30:00";
const AVAILABILITY_DATE = __ENV.AVAILABILITY_DATE || "2026-12-01";

export const options = {
  scenarios:
    SCENARIO === "contention"
      ? {
          booking_contention: {
            executor: "ramping-arrival-rate",
            startRate: Number(__ENV.START_RATE || 20),
            timeUnit: "1s",
            preAllocatedVUs: Number(__ENV.PRE_ALLOCATED_VUS || 50),
            maxVUs: Number(__ENV.MAX_VUS || 500),
            stages: [
              {
                target: Number(__ENV.STAGE1_RATE || 80),
                duration: __ENV.STAGE1_DURATION || "2m",
              },
              {
                target: Number(__ENV.STAGE2_RATE || 150),
                duration: __ENV.STAGE2_DURATION || "3m",
              },
              {
                target: Number(__ENV.STAGE3_RATE || 200),
                duration: __ENV.STAGE3_DURATION || "3m",
              },
              { target: 0, duration: __ENV.STAGE4_DURATION || "1m" },
            ],
            exec: "contentionFlow",
          },
        }
      : {
          mixed_api_traffic: {
            executor: "ramping-arrival-rate",
            startRate: Number(__ENV.START_RATE || 30),
            timeUnit: "1s",
            preAllocatedVUs: Number(__ENV.PRE_ALLOCATED_VUS || 60),
            maxVUs: Number(__ENV.MAX_VUS || 600),
            stages: [
              {
                target: Number(__ENV.STAGE1_RATE || 100),
                duration: __ENV.STAGE1_DURATION || "3m",
              },
              {
                target: Number(__ENV.STAGE2_RATE || 250),
                duration: __ENV.STAGE2_DURATION || "4m",
              },
              {
                target: Number(__ENV.STAGE3_RATE || 400),
                duration: __ENV.STAGE3_DURATION || "4m",
              },
              { target: 0, duration: __ENV.STAGE4_DURATION || "2m" },
            ],
            exec: "mixedFlow",
          },
        },
  thresholds: {
    http_req_failed: ["rate<0.03"],
    http_req_duration: ["p(95)<800", "p(99)<1500"],
    "checks{kind:login}": ["rate>0.99"],
    "checks{kind:availability}": ["rate>0.97"],
    "checks{kind:appointment}": ["rate>0.90"],
  },
};

function login() {
  const payload = JSON.stringify({
    email: ADMIN_EMAIL,
    password: ADMIN_PASSWORD,
    device_name: `k6-vu-${__VU}`,
  });

  const res = http.post(`${BASE_URL}/api/auth/login`, payload, {
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    tags: { endpoint: "auth_login" },
  });

  const ok = check(
    res,
    {
      "login succeeded": (r) => r.status === 200,
      "login has token": (r) => Boolean(r.json("data.token")),
    },
    { kind: "login" },
  );

  if (!ok) {
    return null;
  }

  return res.json("data.token");
}

function authHeaders(token) {
  return {
    headers: {
      Authorization: `Bearer ${token}`,
      "Content-Type": "application/json",
      Accept: "application/json",
    },
  };
}

export function setup() {
  if (RESOURCE_IDS.length === 0) {
    throw new Error("RESOURCE_IDS cannot be empty.");
  }
}

export function mixedFlow() {
  const token = login();

  if (!token) {
    sleep(1);
    return;
  }

  const path = Math.random();

  if (path < 0.6) {
    const resourceQuery = RESOURCE_IDS.map(
      (id) => "resource_ids[]=" + encodeURIComponent(id),
    ).join("&");
    const url = `${BASE_URL}/api/scheduling/availability?date=${encodeURIComponent(AVAILABILITY_DATE)}&appointment_type_id=${APPOINTMENT_TYPE_ID}&${resourceQuery}`;
    const res = http.get(url, {
      ...authHeaders(token),
      tags: { endpoint: "availability" },
    });

    check(
      res,
      {
        "availability status ok": (r) => r.status === 200,
      },
      { kind: "availability" },
    );
  } else if (path < 0.9) {
    const startAt = new Date(
      Date.now() + (5 + Math.floor(Math.random() * 120)) * 60 * 1000,
    );
    const endAt = new Date(startAt.getTime() + 30 * 60 * 1000);

    const payload = JSON.stringify({
      appointment_type_id: APPOINTMENT_TYPE_ID,
      start_time: formatDateTime(startAt),
      end_time: formatDateTime(endAt),
      resource_ids: [
        RESOURCE_IDS[Math.floor(Math.random() * RESOURCE_IDS.length)],
      ],
    });

    const res = http.post(`${BASE_URL}/api/appointments`, payload, {
      ...authHeaders(token),
      tags: { endpoint: "appointments_store" },
    });

    check(
      res,
      {
        "appointment status expected": (r) =>
          r.status === 201 || r.status === 422,
      },
      { kind: "appointment" },
    );
  } else {
    const res = http.get(`${BASE_URL}/api/health`, {
      headers: { Accept: "application/json" },
      tags: { endpoint: "health" },
    });

    check(res, { "health status ok": (r) => r.status === 200 });
  }

  sleep(Number(__ENV.SLEEP_SECONDS || "0.1"));
}

export function contentionFlow() {
  const token = login();

  if (!token) {
    sleep(1);
    return;
  }

  const payload = JSON.stringify({
    appointment_type_id: APPOINTMENT_TYPE_ID,
    start_time: CONTENTION_START_TIME,
    end_time: CONTENTION_END_TIME,
    resource_ids: [CONTENTION_RESOURCE_ID],
  });

  const res = http.post(`${BASE_URL}/api/appointments`, payload, {
    ...authHeaders(token),
    tags: { endpoint: "appointments_contention" },
  });

  check(
    res,
    {
      "contention status expected": (r) => r.status === 201 || r.status === 422,
    },
    { kind: "appointment" },
  );

  sleep(Number(__ENV.SLEEP_SECONDS || "0.05"));
}

function formatDateTime(date) {
  const y = date.getUTCFullYear();
  const m = String(date.getUTCMonth() + 1).padStart(2, "0");
  const d = String(date.getUTCDate()).padStart(2, "0");
  const hh = String(date.getUTCHours()).padStart(2, "0");
  const mm = String(date.getUTCMinutes()).padStart(2, "0");
  const ss = String(date.getUTCSeconds()).padStart(2, "0");
  return `${y}-${m}-${d} ${hh}:${mm}:${ss}`;
}
