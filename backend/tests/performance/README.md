# Performance Stress Test (k6)

This folder contains a high-volume stress script for the API:
- `k6-stress.js`

## Prerequisites

1. App stack is running (`docker compose up -d`).
2. DB has seed data (`php artisan migrate --seed`), so at least:
- one user (`admin@stbc.local`)
- appointment type id `1`
- resource id `1`
3. k6 installed locally.

## Scenario 1: Mixed API traffic (recommended baseline)

Runs a realistic distribution:
- 60% scheduling availability reads
- 30% appointment writes
- 10% health checks

```bash
k6 run backend/tests/performance/k6-stress.js
```

Docker alternative (no local k6 install):

```powershell
docker run --rm -i -v ${PWD}:/work -w /work `
  grafana/k6 run `
  -e BASE_URL=http://host.docker.internal:8000 `
  backend/tests/performance/k6-stress.js
```

Example with explicit env:

```bash
k6 run \
  -e BASE_URL=http://localhost:8000 \
  -e ADMIN_EMAIL=admin@stbc.local \
  -e ADMIN_PASSWORD=ChangeMe123! \
  -e APPOINTMENT_TYPE_ID=1 \
  -e RESOURCE_IDS=1,2,3 \
  -e START_RATE=50 \
  -e STAGE1_RATE=150 \
  -e STAGE2_RATE=300 \
  -e STAGE3_RATE=500 \
  backend/tests/performance/k6-stress.js
```

Docker alternative:

```powershell
docker run --rm -i -v ${PWD}:/work -w /work `
  grafana/k6 run `
  -e BASE_URL=http://host.docker.internal:8000 `
  -e ADMIN_EMAIL=admin@stbc.local `
  -e ADMIN_PASSWORD=ChangeMe123! `
  -e APPOINTMENT_TYPE_ID=1 `
  -e RESOURCE_IDS=1,2,3 `
  -e START_RATE=50 `
  -e STAGE1_RATE=150 `
  -e STAGE2_RATE=300 `
  -e STAGE3_RATE=500 `
  backend/tests/performance/k6-stress.js
```

## Scenario 2: Booking contention (race-condition stress)

All VUs attempt the same resource/time slot. Expected behavior is mostly `422` after first successes.

```bash
k6 run \
  -e SCENARIO=contention \
  -e BASE_URL=http://localhost:8000 \
  -e ADMIN_EMAIL=admin@stbc.local \
  -e ADMIN_PASSWORD=ChangeMe123! \
  -e APPOINTMENT_TYPE_ID=1 \
  -e CONTENTION_RESOURCE_ID=1 \
  -e CONTENTION_START_TIME="2026-12-01 09:00:00" \
  -e CONTENTION_END_TIME="2026-12-01 09:30:00" \
  backend/tests/performance/k6-stress.js
```

Docker alternative:

```powershell
docker run --rm -i -v ${PWD}:/work -w /work `
  grafana/k6 run `
  -e SCENARIO=contention `
  -e BASE_URL=http://host.docker.internal:8000 `
  -e ADMIN_EMAIL=admin@stbc.local `
  -e ADMIN_PASSWORD=ChangeMe123! `
  -e APPOINTMENT_TYPE_ID=1 `
  -e CONTENTION_RESOURCE_ID=1 `
  -e CONTENTION_START_TIME="2026-12-01 09:00:00" `
  -e CONTENTION_END_TIME="2026-12-01 09:30:00" `
  backend/tests/performance/k6-stress.js
```

## Key env vars

- `SCENARIO`: `mixed` (default) or `contention`
- `BASE_URL`: API origin
- `ADMIN_EMAIL`, `ADMIN_PASSWORD`: auth credentials
- `APPOINTMENT_TYPE_ID`: valid DB id
- `RESOURCE_IDS`: comma-separated resource ids for mixed mode
- `AVAILABILITY_DATE`: date used in availability calls
- `START_RATE`, `STAGE*_RATE`: arrival rate profile
- `PRE_ALLOCATED_VUS`, `MAX_VUS`: VU pool limits

## Thresholds used

- `http_req_failed < 3%`
- `http_req_duration p95 < 800ms`
- `http_req_duration p99 < 1500ms`
- login checks > 99%
- availability checks > 97%
- appointment checks > 90%

Tune these to your SLOs and hardware.
