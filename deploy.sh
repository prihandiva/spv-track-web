#!/usr/bin/env bash
#
# Safe-ish Laravel Docker deployment:
# 1. Validate Compose configuration
# 2. Build images
# 3. Start PostgreSQL and Redis
# 4. Wait for their health checks
# 5. Back up PostgreSQL
# 6. Run Laravel migrations once
# 7. Start app, queue, and Nginx
# 8. Validate Nginx and optionally test URLs
#
# Run from the project directory:
#   chmod +x deploy.sh
#   ./deploy.sh
#
# Optional environment variables:
#   ENV_FILE=.env.production
#   COMPOSE_FILE=docker-compose.yml
#   HEALTH_TIMEOUT=120
#   BACKUP_DIR=./backups
#   PUBLIC_URL=https://your-real-domain.example
#   SKIP_DB_BACKUP=1   # only if you have another verified backup

set -Eeuo pipefail
IFS=$'\n\t'

ENV_FILE="${ENV_FILE:-.env.production}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.yml}"
HEALTH_TIMEOUT="${HEALTH_TIMEOUT:-180}"
BACKUP_DIR="${BACKUP_DIR:-./backups}"
PUBLIC_URL="${PUBLIC_URL:-}"
SKIP_DB_BACKUP="${SKIP_DB_BACKUP:-0}"

log() {
  printf '\n[%s] %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*"
}

die() {
  printf '\nERROR: %s\n' "$*" >&2
  exit 1
}

on_error() {
  local exit_code=$?
  printf '\nDeployment stopped at line %s (exit code %s).\n' "${BASH_LINENO[0]:-unknown}" "$exit_code" >&2
  printf 'Inspect logs with:\n  docker compose --env-file "%s" -f "%s" logs --tail=100\n' "$ENV_FILE" "$COMPOSE_FILE" >&2
  exit "$exit_code"
}
trap on_error ERR

command -v docker >/dev/null 2>&1 || die "Docker is not installed or not in PATH."
docker compose version >/dev/null 2>&1 || die "Docker Compose v2 is required."
command -v date >/dev/null 2>&1 || die "date command not found."

[[ -f "$ENV_FILE" ]] || die "Environment file not found: $ENV_FILE"
[[ -f "$COMPOSE_FILE" ]] || die "Compose file not found: $COMPOSE_FILE"

# Use an explicit Compose project file and environment file for every command.
dc() {
  docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" "$@"
}

log "Validating Compose configuration..."
dc config --quiet

log "Building images..."
dc build

log "Starting PostgreSQL and Redis..."
dc up -d postgres redis

wait_for_health() {
  local service="$1"
  local timeout="$2"
  local start now container_id status state

  container_id="$(dc ps -q "$service")"
  [[ -n "$container_id" ]] || die "No container found for service '$service' after startup."

  start="$(date +%s)"
  while true; do
    status="$(docker inspect --format '{{if .State.Health}}{{.State.Health.Status}}{{else}}no-healthcheck{{end}}' "$container_id")"
    state="$(docker inspect --format '{{.State.Status}}' "$container_id")"

    case "$status" in
      healthy)
        log "Service '$service' is healthy."
        return 0
        ;;
      unhealthy)
        dc logs --tail=80 "$service" || true
        die "Service '$service' is unhealthy."
        ;;
      no-healthcheck)
        die "Service '$service' has no Docker health check; cannot safely wait for readiness."
        ;;
    esac

    if [[ "$state" == "exited" || "$state" == "dead" ]]; then
      dc logs --tail=80 "$service" || true
      die "Service '$service' stopped before becoming healthy (state: $state)."
    fi

    now="$(date +%s)"
    if (( now - start >= timeout )); then
      dc logs --tail=80 "$service" || true
      die "Timed out waiting ${timeout}s for '$service' to become healthy."
    fi

    sleep 3
  done
}

log "Waiting for PostgreSQL and Redis health checks (timeout: ${HEALTH_TIMEOUT}s each)..."
wait_for_health postgres "$HEALTH_TIMEOUT"
wait_for_health redis "$HEALTH_TIMEOUT"

if [[ "$SKIP_DB_BACKUP" == "1" ]]; then
  log "Skipping automatic PostgreSQL backup because SKIP_DB_BACKUP=1."
else
  command -v date >/dev/null 2>&1 || die "date command not found."
  mkdir -p "$BACKUP_DIR"
  chmod 700 "$BACKUP_DIR" 2>/dev/null || true
  backup_file="${BACKUP_DIR%/}/postgres-$(date '+%Y%m%d-%H%M%S').sql"
  log "Creating PostgreSQL backup at: $backup_file"
  # Backup contents are sensitive. The file is restricted to the current user.
  umask 077
  dc exec -T postgres sh -c 'pg_dump -U "$POSTGRES_USER" "$POSTGRES_DB"' > "$backup_file"
  [[ -s "$backup_file" ]] || die "PostgreSQL backup is empty: $backup_file"
  chmod 600 "$backup_file" 2>/dev/null || true
  log "PostgreSQL backup completed. Store it securely and verify restorability."
fi

log "Running Laravel migrations once..."
dc run --rm --no-deps app php artisan migrate --force
dc run --rm --no-deps app php artisan db:seed 


log "Starting Laravel app, queue worker, and Nginx..."
dc up -d app queue nginx

log "Waiting for PHP-FPM app health check..."
wait_for_health app "$HEALTH_TIMEOUT"

log "Validating Nginx configuration..."
dc exec -T nginx nginx -t

log "Current container status:"
dc ps

log "Checking local Nginx endpoint..."
if command -v curl >/dev/null 2>&1; then
  if ! curl --fail --silent --show-error --output /dev/null --max-time 15 \
      --write-out 'Local Nginx HTTP status: %{http_code}\n' \
      http://127.0.0.1:8080/; then
    dc logs --tail=100 app nginx || true
    die "Local HTTP test failed. Review the logs above."
  fi

  if [[ -n "$PUBLIC_URL" ]]; then
    log "Checking public URL: $PUBLIC_URL"
    if ! curl --fail --silent --show-error --location --output /dev/null --max-time 30 \
        --write-out 'Public URL HTTP status: %{http_code}\n' \
        "$PUBLIC_URL"; then
      dc logs --tail=100 app nginx || true
      die "Public URL test failed. Check DNS, Caddy, TLS, and app logs."
    fi
  else
    log "Skipping public URL test. Set PUBLIC_URL to test the site through Caddy."
  fi
else
  log "curl is not installed on the host; skipping HTTP tests."
fi

log "Deployment steps completed."
log "Review application and queue logs before considering the deployment finished:"
printf '  docker compose --env-file "%s" -f "%s" logs --tail=100 app nginx queue\n' "$ENV_FILE" "$COMPOSE_FILE"
