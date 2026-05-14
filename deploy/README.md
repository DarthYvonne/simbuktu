# Simbuktu prod deploy artifacts

Scripts and unit files staged here for installation on the Hetzner box. Nothing in this directory is read by the app at runtime — they're config you copy into `/etc/...` to enable the scaling work.

## simbuktu-worker@.service

Systemd template unit for the Laravel queue worker. The existing single-worker `simbuktu-worker.service` is fine for current load; this template lets you spawn N workers when the chat-reply queue gets busy.

### Install

```bash
sudo cp simbuktu-worker@.service /etc/systemd/system/
sudo systemctl daemon-reload

# Start 4 workers (rule of thumb: 1 worker per ~6 chat replies/sec needed)
sudo systemctl enable --now simbuktu-worker@1 simbuktu-worker@2 simbuktu-worker@3 simbuktu-worker@4

# Verify
systemctl list-units 'simbuktu-worker@*' --type=service
```

The single `simbuktu-worker.service` and the `@N` instances all watch the same `default` queue, so adding workers just adds parallelism.

### Tuning

- One worker handles ~6 chat replies/min sustained (10s avg per LLM call). Multiply for desired throughput.
- Workers self-restart every 1h (`--max-time=3600`) or after 1000 jobs (`--max-jobs=1000`) — prevents memory leaks.
- If you switch `QUEUE_CONNECTION=redis`, also restart the workers: `sudo systemctl restart simbuktu-worker simbuktu-worker@*`.

### Disable

```bash
sudo systemctl disable --now simbuktu-worker@1 simbuktu-worker@2 simbuktu-worker@3 simbuktu-worker@4
```

## simbuktu-cron

Adds the missing `php artisan schedule:run` cron entry. Without it the schedules defined in `routes/console.php` (the per-minute simulation tick, news fetcher, news digester, daily cover refresh) do not run automatically.

### Install

```bash
sudo cp simbuktu-cron /etc/cron.d/simbuktu
sudo chown root:root /etc/cron.d/simbuktu
sudo chmod 644 /etc/cron.d/simbuktu
```

`cron` reloads `/etc/cron.d/` automatically — no service restart needed. Verify:

```bash
sudo run-parts --test /etc/cron.d/   # lists files cron sees
sudo journalctl -u cron --since '2 min ago' | grep simbuktu
```

### What this enables

| schedule | what it does |
|---|---|
| `* * * * *` simulation:tick | one round per active post per minute |
| every 30 min | FetchNewsJob — pulls fresh news headlines |
| every 6 h | DigestNewsJob — LLM-summarises news for personas |
| daily 03:15 | covers:refresh — regenerates persona profile covers |

## Switching cache + sessions to Redis

After php-fpm has been reloaded so the `php-redis` extension is loaded:

1. Edit `/var/www/simbuktu/.env`:
   ```
   CACHE_STORE=redis
   SESSION_DRIVER=redis
   QUEUE_CONNECTION=redis     # ← only flip this if you also restart all workers
   ```
2. Clear cached config: `php artisan config:clear && php artisan cache:clear`
3. Restart php-fpm: `sudo systemctl reload php8.3-fpm php8.4-fpm`
4. If you flipped `QUEUE_CONNECTION`, restart workers too: `sudo systemctl restart 'simbuktu-worker*'`

Sessions migrating from `database` to `redis` will log everyone out once. Acceptable for the gain (every page load drops 1-2 DB writes).

## Health checks after deploy

```bash
# Worker(s) processing the queue
ps aux | grep 'queue:work' | grep -v grep

# Failed jobs?
cd /var/www/simbuktu && php artisan queue:failed

# Schedule running?
sudo journalctl -u cron --since '2 min ago' | grep simbuktu
```
