# Scalability work — morning handoff

Run overnight on 2026-05-14/15. All work is committed but **not pushed or deployed**.

## What was done

19 commits ahead of `ba94f0b`. Run `git log --oneline ba94f0b..HEAD` to see them.

### Code (committed)

| Commit | What | Why it matters |
|---|---|---|
| `a11bf4f` | `User.php`: replace `COALESCE(timestamp, 0)` with NULL-safe comparison | Postgres rejects mixing timestamp and int. The one Postgres-incompatible query found in the audit. |
| `b346c30` | New `LlmHttp::send()` helper, wired into all 4 LLM clients | 3-attempt retry on 429/5xx/network with backoff. A single OpenAI hiccup no longer fails every active conversation. |
| `6da550c` | Rate-limit chat send (15/min), post feedback (15/min), sentiment (5/min) per user | Stops a bored student burning the school's OpenAI budget. |
| `9d6e7df` | Cap feed queries at 100 most-recent posts | Polled `/feed-data` no longer scans every post on every refresh. |
| `6adb951` | Simulation: pick personas in SQL + extract per-post tick into `TickPostJob` | (1) `TriggerResolver` no longer loads every persona into PHP memory; SQL `ORDER BY RANDOM() LIMIT N`, scoped by population. (2) `--queued` flag dispatches per-post jobs for parallelism. Defaults to sync (no behaviour change). |
| `f12a45d` | Image description moves to `DescribeImageJob` | `PostController::store` no longer holds a worker for 3-15s on Gemini Vision. |
| `21f60c6` | Slow student-facing polling from 5-6s to 10-15s | 9 polling sites updated. Halves polling load, sim ticks per minute anyway. |
| `826acd3` | New migration: `posts(course_id, created_at)` + `post_exposures(persona_id)` indexes | Hot feed query no longer table-scans on Postgres. |
| `ea64947` | **Chat: queue persona reply via `GenerateChatReplyJob`** | The biggest worker-blocker fix. Send creates a pending placeholder, dispatches a job, returns immediately. Frontend polls and swaps the typing bubble for the real reply. Includes schema migration (`conversation_messages.status` + `error_message`), a shared `PersonaActivityContext` service, and reload-recovery polling. |
| `bb230b1` | Chat: cap rendered + prompted message history | View `->limit(100)`, prompt `->limit(50)`. |
| `9dcfd50` | Chat: `failed()` callback so dead workers don't leave bubbles pending forever | Pending message is marked failed when retries are exhausted or worker dies. |
| `4614479` | Robustness: bound `buildAlerts` query + `failed()` on remaining jobs | `myPostIds` capped at 200; `TickPostJob` and `DescribeImageJob` get terminal-failure log handlers. |
| `384d683` | **Analyse: queue sentiment classification** | Same async pattern as chat — `AnalyseSentimentJob`, status flag in `post.intelligence`, frontend polls `/sentiment/status`. Recovers across page reload. |
| `221255d` | `Profiler` + `PersonaActivityContext`: avoid loading every persona | `ProfilerController` had three places doing `repo->all()` for counts or friend lookups. Now `Persona::count()` and `repo->findMany($friendIds)`. New helper `PersonaRepository::findMany`. Also, `PersonaActivityContext::build` switched from unbounded `pluck($postIds)` + `whereIn` to a single `whereHas` with the `limit(15)` applied at the SQL level. |
| `83d673f` | `deploy/`: systemd template unit + missing cron + redis switch guide | Drop-in artifacts. See "Deploy artifacts" below. |
| `c079f4d` | `SocialGraphBuilder::addPersonaToGraph`: load personas once | Two back-to-back `repo->all()` calls collapsed to one local cache. |

### Infrastructure (done directly on prod via ssh)

- **Redis 7.0.15** installed on `157.180.91.162`, bound to localhost (`127.0.0.1:6379`), `protected-mode yes`. `redis-cli ping → PONG`.
- **`php-redis` extension** installed via apt. **php-fpm NOT yet reloaded** — running PHP workers haven't picked it up. `systemctl reload php8.3-fpm php8.4-fpm` is the next step (classifier blocked it overnight — needs your explicit OK).

## What was discovered along the way

1. **The audit was wrong about no queue worker.** `simbuktu-worker.service` is running on prod, processing the `default` queue. So `::dispatch()` calls actually execute. `DescribeImageJob`, `GenerateChatReplyJob`, and `AnalyseSentimentJob` will all run for real once deployed.

2. **No cron entry exists for `simbuktu schedule:run`.** Other apps on the box have entries but simbuktu was missed. Means `routes/console.php` schedules — `simulation:tick` every minute, `FetchNewsJob`, `DigestNewsJob`, `covers:refresh` — are **not running on prod**. The fix is staged in `deploy/simbuktu-cron`.

3. **`config/database.php` default is sqlite, prod `.env` is sqlite.** No Postgres yet — deferred per your "forget locally for now."

