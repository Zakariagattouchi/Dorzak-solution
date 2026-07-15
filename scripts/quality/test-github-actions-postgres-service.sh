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
export REAL_JQ
REAL_JQ="$(command -v jq)"
cat > "$TEMP/bin/docker" <<'DOCKER'
#!/usr/bin/env bash
set -euo pipefail
printf '%q ' "$@" >> "$FAKE_DOCKER_STATE/calls"
printf '\n' >> "$FAKE_DOCKER_STATE/calls"
readonly ID='aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'
case "$1 $2" in
  'pull docker.io/library/postgres@sha256:c95fd5346040eba2de3c435e14874af18f5d681fb5848d4f081dbead0878af28') exit 0 ;;
  'image inspect')
    case "$4" in ('{{.Architecture}}') printf '%s\n' "${FAKE_ARCH:-amd64}" ;; ('{{.Os}}') printf 'linux\n' ;; (*) exit 2 ;; esac ;;
  'run -d')
    cidfile=''
    previous=''
    for argument in "$@"; do
      if [[ "$previous" == --cidfile ]]; then cidfile="$argument"; fi
      previous="$argument"
    done
    nonce="$(printf '%s\n' "$*" | sed -n 's/.*dorzak\.instance_nonce_sha256=\([0-9a-f]\{64\}\).*/\1/p')"
    printf '%s' "$nonce" > "$FAKE_DOCKER_STATE/nonce"
    if [[ -n "$cidfile" ]]; then
      test ! -e "$cidfile"
      printf '%s\n' "$ID" > "$cidfile"
      perl -e 'printf "%04o\n", (stat($ARGV[0]))[2] & 07777' "$cidfile" > "$FAKE_DOCKER_STATE/cid-mode"
    fi
    if [[ "${FAKE_FAULT:-}" == invalid-stdout ]]; then printf 'not-a-container-id\n'; else printf '%s\n' "$ID"; fi ;;
  exec\ *)
    if [[ "$*" == *pg_isready* || "$*" == *'REVOKE CONNECT'* ]]; then exit 0; fi
    if [[ "$*" == *server_version_num* ]]; then
      if [[ "${FAKE_FAULT:-}" == bad-version ]]; then printf '150000\n'; else printf '160014\n'; fi
      exit 0
    fi
    if [[ "$*" == *current_setting* ]]; then cat "$FAKE_DOCKER_STATE/nonce"; printf '\n'; exit 0; fi
    exit 2 ;;
  'port aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa')
    if [[ "${FAKE_FAULT:-}" == bad-port ]]; then printf '0.0.0.0:invalid\n'; else printf '127.0.0.1:54321\n'; fi ;;
  'inspect --format')
    case "$3" in
      *dorzak.p00.kind*) printf 'postgresql-service\n' ;;
      *dorzak.p00.run*) printf '7001\n' ;;
      *dorzak.p00.job*) printf 'postgresql-16\n' ;;
      *dorzak.p00.attempt*) printf '1\n' ;;
      *dorzak.p00.no-real-data*) printf 'true\n' ;;
      *) exit 2 ;;
    esac ;;
  'rm -f') printf '%s\n' "$3" >> "$FAKE_DOCKER_STATE/removed" ;;
  'inspect aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa') test ! -f "$FAKE_DOCKER_STATE/removed" ;;
  *) exit 2 ;;
esac
DOCKER
cat > "$TEMP/bin/jq" <<'JQ'
#!/usr/bin/env bash
set -euo pipefail
if [[ "${FAKE_FAULT:-}" == state-write && "$*" == *lifecycleId* ]]; then
  exit 73
fi
exec "$REAL_JQ" "$@"
JQ
cat > "$TEMP/bin/openssl" <<'OPENSSL'
#!/usr/bin/env bash
set -euo pipefail
test "$1 $2" = 'rand -hex'
case "$3" in
  32) printf '%064d\n' 0 | tr 0 1 ;;
  6) printf '%012d\n' 0 | tr 0 2 ;;
  *) exit 2 ;;
esac
OPENSSL
chmod +x "$TEMP/bin/docker" "$TEMP/bin/jq" "$TEMP/bin/openssl"

HELPER_PATH="$TEMP/helper-path"
mkdir -p "$HELPER_PATH"
for command_name in awk bash cat chmod mkdir mv perl rm rmdir sed seq shasum sleep tr; do
  ln -s "$(command -v "$command_name")" "$HELPER_PATH/$command_name"
