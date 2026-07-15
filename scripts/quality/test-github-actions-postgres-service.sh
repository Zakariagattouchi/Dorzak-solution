#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
HELPER="$ROOT/scripts/quality/github-actions-postgres-service"
test "$(rg -c 'docker.io/library/postgres@sha256:c95fd5346040eba2de3c435e14874af18f5d681fb5848d4f081dbead0878af28' "$HELPER")" = 1
test -z "$(rg -n 'postgres:[0-9]' "$HELPER" || true)"
test -z "$(rg -n 'docker (rm|stop).*(\*|\$\()' "$HELPER" || true)"
bash -n "$HELPER"

TEMP="$(mktemp -d)"
trap 'rm -rf "$TEMP"' EXIT
mkdir -p "$TEMP/bin" "$TEMP/runner"
export FAKE_DOCKER_STATE="$TEMP/docker"
mkdir -p "$FAKE_DOCKER_STATE"
cat > "$TEMP/bin/docker" <<'DOCKER'
#!/usr/bin/env bash
set -euo pipefail
printf '%q ' "$@" >> "$FAKE_DOCKER_STATE/calls"
printf '\n' >> "$FAKE_DOCKER_STATE/calls"
case "$1 $2" in
  'pull docker.io/library/postgres@sha256:c95fd5346040eba2de3c435e14874af18f5d681fb5848d4f081dbead0878af28') exit 0 ;;
  'image inspect')
    case "$4" in ('{{.Architecture}}') printf '%s\n' "${FAKE_ARCH:-amd64}" ;; ('{{.Os}}') printf 'linux\n' ;; (*) exit 2 ;; esac ;;
  'run -d')
    nonce="$(printf '%s\n' "$*" | sed -n 's/.*dorzak\.instance_nonce_sha256=\([0-9a-f]\{64\}\).*/\1/p')"
    printf '%s' "$nonce" > "$FAKE_DOCKER_STATE/nonce"
    printf '%064d\n' 0 | tr 0 a ;;
  exec\ *)
    if printf '%s\n' "$*" | rg -q 'pg_isready|REVOKE CONNECT'; then exit 0; fi
    if printf '%s\n' "$*" | rg -q 'server_version_num'; then printf '160014\n'; exit 0; fi
    if printf '%s\n' "$*" | rg -q 'current_setting'; then cat "$FAKE_DOCKER_STATE/nonce"; printf '\n'; exit 0; fi
    exit 2 ;;
  'port aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa') printf '127.0.0.1:54321\n' ;;
  'inspect --format')
    if printf '%s\n' "$3" | rg -q 'dorzak.p00.run'; then printf '7001\n'; else printf 'postgresql-16\n'; fi ;;
  'rm -f') printf '%s' removed > "$FAKE_DOCKER_STATE/removed" ;;
  'inspect aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa') test ! -f "$FAKE_DOCKER_STATE/removed" ;;
  *) exit 2 ;;
esac
DOCKER
chmod +x "$TEMP/bin/docker"

export PATH="$TEMP/bin:$PATH"
export RUNNER_TEMP="$TEMP/runner"
export RUNNER_OS=Linux
export RUNNER_ARCH=X64
export GITHUB_RUN_ID=7001
export GITHUB_JOB=postgresql-16
export GITHUB_RUN_ATTEMPT=1
export GITHUB_ENV="$TEMP/github-env"
start_output="$($HELPER start)"
test -z "$(printf '%s\n' "$start_output" | rg -v '^::add-mask::' | rg 'postgresql://|POSTGRES_PASSWORD' || true)"
rg -x 'P00_PG_IDENTITY=docker.io/library/postgres@sha256:c95fd5346040eba2de3c435e14874af18f5d681fb5848d4f081dbead0878af28' "$GITHUB_ENV"
rg -x 'P00_PG_ATTESTATION_SHA256=[0-9a-f]{64}' "$GITHUB_ENV"
rg -x 'P00_E2E_SERVICE_LIFECYCLE_ID=[0-9a-f]{64}' "$GITHUB_ENV"
$HELPER stop
test "$(rg -c '^rm -f a{64} $' "$FAKE_DOCKER_STATE/calls")" = 1
test -z "$(rg 'rm -f .*(\*|dorzak-p00-postgres)' "$FAKE_DOCKER_STATE/calls" || true)"

rm -rf "$RUNNER_TEMP/dorzak-p00-postgres" "$FAKE_DOCKER_STATE"
mkdir -p "$FAKE_DOCKER_STATE"
: > "$GITHUB_ENV"
export FAKE_ARCH=arm64
if "$HELPER" start >"$TEMP/wrong-arch.log" 2>&1; then exit 1; fi
test -z "$(rg 'postgresql://|POSTGRES_PASSWORD' "$TEMP/wrong-arch.log" || true)"

export GITHUB_RUN_ATTEMPT=2
if "$HELPER" start >"$TEMP/retry.log" 2>&1; then exit 1; fi
rg -q 'retry' "$TEMP/retry.log"
printf 'P00_GHA_POSTGRES_TEST PASS\n'
