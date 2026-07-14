#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
expected=$'composer-validation\nphp-style-static\nsqlite\npostgresql-16\nfrontend\nplaywright'
actual="$("$ROOT/scripts/quality/run-p00" --list)"
[[ "$actual" == "$expected" ]]
set +e
output="$("$ROOT/scripts/quality/run-p00" invalid-job 2>&1)"
result="$?"
set -e
[[ "$result" -eq 64 ]]
[[ "$output" == 'Unknown P00 job: invalid-job' ]]
dispatcher_command="$(sed -n '/test-run-p00\.sh/p' "$ROOT/scripts/quality/run-p00")"
if [[ "$dispatcher_command" != '      (cd "$ROOT" && bash scripts/quality/test-run-p00.sh | tee "$JOB_ARTIFACTS/dispatcher.tap") || return $?' ]]; then
  printf 'DISPATCH_INTERPRETER_RED actual=%q\n' "$dispatcher_command" >&2
  exit 1
fi

real_bash="$(command -v bash)"
real_git="$(command -v git)"
real_node="$(command -v node)"
seam="$(mktemp -d "${TMPDIR:-/tmp}/dorzak-p00-dispatch.XXXXXX")"
trap 'rm -rf "$seam"' EXIT
events="$seam/events"
results="$seam/results"
bypasses="$seam/status-bypasses"
: > "$events"
: > "$results"
: > "$bypasses"

export P00_TEST_ROOT="$ROOT"
export P00_TEST_REAL_GIT="$real_git"
export P00_TEST_REAL_NODE="$real_node"
export P00_TEST_EVENTS="$events"
export P00_TEST_BYPASSES="$bypasses"

cat > "$seam/git" <<'SH'
#!/bin/sh
if [ "$#" -eq 5 ] \
  && [ "$1" = '-C' ] \
  && [ "$2" = "$P00_TEST_ROOT" ] \
  && [ "$3" = 'status' ] \
  && [ "$4" = '--short' ] \
  && [ "$5" = '--untracked-files=normal' ]; then
  printf '%s\n' clean >> "$P00_TEST_BYPASSES"
  exit 0
fi
exec "$P00_TEST_REAL_GIT" "$@"
SH

cat > "$seam/node" <<'SH'
#!/bin/sh
case "${1:-}" in
  --input-type=module|-p)
    exec "$P00_TEST_REAL_NODE" "$@"
    ;;
  --test)
    printf '%s\n' later:composer-node >> "$P00_TEST_EVENTS"
    exit 0
    ;;
esac
if [ "${1:-}" = "$P00_TEST_ROOT/scripts/quality/p00.mjs" ] \
  && [ "${2:-}" = 'write-result' ]; then
  exit 0
fi
if [ "${1:-}" = 'scripts/quality/p00.mjs' ] \
  && [ "${2:-}" = 'bundle' ]; then
  printf '%s\n' later:frontend-bundle >> "$P00_TEST_EVENTS"
  exit 0
fi
exec "$P00_TEST_REAL_NODE" "$@"
SH

cat > "$seam/bash" <<'SH'
#!/bin/sh
printf '%s\n' first:composer >> "$P00_TEST_EVENTS"
exit 43
SH

cat > "$seam/composer" <<'SH'
#!/bin/sh
printf '%s\n' later:composer-validate >> "$P00_TEST_EVENTS"
exit 0
SH

cat > "$seam/npm" <<'SH'
#!/bin/sh
if [ "${1:-}" = 'run' ] && [ "${2:-}" = 'format:check' ]; then
  printf '%s\n' first:frontend-format >> "$P00_TEST_EVENTS"
  exit 42
fi
if [ "${1:-}" = 'run' ] && [ "${2:-}" = 'test:e2e' ]; then
  printf '%s\n' first:playwright >> "$P00_TEST_EVENTS"
  exit 44
fi
printf 'later:npm' >> "$P00_TEST_EVENTS"
printf ' %s' "$@" >> "$P00_TEST_EVENTS"
printf '\n' >> "$P00_TEST_EVENTS"
exit 0
SH

cat > "$seam/cp" <<'SH'
#!/bin/sh
printf '%s\n' later:playwright-copy >> "$P00_TEST_EVENTS"
exit 0
SH
chmod +x "$seam/git" "$seam/node" "$seam/bash" "$seam/composer" "$seam/npm" "$seam/cp"

runner_class="$($real_node -e "const r=require('$ROOT/docs/superpowers/control/execution/p00-execution-entry.json');process.stdout.write(r.execution.runnerClasses.local)")"
for sentinel in 'composer-validation 43' 'frontend 42' 'playwright 44'; do
  job="${sentinel% *}"
  expected_status="${sentinel##* }"
  set +e
  PATH="$seam:$PATH" \
    P00_ARTIFACT_DIR="$seam/artifacts-$job" \
    P00_CONTROL_RECORD='docs/superpowers/control/execution/p00-execution-entry.json' \
    P00_RUNNER_ROLE=local \
    P00_RUNNER_CLASS="$runner_class" \
    "$real_bash" "$ROOT/scripts/quality/run-p00" "$job" >/dev/null 2>&1
  actual_status="$?"
  set -e
  printf '%s %s\n' "$job" "$actual_status" >> "$results"
done

actual_results="$(cat "$results")"
expected_results=$'composer-validation 43\nfrontend 42\nplaywright 44'
bypass_count="$(wc -l < "$bypasses" | tr -d ' ')"
later_count="$(awk '/^later:/{count++} END{print count+0}' "$events")"
if [[ "$actual_results" != "$expected_results" || "$bypass_count" -ne 3 || "$later_count" -ne 0 ]]; then
  printf 'DISPATCH_SENTINEL_RED results=%q bypasses=%s later=%s events=%q\n' \
    "$actual_results" "$bypass_count" "$later_count" "$(tr '\n' ',' < "$events")" >&2
  exit 1
fi
printf '%s\n' \
  'TAP version 13' \
  'ok 1 - lists six ordered jobs' \
  'ok 2 - invalid job and sequential steps fail closed' \
  '1..2'