done
ln -s "$TEMP/bin/docker" "$HELPER_PATH/docker"
ln -s "$TEMP/bin/jq" "$HELPER_PATH/jq"
ln -s "$TEMP/bin/openssl" "$HELPER_PATH/openssl"
cat > "$HELPER_PATH/rg" <<'RG'
#!/usr/bin/env bash
set -euo pipefail
: > "$FAKE_DOCKER_STATE/rg-sentinel-invoked"
printf 'P00_GHA_POSTGRES_RG_SENTINEL FAIL invoked\n' >&2
exit 97
RG
chmod +x "$HELPER_PATH/rg"

export PATH="$TEMP/bin:$PATH"
export RUNNER_TEMP="$TEMP/runner"
export RUNNER_OS=Linux
export RUNNER_ARCH=X64
export GITHUB_RUN_ID=7001
export GITHUB_JOB=postgresql-16
export GITHUB_RUN_ATTEMPT=1
export GITHUB_ENV="$TEMP/github-env"
start_output="$(PATH="$HELPER_PATH" "$HELPER" start)"
test -z "$(printf '%s\n' "$start_output" | rg -v '^::add-mask::' | rg 'postgresql://|POSTGRES_PASSWORD' || true)"
rg -x 'P00_PG_IDENTITY=docker.io/library/postgres@sha256:c95fd5346040eba2de3c435e14874af18f5d681fb5848d4f081dbead0878af28' "$GITHUB_ENV"
rg -x 'P00_PG_ATTESTATION_SHA256=[0-9a-f]{64}' "$GITHUB_ENV"
rg -x 'P00_E2E_SERVICE_LIFECYCLE_ID=[0-9a-f]{64}' "$GITHUB_ENV"
PATH="$HELPER_PATH" "$HELPER" stop
test ! -e "$FAKE_DOCKER_STATE/rg-sentinel-invoked"
test "$(rg -c '^rm -f a{64} $' "$FAKE_DOCKER_STATE/calls")" = 1
test -z "$(rg 'rm -f .*(\*|dorzak-p00-postgres)' "$FAKE_DOCKER_STATE/calls" || true)"

fault_failures=0
run_fault_case() {
  local fault="$1"
  local log="$TEMP/$fault.log"
  local failed=0
  rm -rf "$RUNNER_TEMP/dorzak-p00-postgres" "$FAKE_DOCKER_STATE"
  mkdir -p "$FAKE_DOCKER_STATE"
  : > "$GITHUB_ENV"
  export FAKE_FAULT="$fault"
  set +e
  "$HELPER" start 2>&1 | rg -v '^::add-mask::' > "$log"
  local helper_status="${PIPESTATUS[0]}"
  set -e
  unset FAKE_FAULT

  if [[ "$helper_status" -eq 0 ]]; then failed=1; fi
  if [[ "$(rg '^rm -f ' "$FAKE_DOCKER_STATE/calls" || true)" \
    != 'rm -f aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa ' ]]; then
    failed=1
  fi
  if [[ "$(cat "$FAKE_DOCKER_STATE/cid-mode" 2>/dev/null || true)" != 0600 ]]; then failed=1; fi
  for label in \
    'dorzak.p00.kind=postgresql-service' \
    'dorzak.p00.run=7001' \
    'dorzak.p00.job=postgresql-16' \
    'dorzak.p00.attempt=1' \
    'dorzak.p00.no-real-data=true'; do
    if ! rg -q -- "--label $label" "$FAKE_DOCKER_STATE/calls"; then failed=1; fi
  done
  if rg -q \
    'postgresql://|POSTGRES_PASSWORD|1111111111111111111111111111111111111111111111111111111111111111|docker (rm|stop).*(\*|--filter|--label|--name)|rm -f.*(\*|dorzak-p00-postgres)' \
    "$log"; then
    failed=1
  fi
  if [[ "$failed" -ne 0 ]]; then
    printf 'P00_GHA_POSTGRES_FAULT_ASSERTION FAIL case=%s\n' "$fault" >&2
    fault_failures=$((fault_failures + 1))
  fi
}

run_fault_case invalid-stdout
run_fault_case state-write
run_fault_case bad-version
run_fault_case bad-port
test "$fault_failures" -eq 0

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
