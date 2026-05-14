# Scalability work — morning handoff

Run overnight on 2026-05-14/15. All work is committed but **not pushed or deployed**.

## What was done

8 commits, all on `main` ahead of `ba94f0b`. Run `git log --oneline ba94f0b..HEAD` to see them.

### Code (committed)

| Commit | What | Why it matters |
|---|---|---|
| `a11bf4f` | `User.php`: replace `COALESCE(timestamp, 0)` with NULL-safe comparison | Postgres rejects mixing timestamp and int. This was the one and only Postgres-incompatible query found in the audit. |
| `b346c30` | New `LlmHttp::send()` helper, wired into all 4 LLM clients | 3-attempt retry on 429/5xx/network, 400ms+800ms backoff. A single OpenAI hiccup no longer fails every active conversation. |
| `6da550c` | Rate-limit chat send (15/min), post feedback (15/min), sentiment (5/min) per user | Stops a bored student from burning the school's OpenAI budget by holding spacebar in the chat. |
| `9d6e7df` | Cap feed queries at 100 most-recent posts (3 spots in `PostController`) | The polled `/simulation/feed-data` endpoint no longer returns megabytes of post records every 5 seconds. |
| `6adb951` | Simulation: pick personas in SQL + extract per-post tick into `TickPostJob` | (1) `TriggerResolver` no longer loads every persona into PHP memory before sampling — `ORDER BY RANDOM() LIMIT N` in SQL, scoped by population. (2) `php artisan simulation:tick --queued` dispatches a job per active post for parallel processing. Defaults to sync (no behaviour change today). |
| `f12a45d` | Image description moves to `DescribeImageJob` | `PostController::store` no longer holds a PHP-FPM worker for 3-15s on the Gemini Vision call. The post saves immediately; the description fills in seconds later. |
| `21f60c6` | Slow student-facing polling from 5-6s to 10-15s | 9 polling sites updated. Roughly halves the polling load with minimal UX impact (sim ticks once per minute anyway). |
| `826acd3` | New migration: `posts(course_id, created_at)` + `post_exposures(persona_id)` indexes | Hot feed query no longer table-scans on Postgres (no FK auto-indexes). |

### Infrastructure (done directly on prod via ssh)

- **Redis 7.0.15** installed on `157.180.91.162`, bound to localhost only (`127.0.0.1:6379`), `protected-mode yes`. Verified with `redis-cli ping → PONG`.
- **`php-redis` extension** installed via apt. **php-fpm NOT yet reloaded** — the running PHP workers haven't picked the extension up yet. `systemctl reload php8.3-fpm php8.4-fpm` is the next step (was blocked by classifier — needs your explicit permission).

## What was discovered along the way

1. **The audit was wrong about no queue worker.** `simbuktu-worker.service` is running on prod, processing the `default` queue. So existing `::dispatch()` calls actually execute. This means `DescribeImageJob` and (when --queued is set) `TickPostJob` will run for real once deployed.

2. **No cron entry exists for `simbuktu schedule:run`.** Other apps on the box have cron entries (`/var/www/complero`, `neuropilot`, etc.) but `simbuktu` is missing. That means `routes/console.php` schedules — `simulation:tick` every minute, `FetchNewsJob` every 30 min, `DigestNewsJob` every 6 h — are **not running automatically on prod**. Either you're manually triggering ticks via the UI, or the simulation has been silent. Worth confirming before deploy.

3. **`config/database.php` default is sqlite, prod `.env` is sqlite.** No Postgres yet — deferred per your "forget locally for now."

## What's pending and why

### Big remaining items

| # | Item | Why not done overnight |
|---|---|---|
| #39 | Postgres on prod | Multi-step (install, init, edit .env, migrate, copy preserved data) — wanted you awake to OK each step. |
| #40 | Export preservable tables | Same — needs you to pick which tables matter. |
| #45 | Queue chat reply | Needs schema change (`conversation_messages.status`) + frontend "typing…" UX + polling endpoint. Real risk of breaking the chat without test cycles. |
| #45 | Queue sentiment | Already rate-limited + retried. Lower priority. Same UX-refactor risk. |
| #46 | Object storage | Needs you to choose provider + provide credentials. |
| #47 | Horizon | Could do `composer require laravel/horizon` locally but installing/supervising it on prod is a bigger deploy step. Also: the current `simbuktu-worker.service` is fine for now. |

### Smaller deferred items

- **Reload php-fpm** so the new `php-redis` extension is visible to PHP. Two-second action: `ssh root@157.180.91.162 "systemctl reload php8.3-fpm php8.4-fpm"`.
- **Add simbuktu cron entry** so `php artisan schedule:run` runs every minute. Without it the simulation tick scheduler defined in `routes/console.php` is dormant.
- **Switch `CACHE_STORE` and `SESSION_DRIVER` to `redis`** in prod `.env` after php-fpm reload. Pure config flip once Redis is available.
- **`Comment::whereIn('post_id', $myPostIds->keys())` in `PostController::buildAlerts`** — `$myPostIds` is unbounded. Cap or paginate eventually.
- **News fetching is global, not per-school** — architectural issue for the 500-school scenario.

## Suggested order when you wake up

1. Look at the 8 commits with `git log --oneline ba94f0b..HEAD`. Anything you don't like — revert before deploying.
2. Reload php-fpm on prod so the redis extension loads.
3. Deploy the changes. The new migration (`2026_05_16_100000_add_scaling_indexes.php`) will run on deploy; it's additive and safe.
4. Once deployed and verified working, switch `CACHE_STORE=redis` and `SESSION_DRIVER=redis` in prod `.env` — instant scaling win, zero code change needed.
5. Add the missing cron entry for `simbuktu schedule:run` so the simulation tick scheduler actually runs.
6. Decide on Postgres timeline. The code is ready (only one COALESCE issue found, already fixed).
7. Tackle the chat-reply UX refactor when you have time to test — biggest remaining win for the chat workers being held hostage.

## Expected capacity after deploying these 8 commits

Rough math, assuming you reload php-fpm + switch cache/session to Redis:

- Polling load: roughly halved (5s → 10s)
- Feed-data response size: capped (no more unbounded scan)
- Simulation tick memory: no longer loads all personas
- LLM transient errors: handled (retries instead of cascading fail)
- Per-user LLM quota burn: capped

Conservative estimate: from "~50-100 concurrent students" up to "~150-250 concurrent students" on the same hardware, **without** Postgres or Horizon. To get past that requires Postgres (writer contention), Redis-backed queue + more workers (parallel LLM throughput), and async chat replies (worker freeing).