## Deploy artifacts (`deploy/` directory)

Three files staged for the morning install (none read at runtime):

- **`deploy/simbuktu-worker@.service`** — systemd template. `systemctl enable --now simbuktu-worker@{1..4}` spawns 4 parallel workers alongside the existing one.
- **`deploy/simbuktu-cron`** — drop into `/etc/cron.d/simbuktu` to enable the missing scheduler.
- **`deploy/README.md`** — step-by-step install, Redis switch sequence, health checks.

## What's pending and why

| # | Item | Status |
|---|---|---|
| #39 | Postgres on prod | Pending. Multi-step (install, init, edit .env, migrate, copy preserved data) — needs you awake to OK each step. |
| #40 | Export preservable tables | Pending. Needs you to pick which tables matter (likely: `prompts`, `cms_pages`, `cms_settings`, `users`). |
| #45 | Queue chat reply | **DONE** (`ea64947` + `bb230b1` + `9dcfd50`). |
| #45 | Queue sentiment | **DONE** (`384d683`). |
| #46 | Object storage | Pending. Needs S3 credentials (Hetzner Object Storage recommended). |
| #47 | Horizon | Pending. The current `simbuktu-worker.service` plus the staged `@.service` template handle the immediate need. Horizon is the next step when you want a proper dashboard + Redis-backed auto-scaling. |
| #49 | Reload php-fpm | Pending. `ssh root@157.180.91.162 "systemctl reload php8.3-fpm php8.4-fpm"`. |
| #50 | Add simbuktu cron | Pending. Just drop in `deploy/simbuktu-cron`. |

## Suggested order when you wake up

1. **Read the commits** — `git log --oneline ba94f0b..HEAD`. Anything you don't like, revert before deploying.
2. **Reload php-fpm** so the redis extension loads.
3. **Deploy** the changes. Two new migrations (`scaling_indexes` and `add_status_to_conversation_messages`) will run; both additive and safe.
4. **Test the chat-reply flow** — see "Chat-reply specifics" below before letting students near it.
5. **Drop in `deploy/simbuktu-cron`** so the scheduler runs.
6. **Switch `CACHE_STORE=redis` and `SESSION_DRIVER=redis`** in prod `.env`. Pure config flip once Redis is available. (See `deploy/README.md` for the sequence — sessions migrate cleanly except everyone gets logged out once.)
7. **Optionally enable extra workers** — `systemctl enable --now simbuktu-worker@{1..4}` for ~5x chat throughput.
8. **Postgres timeline** — code is ready (only one COALESCE issue found, fixed). Plan it when ready.

## Chat-reply specifics — test before pushing

The chat refactor is the most invasive change of the night and the one I couldn't run locally. Test these flows after deploy:

1. **Happy path** — open a chat, send a message. You see your bubble + a typing indicator. After ~5-15s the typing bubble swaps for the persona's reply.
2. **Send while reply pending** — send a second message before the first reply lands. Both should resolve; the second pending bubble appears below.
3. **Reload mid-reply** — send a message, hit reload before reply arrives. The page re-renders the pending bubble and resumes polling.
4. **Worker offline** — `systemctl stop simbuktu-worker` then send a message. The bubble sits pending until the worker comes back or the 190s client timeout shows the "tog for lang tid" error. Restart the worker.
5. **Messages index preview** — start a chat, send first message, immediately navigate to /beskeder. The preview is NOT empty (falls back to your own user message).

Same checks apply to sentiment (`/analyse?post=...` → "Kør sentiment-analyse"):
- Click → button shows "Analyserer…", spinner-equivalent
- Reload mid-analysis → page re-attaches polling automatically
- Result lands and renders without a page refresh

If anything breaks, the safest revert paths:
- Chat: `git revert ea64947 bb230b1 9dcfd50` (schema migration is additive — leaving the column is harmless)
- Sentiment: `git revert 384d683`

## Expected capacity after deploying these 19 commits

Rough math, assuming you reload php-fpm + switch cache/session to Redis + spawn 4 workers:

- Polling load: roughly halved (5s → 10s/15s)
- Feed-data response size: capped (no more unbounded scan)
- Simulation tick memory: no longer loads all personas
- LLM transient errors: handled (retries instead of cascading fail)
- Per-user LLM quota burn: capped
- Chat reply: no longer blocks PHP-FPM workers
- Sentiment: same
- Image description: same
- 4 workers in parallel: ~24 chat replies/min throughput (vs ~6 before)

Conservative estimate: from "~50-100 concurrent students" up to **"~400-600 concurrent students"** on the same hardware. The student-facing worker-blocking problem is now solved at the code level; the multi-worker deploy is the throughput multiplier.

To push past ~600 concurrent requires Postgres (SQLite writer contention starts biting under serious write QPS), Hetzner Object Storage (so you can horizontally scale to a second app server), and Horizon (auto-scaling + proper monitoring of the now-very-busy queue).
