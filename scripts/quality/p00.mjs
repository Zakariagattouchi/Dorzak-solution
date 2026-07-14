import { createHash } from 'node:crypto';
import { execFileSync } from 'node:child_process';
import {
  copyFileSync,
  existsSync,
  lstatSync,
  mkdtempSync,
  mkdirSync,
  readFileSync,
  readdirSync,
  realpathSync,
  renameSync,
  rmSync,
  writeFileSync,
} from 'node:fs';
import { basename, dirname, join, relative, resolve } from 'node:path';
import { arch, platform, release } from 'node:os';
import { fileURLToPath } from 'node:url';
import { gzipSync } from 'node:zlib';

const MODULE = fileURLToPath(import.meta.url);
const ROOT = resolve(dirname(MODULE), '../..');
const ARTIFACTS = () => resolve(process.env.P00_ARTIFACT_DIR || join(ROOT, '.artifacts/p00'));
const JOB_ARTIFACTS = () =>
  resolve(process.env.P00_JOB_ARTIFACT_DIR || join(ARTIFACTS(), requiredEnvironment('P00_JOB')));
const BUNDLE_ARTIFACTS = () =>
  resolve(process.env.P00_JOB_ARTIFACT_DIR || join(ARTIFACTS(), 'standalone-bundle'));
const BUNDLE_ZLIB_VERSION = '1.3.1-e00f703';
const SHA40 = /^[0-9a-f]{40}$/;
const SHA64 = /^[0-9a-f]{64}$/;
const origins = new WeakMap();

function invariant(condition, message) {
  if (!condition) throw new Error(message);
}

export function sha256(value) {
  return createHash('sha256').update(value).digest('hex');
}

function canonical(value) {
  if (Array.isArray(value)) return value.map(canonical);
  if (value && typeof value === 'object') {
    return Object.fromEntries(
      Object.keys(value)
        .sort()
        .map((key) => [key, canonical(value[key])]),
    );
  }
  return value;
}

function rejectSecrets(value, path = '$') {
  const blockedKeys = new Set([
    'authorization',
    'proxyauthorization',
    'cookie',
    'setcookie',
    'apikey',
    'clientsecret',
    'accesstoken',
    'refreshtoken',
    'idtoken',
    'password',
    'passwd',
    'secret',
    'token',
    'dburl',
    'databaseurl',
    'dsn',
    'credential',
    'privatekey',
    'connectionstring',
  ]);
  if (typeof value === 'string') {
    invariant(!/-----BEGIN [A-Z ]*PRIVATE KEY-----/i.test(value), 'Private key at ' + path);
    invariant(
      !/\b(?:bearer|basic)\s+[A-Za-z0-9._~+\/=-]+/i.test(value),
      'Authorization value at ' + path,
    );
    invariant(!/^(?:cookie|set-cookie)\s*:/im.test(value), 'Cookie header at ' + path);
    invariant(
      !/\b(?:api[_-]?key|client[_-]?secret|access[_-]?token|refresh[_-]?token|id[_-]?token|password|passwd|secret|token|db[_-]?url|database[_-]?url|dsn|credential|private[_-]?key|connection[_-]?string)\s*[=:]\s*\S+/i.test(
        value,
      ),
      'Secret assignment at ' + path,
    );
    invariant(
      !/[?&](?:api[_-]?key|token|secret|password|credential)=/i.test(value),
      'Secret query key at ' + path,
    );
    invariant(
      !/[a-z][a-z0-9+.-]*:\/\/[^\/\s:@]+:[^\/\s@]+@/i.test(value),
      'Credential URL at ' + path,
    );
    invariant(!/\bAKIA[0-9A-Z]{16}\b/.test(value), 'AWS access key at ' + path);
    invariant(
      !/\b(?:gh[pousr]_[A-Za-z0-9]{20,}|github_pat_[A-Za-z0-9_]{20,})\b/.test(value),
      'GitHub token at ' + path,
    );
    invariant(
      !/\beyJ[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\b/.test(value),
      'JWT at ' + path,
    );
    return;
  }
  if (Array.isArray(value)) {
    value.forEach((item, index) => rejectSecrets(item, path + '[' + index + ']'));
    return;
  }
  if (value && typeof value === 'object') {
    for (const [key, item] of Object.entries(value)) {
      const normalized = key.toLowerCase().replace(/[^a-z0-9]/g, '');
      invariant(!blockedKeys.has(normalized), 'Secret key at ' + path + '.' + key);
      rejectSecrets(item, path + '.' + key);
    }
  }
}

export function stableJson(value) {
  rejectSecrets(value);
  return JSON.stringify(canonical(value), null, 2) + '\n';
}

function readJson(path) {
  return JSON.parse(readFileSync(path, 'utf8'));
}

function writeJson(path, value) {
  mkdirSync(dirname(path), { recursive: true });
  writeFileSync(path, stableJson(value), { flag: 'w' });
}

const contractBytes = readFileSync(join(ROOT, 'scripts/quality/p00-contract.json'));
export const contract = JSON.parse(contractBytes);
export const contractSha256 = sha256(contractBytes);
invariant(contract.schemaVersion === 1, 'Unsupported P00 contract');
invariant(contract.jobs.length === 6, 'P00 contract must have six jobs');

function command(file, args, cwd = ROOT) {
  return execFileSync(file, args, {
    cwd,
    encoding: 'utf8',
    stdio: ['ignore', 'pipe', 'ignore'],
  }).trim();
}

function version(file, args, cwd = ROOT) {
  const match = command(file, args, cwd).match(/\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?/);
  invariant(match, 'Cannot parse version for ' + file);
  return match[0];
}

function requiredEnvironment(name) {
  const value = process.env[name];
  invariant(typeof value === 'string' && value.length > 0, name + ' is required');
  return value;
}

function expectedPostgresqlIdentity() {
  const kind = requiredEnvironment('P00_PG_IDENTITY_KIND');
  const identity = requiredEnvironment('P00_PG_IDENTITY');
  const attestationSha256 = requiredEnvironment('P00_PG_ATTESTATION_SHA256');
  const attestationPath = requiredEnvironment('P00_PG_ATTESTATION_PATH');
  const instanceNonceSha256 = requiredEnvironment('P00_PG_INSTANCE_NONCE_SHA256');
  invariant(
    kind === 'oci' || kind === 'external-attestation',
    'Unapproved PostgreSQL identity kind',
  );
  invariant(SHA64.test(attestationSha256), 'PostgreSQL attestation hash is invalid');
  invariant(SHA64.test(instanceNonceSha256), 'PostgreSQL instance nonce hash is invalid');
  invariant(
    sha256(readFileSync(attestationPath)) === attestationSha256,
    'PostgreSQL attestation bytes changed',
  );
  const attestation = readJson(attestationPath);
  invariant(
    stableJson(Object.keys(attestation)) ===
      stableJson([
        'schemaVersion',
        'kind',
        'identity',
        'serverMajor',
        'immutable',
        'instanceNonceSha256',
      ]),
    'PostgreSQL attestation keys are not closed',
  );
  invariant(
    attestation.schemaVersion === 2 &&
      attestation.kind === kind &&
      attestation.identity === identity &&
      attestation.serverMajor === 16 &&
      attestation.immutable === true &&
      attestation.instanceNonceSha256 === instanceNonceSha256,
    'PostgreSQL attestation content mismatch',
  );
  if (kind === 'oci') {
    invariant(/@sha256:[0-9a-f]{64}$/.test(identity), 'OCI PostgreSQL identity is mutable');
  } else {
    invariant(
      /^external:[A-Za-z0-9._/-]+@[A-Za-z0-9._:-]+#sha256:[0-9a-f]{64}$/.test(identity),
      'External PostgreSQL attestation identity is mutable',
    );
  }
  rejectSecrets(identity, 'postgresql.identity');
  return { kind, identity, attestationSha256, instanceNonceSha256 };
}

function collectInputs() {
  const chromiumPath = command(process.execPath, [
    '-e',
    "process.stdout.write(require('playwright').chromium.executablePath())",
  ]);
  const playwrightPackage = readJson(join(ROOT, 'node_modules/@playwright/test/package.json'));
  const browsers = readJson(join(ROOT, 'node_modules/playwright-core/browsers.json'));
  const chromium = browsers.browsers.find((browser) => browser.name === 'chromium');
  invariant(chromium && /^\d+$/.test(chromium.revision), 'Playwright Chromium revision is missing');
  const control = readJson(resolve(ROOT, requiredEnvironment('P00_CONTROL_RECORD')));
  const runnerClasses = control.execution?.runnerClasses;
  assertExactKeys(runnerClasses, ['local', 'ci'], '$.control.execution.runnerClasses');
  invariant(
    Object.values(runnerClasses).every(
      (value) => typeof value === 'string' && /^[A-Za-z0-9][A-Za-z0-9._-]{0,63}$/.test(value),
    ),
    'Runner classes are invalid',
  );
  invariant(runnerClasses.local !== runnerClasses.ci, 'Runner classes must be distinct');
  const runnerRole = requiredEnvironment('P00_RUNNER_ROLE');
  invariant(['local', 'ci'].includes(runnerRole), 'Runner role is invalid');
  invariant(
    requiredEnvironment('P00_RUNNER_CLASS') === runnerClasses[runnerRole],
    'Runner role/class binding mismatch',
  );
  const portableInputs = {
    contractSha256,
    runtime: {
      php: command('php', ['-r', 'echo PHP_VERSION;']),
      composer: version('composer', ['--version'], join(ROOT, 'backend')),
      node: process.versions.node,
      npm: version('npm', ['--version']),
    },
    lockfileSha256: {
      composer: sha256(readFileSync(join(ROOT, 'backend/composer.lock'))),
      npm: sha256(readFileSync(join(ROOT, 'package-lock.json'))),
    },
    postgresql: (() => {
      const value = expectedPostgresqlIdentity();
      return {
        kind: value.kind,
        identity: value.identity,
        policy: 'postgresql-16-test-closed-transport-v1',
      };
    })(),
    playwright: {
      packageVersion: playwrightPackage.version,
      chromiumRevision: chromium.revision,
    },
    bundleAlgorithms: {
      assetSelection: 'html-module-entry-and-modulepreload-v1',
      gzip: 'node-zlib-level-9',
    },
    runnerClasses,
  };
  invariant(
    portableInputs.runtime.php === requiredEnvironment('P00_PHP_VERSION'),
    'PHP pin mismatch',
  );
  invariant(
    portableInputs.runtime.composer === requiredEnvironment('P00_COMPOSER_VERSION'),
    'Composer pin mismatch',
  );
  invariant(
    portableInputs.runtime.node === requiredEnvironment('P00_NODE_VERSION'),
    'Node pin mismatch',
  );
  invariant(
    portableInputs.runtime.npm === requiredEnvironment('P00_NPM_VERSION'),
    'npm pin mismatch',
  );
  return {
    portableInputs,
    platformObservation: {
      os: platform(),
      arch: arch(),
      osRelease: release(),
      runnerRole,
      runnerClass: requiredEnvironment('P00_RUNNER_CLASS'),
      zlib: process.versions.zlib,
      chromiumExecutableSha256: sha256(readFileSync(chromiumPath)),
    },
  };
}

function safeAssetPath(distDirectory, url) {
  invariant(!/[?#]/.test(url), 'Bundle URL query/fragment is prohibited: ' + url);
  invariant(!/^[a-z][a-z0-9+.-]*:/i.test(url), 'External bundle URL is prohibited: ' + url);
  const decoded = decodeURIComponent(url).replace(/^\/+/, '');
  invariant(decoded.length > 0 && !decoded.includes('\\'), 'Invalid bundle URL: ' + url);
  invariant(!decoded.split('/').includes('..'), 'Bundle URL escapes dist: ' + url);
  const root = realpathSync(distDirectory);
  const candidate = resolve(root, decoded);
  invariant(candidate.startsWith(root + '/'), 'Bundle path escapes dist: ' + url);
  const stat = lstatSync(candidate);
  invariant(
    stat.isFile() && !stat.isSymbolicLink(),
    'Bundle asset is not a regular file: ' + decoded,
  );
  invariant(realpathSync(candidate) === candidate, 'Bundle asset aliases another path: ' + decoded);
  return { path: decoded, absolute: candidate };
}

function javascriptFiles(directory, root = directory) {
  const files = [];
  for (const name of readdirSync(directory).sort()) {
    const path = join(directory, name);
    const stat = lstatSync(path);
    invariant(!stat.isSymbolicLink(), 'Symlink in bundle output: ' + relative(root, path));
    if (stat.isDirectory()) files.push(...javascriptFiles(path, root));
    else if (stat.isFile() && name.endsWith('.js')) files.push(path);
    else invariant(stat.isFile(), 'Special file in bundle output: ' + relative(root, path));
  }
  return files;
}

function warningOccurrences(log, warning) {
  return log.split(warning).length - 1;
}

function assertBundle(value, enforceBudget = true) {
  rejectSecrets(value, '$.bundle');
  assertExactKeys(
    value,
    [
      'schemaVersion',
      'files',
      'minifiedBytes',
      'gzipBytes',
      'limitBytes',
      'nodeVersion',
      'zlibVersion',
      'largeChunkDebt',
    ],
    '$.bundle',
  );
  value.files?.forEach((file, index) =>
    assertExactKeys(file, ['path', 'minifiedBytes', 'gzipBytes'], '$.bundle.files[' + index + ']'),
  );
  assertExactKeys(
    value.largeChunkDebt,
    ['status', 'thresholdBytes', 'affectedFiles', 'message', 'messageSha256', 'occurrenceCount'],
    '$.bundle.largeChunkDebt',
  );
  invariant(value.schemaVersion === 1, 'Bundle schema mismatch');
  invariant(
    typeof value.nodeVersion === 'string' && value.nodeVersion.length > 0,
    'Bundle Node identity is missing',
  );
  invariant(
    typeof value.zlibVersion === 'string' && value.zlibVersion.length > 0,
    'Bundle zlib identity is missing',
  );
  invariant(
    Array.isArray(value.files) && value.files.length > 0,
    'Bundle file measurements are missing',
  );
  invariant(
    value.files.every(
      (file) =>
        typeof file.path === 'string' &&
        Number.isInteger(file.minifiedBytes) &&
        file.minifiedBytes >= 0 &&
        Number.isInteger(file.gzipBytes) &&
        file.gzipBytes >= 0,
    ),
    'Bundle file measurement is invalid',
  );
  invariant(
    new Set(value.files.map((file) => file.path)).size === value.files.length,
    'Bundle file is duplicated',
  );
  invariant(
    value.minifiedBytes === value.files.reduce((total, file) => total + file.minifiedBytes, 0),
    'Bundle raw total mismatch',
  );
  invariant(
    value.gzipBytes === value.files.reduce((total, file) => total + file.gzipBytes, 0),
    'Bundle gzip total mismatch',
  );
  invariant(Number.isInteger(value.gzipBytes) && value.gzipBytes >= 0, 'Bundle gzip is invalid');
  invariant(value.limitBytes === contract.bundle.initialGzipLimitBytes, 'Bundle limit changed');
  invariant(value.zlibVersion === BUNDLE_ZLIB_VERSION, 'Bundle zlib identity changed');
  if (enforceBudget)
    invariant(value.gzipBytes <= value.limitBytes, 'Initial JavaScript gzip budget exceeded');
  invariant(value.largeChunkDebt.status === contract.bundle.debtStatus, 'Vite debt status changed');
  invariant(
    value.largeChunkDebt.thresholdBytes === contract.bundle.largeChunkThresholdBytes,
    'Vite threshold changed',
  );
  invariant(value.largeChunkDebt.message === contract.bundle.warning, 'Vite warning text changed');
  invariant(
    value.largeChunkDebt.messageSha256 === sha256(contract.bundle.warning),
    'Vite warning hash changed',
  );
  invariant(
    value.largeChunkDebt.occurrenceCount === contract.bundle.expectedOccurrences,
    'Vite warning occurrence changed',
  );
  invariant(
    value.largeChunkDebt.affectedFiles.length > 0,
    'Accepted Vite large-chunk debt disappeared without an approved task',
  );
}

export function measureBundle(
  distDirectory,
  limitBytes = contract.bundle.initialGzipLimitBytes,
  buildLogPath = null,
  artifactDirectory = BUNDLE_ARTIFACTS(),
) {
  invariant(
    process.versions.zlib === BUNDLE_ZLIB_VERSION,
    'zlib runtime differs from the P00 bundle contract',
  );
  invariant(limitBytes === contract.bundle.initialGzipLimitBytes, 'Bundle limit cannot be changed');
  const pinnedNode = readFileSync(join(ROOT, '.node-version'), 'utf8').trim().replace(/^v/, '');
  invariant(process.versions.node === pinnedNode, 'Node runtime differs from .node-version');
  const html = readFileSync(join(distDirectory, 'index.html'), 'utf8');
  const urls = [];
  for (const tag of html.match(/<(?:script|link)\b[^>]*>/gi) || []) {
    const type = tag.match(/\btype=["']([^"']+)["']/i)?.[1];
    const rel = tag.match(/\brel=["']([^"']+)["']/i)?.[1];
    const src = tag.match(/\bsrc=["']([^"']+)["']/i)?.[1];
    const href = tag.match(/\bhref=["']([^"']+)["']/i)?.[1];
    const value = type === 'module' && src ? src : rel === 'modulepreload' && href ? href : null;
    if (value && value.endsWith('.js') && !urls.includes(value)) urls.push(value);
  }
  invariant(urls.length > 0, 'No initial JavaScript assets found');
  const files = urls.map((url) => {
    const asset = safeAssetPath(distDirectory, url);
    const bytes = readFileSync(asset.absolute);
    return {
      path: asset.path,
      minifiedBytes: bytes.length,
      gzipBytes: gzipSync(bytes, { level: 9 }).length,
    };
  });
  const root = realpathSync(distDirectory);
  const affectedFiles = javascriptFiles(root)
    .filter((path) => readFileSync(path).length > contract.bundle.largeChunkThresholdBytes)
    .map((path) => relative(root, path))
    .sort();
  const logOccurrences = buildLogPath
    ? warningOccurrences(readFileSync(buildLogPath, 'utf8'), contract.bundle.warning)
    : affectedFiles.length > 0
      ? 1
      : 0;
  invariant(
    logOccurrences === contract.bundle.expectedOccurrences,
    'Vite warning occurrence count changed',
  );
  const result = {
    schemaVersion: 1,
    files,
    minifiedBytes: files.reduce((total, file) => total + file.minifiedBytes, 0),
    gzipBytes: files.reduce((total, file) => total + file.gzipBytes, 0),
    limitBytes,
    nodeVersion: process.versions.node,
    zlibVersion: process.versions.zlib,
    largeChunkDebt: {
      status: contract.bundle.debtStatus,
      thresholdBytes: contract.bundle.largeChunkThresholdBytes,
      affectedFiles,
      message: contract.bundle.warning,
      messageSha256: sha256(contract.bundle.warning),
      occurrenceCount: logOccurrences,
    },
  };
  assertBundle(result, false);
  writeJson(join(artifactDirectory, 'bundle.json'), result);
  invariant(result.gzipBytes <= result.limitBytes, 'Initial JavaScript gzip budget exceeded');
  return result;
}

function xmlAttribute(attributes, name) {
  const expression = new RegExp('\\b' + name + '=["\'](\\d+)["\']');
  const value = attributes.match(expression)?.[1];
  invariant(value !== undefined, 'JUnit attribute missing: ' + name);
  return Number(value);
}

function junitCounts(path) {
  const xml = readFileSync(path, 'utf8');
  const root = xml.match(/<testsuite\b([^>]*)>/);
  invariant(root, 'JUnit root is missing');
  const failures =
    xmlAttribute(root[1], 'failures') +
    (/\berrors=/.test(root[1]) ? xmlAttribute(root[1], 'errors') : 0);
  return {
    testCount: xmlAttribute(root[1], 'tests'),
    failureCount: failures,
    unexplainedSkipCount: /\bskipped=/.test(root[1]) ? xmlAttribute(root[1], 'skipped') : 0,
  };
}

function tapCount(path) {
  const lines = readFileSync(path, 'utf8').trimEnd().split('\n');
  const plans = lines.map((line) => line.match(/^1\.\.(\d+)(?:\s+#.*)?$/)).filter(Boolean);
  invariant(plans.length === 1, 'TAP requires one unique top-level plan: ' + path);
  const count = Number(plans[0][1]);
  invariant(
    lines.filter((line) => /^ok \d+\b/.test(line)).length === count,
    'TAP pass count mismatch',
  );
  invariant(
    lines.every((line) => !/^not ok \d+\b/.test(line)),
    'TAP failure present',
  );
  return count;
}

function measuredCounts(job, directory) {
  if (job === 'composer-validation')
    return {
      testCount:
        tapCount(join(directory, 'dispatcher.tap')) + tapCount(join(directory, 'p00-node.tap')),
      failureCount: 0,
      unexplainedSkipCount: 0,
    };
  if (job === 'sqlite') return junitCounts(join(directory, 'sqlite.junit.xml'));
  if (job === 'postgresql-16') return junitCounts(join(directory, 'postgresql-16.junit.xml'));
  if (job === 'frontend') {
    const report = readJson(join(directory, 'vitest.json'));
    return {
      testCount: report.numTotalTests,
      failureCount: report.numFailedTests,
      unexplainedSkipCount: report.numPendingTests,
    };
  }
  if (job === 'playwright') {
    const report = readJson(join(directory, 'playwright.json'));
    invariant(
      report.stats && Number.isInteger(report.stats.expected),
      'Playwright stats are missing',
    );
    return {
      testCount:
        report.stats.expected + report.stats.unexpected + report.stats.flaky + report.stats.skipped,
      failureCount: report.stats.unexpected + report.stats.flaky,
      unexplainedSkipCount: report.stats.skipped,
    };
  }
  return { testCount: 0, failureCount: 0, unexplainedSkipCount: 0 };
}

const artifactNames = {
  'composer-validation': ['dispatcherTap', 'p00NodeTap'],
  'php-style-static': [],
  sqlite: ['junit'],
  'postgresql-16': ['junit', 'postgresqlIdentity'],
  frontend: ['vitest', 'bundle', 'viteBuildLog'],
  playwright: ['playwrightJson'],
};

function artifactHashes(job, directory) {
  const paths = {
    'composer-validation': {
      dispatcherTap: join(directory, 'dispatcher.tap'),
      p00NodeTap: join(directory, 'p00-node.tap'),
    },
    sqlite: { junit: join(directory, 'sqlite.junit.xml') },
    'postgresql-16': {
      junit: join(directory, 'postgresql-16.junit.xml'),
      postgresqlIdentity: join(directory, 'postgresql-identity.json'),
    },
    frontend: {
      vitest: join(directory, 'vitest.json'),
      bundle: join(directory, 'bundle.json'),
      viteBuildLog: join(directory, 'vite-build.log'),
    },
    playwright: { playwrightJson: join(directory, 'playwright.json') },
  };
  return Object.fromEntries(
    Object.entries(paths[job] || {}).map(([name, path]) => [
      name,
      {
        path: relative(directory, path),
        sha256: sha256(readFileSync(path)),
      },
    ]),
  );
}

function assertRecord(record, expectedJob, directory = null) {
  rejectSecrets(record, 'job[' + expectedJob + ']');
  assertExactKeys(
    record,
    [
      'schemaVersion',
      'job',
      'command',
      'integratedSha',
      'status',
      'exitCode',
      'retryAttempt',
      'testCount',
      'failureCount',
      'unexplainedSkipCount',
      'durationMs',
      'contractSha256',
      'inputFingerprintSha256',
      'inputs',
      'platformObservationFingerprintSha256',
      'platformObservation',
      'logSha256',
      'artifacts',
    ],
    'job[' + expectedJob + ']',
  );
  const declared = contract.jobs.find((job) => job.name === expectedJob);
  invariant(declared, 'Unknown P00 job: ' + expectedJob);
  invariant(
    record.schemaVersion === 1 && record.job === expectedJob,
    'Job record identity mismatch',
  );
  invariant(record.command === declared.command, 'Job command provenance mismatch');
  invariant(SHA40.test(record.integratedSha), 'Job SHA is invalid');
  invariant(
    record.status === 'passed' && record.exitCode === 0,
    'Job did not pass: ' + expectedJob,
  );
  invariant(record.retryAttempt === 1, 'Job retry is prohibited: ' + expectedJob);
  invariant(record.testCount === declared.testCount, 'Job count mismatch: ' + expectedJob);
  invariant(
    record.failureCount === 0 && record.unexplainedSkipCount === 0,
    'Job has failures/skips: ' + expectedJob,
  );
  invariant(
    Number.isInteger(record.durationMs) && record.durationMs >= 0,
    'Job duration is invalid',
  );
  invariant(record.contractSha256 === contractSha256, 'Job contract hash mismatch');
  invariant(SHA64.test(record.logSha256), 'Job log hash is invalid');
  invariant(Array.isArray(artifactNames[expectedJob]), 'Job artifact contract is missing');
  invariant(
    stableJson(Object.keys(record.artifacts).sort()) ===
      stableJson([...artifactNames[expectedJob]].sort()),
    'Job artifact names mismatch',
  );
  invariant(
    Object.values(record.artifacts).every(
      (reference) =>
        reference &&
        /^[A-Za-z0-9][A-Za-z0-9._-]*$/.test(reference.path) &&
        SHA64.test(reference.sha256),
    ),
    'Job artifact reference is invalid',
  );
  if (directory !== null) {
    const measured = measuredCounts(expectedJob, directory);
    invariant(
      measured.testCount === record.testCount &&
        measured.failureCount === record.failureCount &&
        measured.unexplainedSkipCount === record.unexplainedSkipCount,
      'Reporter-derived counts differ from job record',
    );
    invariant(
      sha256(readFileSync(join(directory, 'job.log'))) === record.logSha256,
      'Job log bytes changed',
    );
    for (const reference of Object.values(record.artifacts)) {
      const path = safeSibling(directory, reference.path);
      const stat = lstatSync(path);
      invariant(
        stat.isFile() && !stat.isSymbolicLink() && realpathSync(path) === path,
        'Raw artifact is unsafe',
      );
      invariant(sha256(readFileSync(path)) === reference.sha256, 'Raw artifact bytes changed');
    }
  }
  invariant(
    record.inputFingerprintSha256 === sha256(stableJson(record.inputs)),
    'Job input fingerprint mismatch',
  );
  validateSchema(inputsSchema, record.inputs, 'job[' + expectedJob + '].inputs');
  invariant(
    record.inputs.runnerClasses.local !== record.inputs.runnerClasses.ci,
    'Job runner classes must be distinct',
  );
  invariant(
    record.platformObservationFingerprintSha256 === sha256(stableJson(record.platformObservation)),
    'Job platform fingerprint mismatch',
  );
  validateSchema(
    platformObservationSchema,
    record.platformObservation,
    'job[' + expectedJob + '].platformObservation',
  );
  invariant(
    record.platformObservation.runnerClass ===
      record.inputs.runnerClasses[record.platformObservation.runnerRole],
    'Job runner role/class binding mismatch: ' + expectedJob,
  );
}

function writeGateResult(job, exitCode, durationMs, logPath) {
  const declared = contract.jobs.find((item) => item.name === job);
  invariant(declared, 'Unknown P00 job: ' + job);
  const { portableInputs: inputs, platformObservation } = collectInputs();
  const counts = measuredCounts(job, JOB_ARTIFACTS());
  if (job === 'frontend') assertBundle(readJson(join(JOB_ARTIFACTS(), 'bundle.json')));
  const record = {
    schemaVersion: 1,
    job,
    command: declared.command,
    integratedSha: command('git', ['rev-parse', 'HEAD']),
    status:
      exitCode === 0 &&
      counts.testCount === declared.testCount &&
      counts.failureCount === 0 &&
      counts.unexplainedSkipCount === 0
        ? 'passed'
        : 'failed',
    exitCode,
    retryAttempt: Number(process.env.P00_ATTEMPT || '1'),
    testCount: counts.testCount,
    failureCount: counts.failureCount,
    unexplainedSkipCount: counts.unexplainedSkipCount,
    durationMs,
    contractSha256,
    inputFingerprintSha256: sha256(stableJson(inputs)),
    inputs,
    platformObservationFingerprintSha256: sha256(stableJson(platformObservation)),
    platformObservation,
    logSha256: sha256(readFileSync(logPath)),
    artifacts: artifactHashes(job, JOB_ARTIFACTS()),
  };
  writeJson(join(JOB_ARTIFACTS(), 'result.json'), record);
  if (record.status !== 'passed') throw new Error('P00 job result failed validation: ' + job);
  return record;
}

export function aggregateRequiredGates(resultsDirectory) {
  const rootEntries = readdirSync(resultsDirectory).sort();
  const jobNames = contract.jobs.map((job) => job.name).sort();
  invariant(
    stableJson(rootEntries) === stableJson(jobNames),
    'Aggregate root must contain exactly six job directories',
  );
  for (const name of rootEntries) {
    const path = join(resultsDirectory, name);
    const stat = lstatSync(path);
    invariant(
      stat.isDirectory() && !stat.isSymbolicLink() && realpathSync(path) === path,
      'Aggregate job directory is unsafe: ' + name,
    );
  }
  const records = contract.jobs.map((job) => {
    const directory = join(resultsDirectory, job.name);
    for (const name of ['result.json', 'job.log']) {
      const path = join(directory, name);
      const stat = lstatSync(path);
      invariant(
        stat.isFile() && !stat.isSymbolicLink() && realpathSync(path) === path,
        'Aggregate canonical file is unsafe: ' + job.name + '/' + name,
      );
    }
    const record = readJson(join(directory, 'result.json'));
    const entries = readdirSync(directory).sort();
    const expected = [
      'job.log',
      'result.json',
      ...Object.values(record.artifacts).map((value) => value.path),
    ].sort();
    invariant(
      stableJson(entries) === stableJson(expected),
      'Job directory has missing or extra files: ' + job.name,
    );
    assertRecord(record, job.name, directory);
    return record;
  });
  const shas = new Set(records.map((record) => record.integratedSha));
  const fingerprints = new Set(records.map((record) => record.inputFingerprintSha256));
  const completeInputs = new Set(records.map((record) => stableJson(record.inputs)));
  invariant(shas.size === 1, 'P00 jobs used different SHAs');
  invariant(fingerprints.size === 1, 'P00 jobs used different declared inputs');
  invariant(completeInputs.size === 1, 'P00 jobs used different complete portable inputs');
  return {
    schemaVersion: 1,
    status: 'passed',
    integratedSha: records[0].integratedSha,
    retryAttempt: 1,
    contractSha256,
    inputFingerprintSha256: records[0].inputFingerprintSha256,
    inputs: records[0].inputs,
    platformObservationFingerprintSha256: records[0].platformObservationFingerprintSha256,
    platformObservation: records[0].platformObservation,
    testCount: records.reduce((total, record) => total + record.testCount, 0),
    failureCount: 0,
    unexplainedSkipCount: 0,
    jobs: records,
  };
}

function postgresqlObservation(
  serverVersionNum,
  databaseName,
  instanceNonceSha256,
  endpointSha256,
) {
  const expected = expectedPostgresqlIdentity();
  const numericVersion = Number(serverVersionNum);
  invariant(
    Number.isInteger(numericVersion) && numericVersion >= 160000 && numericVersion < 170000,
    'PostgreSQL is not major 16',
  );
  invariant(/_test$/.test(databaseName), 'PostgreSQL database is not a test database');
  invariant(instanceNonceSha256 === expected.instanceNonceSha256, 'Live PostgreSQL nonce mismatch');
  invariant(SHA64.test(endpointSha256), 'PostgreSQL endpoint observation is invalid');
  return { ...expected, endpointSha256, serverVersionNum: numericVersion, databaseName };
}

function assertPostgresqlObservation(value, expectedInputs) {
  rejectSecrets(value, '$.postgresqlObservation');
  assertExactKeys(
    value,
    [
      'kind',
      'identity',
      'attestationSha256',
      'instanceNonceSha256',
      'endpointSha256',
      'serverVersionNum',
      'databaseName',
    ],
    '$.postgresqlObservation',
  );
  invariant(value.kind === expectedInputs.postgresql.kind, 'PostgreSQL identity kind mismatch');
  invariant(
    value.identity === expectedInputs.postgresql.identity,
    'PostgreSQL immutable identity mismatch',
  );
  invariant(
    expectedInputs.postgresql.policy === 'postgresql-16-test-closed-transport-v1',
    'PostgreSQL portable policy mismatch',
  );
  invariant(SHA64.test(value.attestationSha256), 'PostgreSQL attestation hash is invalid');
  invariant(SHA64.test(value.instanceNonceSha256), 'PostgreSQL nonce is invalid');
  invariant(SHA64.test(value.endpointSha256), 'PostgreSQL endpoint observation is invalid');
  invariant(
    Number.isInteger(value.serverVersionNum) &&
      value.serverVersionNum >= 160000 &&
      value.serverVersionNum < 170000,
    'PostgreSQL major is not 16',
  );
  invariant(
    typeof value.databaseName === 'string' && /_test$/.test(value.databaseName),
    'PostgreSQL test database is invalid',
  );
}

export function assertAggregate(value, expectedRunnerRole = 'local') {
  rejectSecrets(value, '$.aggregate');
  const aggregateKeys = [
    'schemaVersion',
    'status',
    'integratedSha',
    'retryAttempt',
    'contractSha256',
    'inputFingerprintSha256',
    'inputs',
    'platformObservationFingerprintSha256',
    'platformObservation',
    'testCount',
    'failureCount',
    'unexplainedSkipCount',
    'jobs',
  ];
  assertExactKeys(
    value,
    Object.hasOwn(value, 'postgresqlObservation')
      ? [...aggregateKeys, 'postgresqlObservation']
      : aggregateKeys,
    '$.aggregate',
  );
  invariant(value.schemaVersion === 1 && value.status === 'passed', 'Aggregate did not pass');
  invariant(value.retryAttempt === 1, 'Aggregate retry is prohibited');
  invariant(value.contractSha256 === contractSha256, 'Aggregate contract mismatch');
  invariant(
    Array.isArray(value.jobs) && value.jobs.length === contract.jobs.length,
    'Aggregate job count mismatch',
  );
  value.jobs.forEach((record, index) => assertRecord(record, contract.jobs[index].name));
  invariant(
    value.testCount === value.jobs.reduce((total, record) => total + record.testCount, 0),
    'Aggregate test count mismatch',
  );
  invariant(
    value.failureCount === 0 && value.unexplainedSkipCount === 0,
    'Aggregate has failures or unexplained skips',
  );
  invariant(
    value.jobs.every((record) => record.integratedSha === value.integratedSha),
    'Aggregate SHA mismatch',
  );
  invariant(
    value.jobs.every((record) => record.inputFingerprintSha256 === value.inputFingerprintSha256),
    'Aggregate input mismatch',
  );
  invariant(
    value.inputFingerprintSha256 === sha256(stableJson(value.inputs)),
    'Aggregate input fingerprint is not self-bound',
  );
  invariant(
    value.jobs.every((record) => stableJson(record.inputs) === stableJson(value.inputs)),
    'Aggregate complete inputs mismatch',
  );
  validateSchema(inputsSchema, value.inputs, '$.aggregate.inputs');
  invariant(
    value.inputs.runnerClasses.local !== value.inputs.runnerClasses.ci,
    'Aggregate runner classes must be distinct',
  );
  invariant(
    value.platformObservationFingerprintSha256 === sha256(stableJson(value.platformObservation)),
    'Aggregate platform fingerprint is not self-bound',
  );
  validateSchema(
    platformObservationSchema,
    value.platformObservation,
    '$.aggregate.platformObservation',
  );
  invariant(
    value.platformObservation.runnerRole === expectedRunnerRole &&
      value.platformObservation.runnerClass === value.inputs.runnerClasses[expectedRunnerRole],
    'Aggregate runner role/class mismatch',
  );
  if (Object.hasOwn(value, 'postgresqlObservation')) {
    assertPostgresqlObservation(value.postgresqlObservation, value.inputs);
    const postgresqlJob = value.jobs.find((record) => record.job === 'postgresql-16');
    invariant(
      postgresqlJob?.artifacts?.postgresqlIdentity?.sha256 ===
        sha256(stableJson(value.postgresqlObservation)),
      'Aggregate PostgreSQL observation is not bound to its canonical job artifact',
    );
  }
  invariant(
    value.jobs.every(
      (record) =>
        record.platformObservation.runnerRole === expectedRunnerRole &&
        record.platformObservation.runnerClass === value.inputs.runnerClasses[expectedRunnerRole],
    ),
    'Aggregate contains a job from the wrong runner role/class',
  );
}

const fileReferenceSchema = {
  type: 'object',
  additionalProperties: false,
  required: ['path', 'sha256'],
  properties: {
    path: { type: 'string', pattern: '^[A-Za-z0-9][A-Za-z0-9._-]*$' },
    sha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
  },
};

const platformObservationSchema = {
  type: 'object',
  additionalProperties: false,
  required: [
    'os',
    'arch',
    'osRelease',
    'runnerRole',
    'runnerClass',
    'zlib',
    'chromiumExecutableSha256',
  ],
  properties: {
    os: { type: 'string', minLength: 1 },
    arch: { type: 'string', minLength: 1 },
    osRelease: { type: 'string', minLength: 1 },
    runnerRole: { enum: ['local', 'ci'] },
    runnerClass: { type: 'string', minLength: 1 },
    zlib: { type: 'string', minLength: 1 },
    chromiumExecutableSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
  },
};

const inputsSchema = {
  type: 'object',
  additionalProperties: false,
  required: [
    'contractSha256',
    'runtime',
    'lockfileSha256',
    'postgresql',
    'playwright',
    'bundleAlgorithms',
    'runnerClasses',
  ],
  properties: {
    contractSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
    runtime: {
      type: 'object',
      additionalProperties: false,
      required: ['php', 'composer', 'node', 'npm'],
      properties: Object.fromEntries(
        ['php', 'composer', 'node', 'npm'].map((name) => [name, { type: 'string', minLength: 1 }]),
      ),
    },
    lockfileSha256: {
      type: 'object',
      additionalProperties: false,
      required: ['composer', 'npm'],
      properties: {
        composer: { type: 'string', pattern: '^[0-9a-f]{64}$' },
        npm: { type: 'string', pattern: '^[0-9a-f]{64}$' },
      },
    },
    postgresql: {
      type: 'object',
      additionalProperties: false,
      required: ['kind', 'identity', 'policy'],
      properties: {
        kind: { enum: ['oci', 'external-attestation'] },
        identity: { type: 'string', minLength: 64 },
        policy: { const: 'postgresql-16-test-closed-transport-v1' },
      },
    },
    playwright: {
      type: 'object',
      additionalProperties: false,
      required: ['packageVersion', 'chromiumRevision'],
      properties: {
        packageVersion: { type: 'string', minLength: 1 },
        chromiumRevision: { type: 'string', pattern: '^\\d+$' },
      },
    },
    bundleAlgorithms: {
      type: 'object',
      additionalProperties: false,
      required: ['assetSelection', 'gzip'],
      properties: {
        assetSelection: { const: 'html-module-entry-and-modulepreload-v1' },
        gzip: { const: 'node-zlib-level-9' },
      },
    },
    runnerClasses: {
      type: 'object',
      additionalProperties: false,
      required: ['local', 'ci'],
      properties: {
        local: { type: 'string', pattern: '^[A-Za-z0-9][A-Za-z0-9._-]{0,63}$' },
        ci: { type: 'string', pattern: '^[A-Za-z0-9][A-Za-z0-9._-]{0,63}$' },
      },
    },
  },
};

export const evidenceSchema = {
  $schema: 'https://json-schema.org/draft/2020-12/schema',
  $id: 'dorzak-p00-evidence-manifest-v1',
  type: 'object',
  additionalProperties: false,
  required: [
    'schemaVersion',
    'BASE_SHA',
    'CODE_SHA',
    'INTEGRATED_SHA',
    'contractSha256',
    'inputs',
    'postgresqlObservation',
    'counts',
    'local',
    'bundle',
    'review',
    'ciRuns',
    'files',
  ],
  properties: {
    schemaVersion: { const: 1 },
    BASE_SHA: { type: 'string', pattern: '^[0-9a-f]{40}$' },
    CODE_SHA: { type: 'string', pattern: '^[0-9a-f]{40}$' },
    INTEGRATED_SHA: { type: 'string', pattern: '^[0-9a-f]{40}$' },
    contractSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
    inputs: inputsSchema,
    postgresqlObservation: {
      type: 'object',
      additionalProperties: false,
      required: [
        'kind',
        'identity',
        'attestationSha256',
        'instanceNonceSha256',
        'endpointSha256',
        'serverVersionNum',
        'databaseName',
      ],
      properties: {
        kind: { enum: ['oci', 'external-attestation'] },
        identity: { type: 'string', minLength: 64 },
        attestationSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
        instanceNonceSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
        endpointSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
        serverVersionNum: { type: 'integer', minimum: 160000, maximum: 169999 },
        databaseName: { type: 'string', pattern: '_test$' },
      },
    },
    counts: {
      type: 'object',
      additionalProperties: false,
      required: ['source', 'jobs'],
      properties: {
        source: { const: 'scripts/quality/p00-contract.json' },
        jobs: {
          type: 'object',
          additionalProperties: false,
          required: [
            'composer-validation',
            'php-style-static',
            'sqlite',
            'postgresql-16',
            'frontend',
            'playwright',
          ],
          properties: Object.fromEntries(
            contract.jobs.map((job) => [
              job.name,
              { type: 'integer', minimum: 0, maximum: job.testCount },
            ]),
          ),
        },
      },
    },
    local: {
      type: 'object',
      additionalProperties: false,
      required: [
        'path',
        'sha256',
        'status',
        'integratedSha',
        'inputFingerprintSha256',
        'platformObservationFingerprintSha256',
        'platformObservation',
      ],
      properties: {
        ...fileReferenceSchema.properties,
        status: { const: 'passed' },
        integratedSha: { type: 'string', pattern: '^[0-9a-f]{40}$' },
        inputFingerprintSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
        platformObservationFingerprintSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
        platformObservation: platformObservationSchema,
      },
    },
    bundle: {
      type: 'object',
      additionalProperties: false,
      required: ['path', 'sha256', 'gzipBytes', 'limitBytes', 'debtStatus', 'warningSha256'],
      properties: {
        ...fileReferenceSchema.properties,
        gzipBytes: { type: 'integer', minimum: 0, maximum: contract.bundle.initialGzipLimitBytes },
        limitBytes: { const: contract.bundle.initialGzipLimitBytes },
        debtStatus: { const: 'accepted-open' },
        warningSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
      },
    },
    review: {
      type: 'object',
      additionalProperties: false,
      required: ['path', 'sha256', 'baseSha', 'codeSha', 'critical', 'important', 'minor'],
      properties: {
        ...fileReferenceSchema.properties,
        baseSha: { type: 'string', pattern: '^[0-9a-f]{40}$' },
        codeSha: { type: 'string', pattern: '^[0-9a-f]{40}$' },
        critical: { const: 0 },
        important: { const: 0 },
        minor: { type: 'array', items: { type: 'string' } },
      },
    },
    ciRuns: {
      type: 'array',
      minItems: 2,
      maxItems: 2,
      items: {
        type: 'object',
        additionalProperties: false,
        required: [
          'path',
          'sha256',
          'provider',
          'runId',
          'attempt',
          'integratedSha',
          'inputFingerprintSha256',
          'platformObservationFingerprintSha256',
          'platformObservation',
        ],
        properties: {
          ...fileReferenceSchema.properties,
          provider: { type: 'string', minLength: 1 },
          runId: { type: 'string', minLength: 1 },
          attempt: { const: 1 },
          integratedSha: { type: 'string', pattern: '^[0-9a-f]{40}$' },
          inputFingerprintSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
          platformObservationFingerprintSha256: { type: 'string', pattern: '^[0-9a-f]{64}$' },
          platformObservation: platformObservationSchema,
        },
      },
    },
    files: {
      type: 'array',
      minItems: 7,
      maxItems: 7,
      uniqueItems: true,
      items: fileReferenceSchema,
    },
  },
};

export function assertExactKeys(value, keys, path) {
  invariant(
    value !== null && typeof value === 'object' && !Array.isArray(value),
    path + ' must be an object',
  );
  invariant(
    stableJson(Object.keys(value).sort()) === stableJson([...keys].sort()),
    path + ' keys are not closed',
  );
}

export function validateSchema(schema, value, path = '$') {
  if ('const' in schema) invariant(Object.is(value, schema.const), path + ' const mismatch');
  if (schema.enum) invariant(schema.enum.includes(value), path + ' enum mismatch');
  if (schema.type === 'object') {
    invariant(
      value !== null && typeof value === 'object' && !Array.isArray(value),
      path + ' type mismatch',
    );
    for (const key of schema.required || [])
      invariant(Object.hasOwn(value, key), path + '.' + key + ' is required');
    if (schema.additionalProperties === false) {
      invariant(
        Object.keys(value).every((key) => Object.hasOwn(schema.properties || {}, key)),
        path + ' has extra keys',
      );
    }
    for (const [key, item] of Object.entries(value)) {
      if (schema.properties?.[key]) validateSchema(schema.properties[key], item, path + '.' + key);
      else if (schema.additionalProperties && typeof schema.additionalProperties === 'object') {
        validateSchema(schema.additionalProperties, item, path + '.' + key);
      }
    }
  } else if (schema.type === 'array') {
    invariant(Array.isArray(value), path + ' type mismatch');
    if (schema.minItems !== undefined)
      invariant(value.length >= schema.minItems, path + ' too short');
    if (schema.maxItems !== undefined)
      invariant(value.length <= schema.maxItems, path + ' too long');
    if (schema.uniqueItems)
      invariant(new Set(value.map(stableJson)).size === value.length, path + ' duplicates');
    value.forEach((item, index) => validateSchema(schema.items, item, path + '[' + index + ']'));
  } else if (schema.type === 'string') {
    invariant(typeof value === 'string', path + ' type mismatch');
    if (schema.minLength !== undefined)
      invariant(value.length >= schema.minLength, path + ' too short');
    if (schema.pattern)
      invariant(new RegExp(schema.pattern).test(value), path + ' pattern mismatch');
  } else if (schema.type === 'integer') {
    invariant(Number.isInteger(value), path + ' type mismatch');
    if (schema.minimum !== undefined) invariant(value >= schema.minimum, path + ' below minimum');
    if (schema.maximum !== undefined) invariant(value <= schema.maximum, path + ' above maximum');
  }
}

function validateCiRun(run, aggregate) {
  rejectSecrets(run, '$.ciRun');
  assertExactKeys(
    run,
    [
      'schemaVersion',
      'provider',
      'runId',
      'attempt',
      'integratedSha',
      'contractSha256',
      'inputFingerprintSha256',
      'inputs',
      'platformObservationFingerprintSha256',
      'platformObservation',
      'postgresqlObservation',
      'requiredGate',
      'jobs',
    ],
    '$.ciRun',
  );
  assertExactKeys(run.requiredGate, ['status', 'jobs'], '$.ciRun.requiredGate');
  invariant(run.schemaVersion === 1, 'CI schema mismatch');
  invariant(typeof run.provider === 'string' && run.provider.length > 0, 'CI provider is missing');
  invariant(typeof run.runId === 'string' && run.runId.length > 0, 'CI run ID is missing');
  invariant(run.attempt === 1, 'CI retry is prohibited');
  invariant(run.integratedSha === aggregate.integratedSha, 'CI SHA mismatch');
  invariant(run.contractSha256 === contractSha256, 'CI contract mismatch');
  invariant(run.inputFingerprintSha256 === aggregate.inputFingerprintSha256, 'CI input mismatch');
  invariant(
    run.inputFingerprintSha256 === sha256(stableJson(run.inputs)),
    'CI input fingerprint is not self-bound',
  );
  invariant(
    stableJson(run.inputs) === stableJson(aggregate.inputs),
    'CI complete portable inputs differ from aggregate',
  );
  validateSchema(inputsSchema, run.inputs, '$.ciRun.inputs');
  invariant(
    run.platformObservationFingerprintSha256 === sha256(stableJson(run.platformObservation)),
    'CI platform fingerprint mismatch',
  );
  validateSchema(platformObservationSchema, run.platformObservation, '$.ciRun.platformObservation');
  invariant(
    run.platformObservation.runnerRole === 'ci' &&
      run.platformObservation.runnerClass === run.inputs.runnerClasses.ci,
    'CI runner role/class mismatch',
  );
  invariant(
    run.requiredGate?.status === 'passed' && run.requiredGate?.jobs === 6,
    'CI required gate failed',
  );
  assertPostgresqlObservation(run.postgresqlObservation, run.inputs);
  invariant(Array.isArray(run.jobs) && run.jobs.length === 6, 'CI jobs missing');
  run.jobs.forEach((record, index) => assertRecord(record, contract.jobs[index].name));
  const postgresqlJob = run.jobs.find((record) => record.job === 'postgresql-16');
  invariant(
    postgresqlJob?.artifacts?.postgresqlIdentity?.sha256 ===
      sha256(stableJson(run.postgresqlObservation)),
    'CI PostgreSQL observation is not bound to its canonical job artifact',
  );
  invariant(
    run.jobs.every(
      (record) =>
        record.integratedSha === run.integratedSha &&
        record.integratedSha === aggregate.integratedSha &&
        record.inputFingerprintSha256 === run.inputFingerprintSha256 &&
        stableJson(record.inputs) === stableJson(run.inputs) &&
        record.platformObservation.runnerRole === 'ci' &&
        record.platformObservation.runnerClass === run.inputs.runnerClasses.ci,
    ),
    'CI jobs do not cross-bind SHA, complete portable inputs, and the approved CI runner class',
  );
}

function safeSibling(directory, path) {
  invariant(
    typeof path === 'string' && /^[A-Za-z0-9][A-Za-z0-9._-]*$/.test(path),
    'Unsafe evidence path',
  );
  const full = resolve(directory, path);
  invariant(dirname(full) === resolve(directory), 'Evidence path escapes output');
  return full;
}

function readSanitizedJson(path, label) {
  const value = readJson(path);
  rejectSecrets(value, label);
  return value;
}

function readSanitizedText(path, label) {
  const value = readFileSync(path, 'utf8');
  rejectSecrets(value, label);
  return value;
}

function normalizedReviewMarkdown(review) {
  return [
    '# Independent P00 Review',
    '',
    'BASE_SHA: ' + review.baseSha,
    'CODE_SHA: ' + review.codeSha,
    'Critical: ' + review.critical,
    'Important: ' + review.important,
    '',
    '## Minor',
    '',
    ...(review.minor.length > 0 ? review.minor.map((finding) => '- ' + finding) : ['- None.']),
    '',
  ].join('\n');
}

function reviewFromMarkdown(markdown) {
  const lines = markdown.split('\n');
  invariant(
    lines[0] === '# Independent P00 Review' &&
      lines[1] === '' &&
      lines[6] === '' &&
      lines[7] === '## Minor' &&
      lines[8] === '' &&
      lines.at(-1) === '',
    'Independent review Markdown shape changed',
  );
  const field = (index, prefix) => {
    invariant(lines[index].startsWith(prefix), 'Independent review field is missing: ' + prefix);
    return lines[index].slice(prefix.length);
  };
  const minorLines = lines.slice(9, -1);
  invariant(
    minorLines.length > 0 && minorLines.every((line) => line.startsWith('- ')),
    'Independent review minor section is invalid',
  );
  const minor =
    minorLines.length === 1 && minorLines[0] === '- None.'
      ? []
      : minorLines.map((line) => line.slice(2));
  const review = {
    baseSha: field(2, 'BASE_SHA: '),
    codeSha: field(3, 'CODE_SHA: '),
    critical: Number(field(4, 'Critical: ')),
    important: Number(field(5, 'Important: ')),
    minor,
  };
  invariant(
    markdown === normalizedReviewMarkdown(review),
    'Independent review Markdown is not canonical',
  );
  return review;
}

export function buildEvidence({
  outputDirectory,
  baseSha,
  codeSha,
  integratedSha,
  localDirectory,
  ciRunPaths,
  reviewJsonPath,
  reviewMarkdownPath,
  faultPoint = null,
}) {
  invariant(
    SHA40.test(baseSha) && SHA40.test(codeSha) && SHA40.test(integratedSha),
    'Evidence SHA is invalid',
  );
  invariant(codeSha === integratedSha, 'P00 evidence requires CODE_SHA equal INTEGRATED_SHA');
  invariant(
    Array.isArray(ciRunPaths) && ciRunPaths.length === 2,
    'Exactly two CI runs are required',
  );
  invariant(
    [null, 'after-temp', 'after-rename'].includes(faultPoint),
    'Unknown evidence fault point',
  );
  const localAggregate = aggregateRequiredGates(localDirectory);
  assertAggregate(localAggregate);
  invariant(localAggregate.integratedSha === integratedSha, 'Local matrix SHA mismatch');
  const observation = readSanitizedJson(
    join(localDirectory, 'postgresql-16', 'postgresql-identity.json'),
    '$.local.postgresqlObservation',
  );
  assertPostgresqlObservation(observation, localAggregate.inputs);
  const localPostgresqlReference = localAggregate.jobs.find(
    (record) => record.job === 'postgresql-16',
  )?.artifacts?.postgresqlIdentity;
  invariant(
    localPostgresqlReference?.sha256 === sha256(stableJson(observation)),
    'Local PostgreSQL observation is not bound to its canonical job artifact',
  );
  const bundle = readSanitizedJson(
    join(localDirectory, 'frontend', 'bundle.json'),
    '$.local.bundle',
  );
  assertBundle(bundle);
  invariant(
    bundle.nodeVersion === localAggregate.inputs.runtime.node &&
      bundle.zlibVersion === localAggregate.platformObservation.zlib,
    'Bundle runtime identity mismatch',
  );
  const review = readSanitizedJson(reviewJsonPath, '$.reviewJson');
  assertExactKeys(
    review,
    ['schemaVersion', 'baseSha', 'codeSha', 'critical', 'important', 'minor'],
    '$.reviewJson',
  );
  invariant(
    review.schemaVersion === 1 && review.baseSha === baseSha && review.codeSha === codeSha,
    'Review range mismatch',
  );
  invariant(
    review.critical === 0 && review.important === 0 && Array.isArray(review.minor),
    'Review is not acceptable',
  );
  invariant(
    review.minor.every((finding) => typeof finding === 'string' && finding.length > 0),
    'Review minor finding is invalid',
  );
  const reviewMarkdown = readSanitizedText(reviewMarkdownPath, '$.reviewMarkdown');
  invariant(
    reviewMarkdown === normalizedReviewMarkdown(review),
    'Review Markdown does not normalize the JSON verdict',
  );
  const runs = ciRunPaths.map((path, index) => readSanitizedJson(path, '$.ciRun[' + index + ']'));
  runs.forEach((run) => validateCiRun(run, localAggregate));
  invariant(runs[0].runId !== runs[1].runId, 'CI run IDs must be distinct');

  const canonicalDirectory = resolve(outputDirectory);
  invariant(!existsSync(canonicalDirectory), 'Evidence output directory already exists');
  mkdirSync(dirname(canonicalDirectory), { recursive: true });
  const publicationDirectory = mkdtempSync(
    join(dirname(canonicalDirectory), '.' + basename(canonicalDirectory) + '.tmp-'),
  );
  let published = false;
  try {
    const readme = [
      '# P00 Verification Evidence',
      '',
      'This directory contains deterministic, sanitized evidence for one exact P00 range.',
      'BASE_SHA is the approved integration base, CODE_SHA is the independently reviewed clean code identity,',
      'and INTEGRATED_SHA is the exact identity exercised locally and by both new attempt-one CI runs.',
      'The later evidence-only commit is deliberately absent from these payloads and is recorded by Control Room.',
      'Counts come only from scripts/quality/p00-contract.json. Raw logs remain ignored under .artifacts/p00.',
      'The Vite large-chunk warning is accepted-open measured debt, not a waived or hidden gate.',
      '',
    ].join('\n');
    const localPayload = { ...localAggregate, postgresqlObservation: observation };
    writeFileSync(join(publicationDirectory, 'README.md'), readme);
    writeJson(join(publicationDirectory, 'manifest.schema.json'), evidenceSchema);
    writeJson(join(publicationDirectory, 'local-full-matrix.json'), localPayload);
    writeJson(join(publicationDirectory, 'ci-run-1.json'), runs[0]);
    writeJson(join(publicationDirectory, 'ci-run-2.json'), runs[1]);
    writeJson(join(publicationDirectory, 'bundle.json'), bundle);
    copyFileSync(reviewMarkdownPath, join(publicationDirectory, 'independent-review.md'));

    const names = [
      'README.md',
      'manifest.schema.json',
      'local-full-matrix.json',
      'ci-run-1.json',
      'ci-run-2.json',
      'bundle.json',
      'independent-review.md',
    ];
    const files = names.map((path) => ({
      path,
      sha256: sha256(readFileSync(join(publicationDirectory, path))),
    }));
    const file = (path) => files.find((item) => item.path === path);
    const manifest = {
      schemaVersion: 1,
      BASE_SHA: baseSha,
      CODE_SHA: codeSha,
      INTEGRATED_SHA: integratedSha,
      contractSha256,
      inputs: localAggregate.inputs,
      postgresqlObservation: observation,
      counts: {
        source: 'scripts/quality/p00-contract.json',
        jobs: Object.fromEntries(contract.jobs.map((job) => [job.name, job.testCount])),
      },
      local: {
        ...file('local-full-matrix.json'),
        status: 'passed',
        integratedSha,
        inputFingerprintSha256: localAggregate.inputFingerprintSha256,
        platformObservationFingerprintSha256: localAggregate.platformObservationFingerprintSha256,
        platformObservation: localAggregate.platformObservation,
      },
      bundle: {
        ...file('bundle.json'),
        gzipBytes: bundle.gzipBytes,
        limitBytes: bundle.limitBytes,
        debtStatus: bundle.largeChunkDebt.status,
        warningSha256: bundle.largeChunkDebt.messageSha256,
      },
      review: {
        ...file('independent-review.md'),
        baseSha,
        codeSha,
        critical: review.critical,
        important: review.important,
        minor: review.minor,
      },
      ciRuns: runs.map((run, index) => ({
        ...file('ci-run-' + (index + 1) + '.json'),
        provider: run.provider,
        runId: run.runId,
        attempt: run.attempt,
        integratedSha: run.integratedSha,
        inputFingerprintSha256: run.inputFingerprintSha256,
        platformObservationFingerprintSha256: run.platformObservationFingerprintSha256,
        platformObservation: run.platformObservation,
      })),
      files,
    };
    writeJson(join(publicationDirectory, 'manifest.json'), manifest);
    if (faultPoint === 'after-temp') throw new Error('P00_TEST_FAULT_AFTER_TEMP');
    origins.set(manifest, publicationDirectory);
    validateEvidence(join(publicationDirectory, 'manifest.json'));
    renameSync(publicationDirectory, canonicalDirectory);
    published = true;
    if (faultPoint === 'after-rename') throw new Error('P00_TEST_FAULT_AFTER_RENAME');
    origins.set(manifest, canonicalDirectory);
    validateEvidence(join(canonicalDirectory, 'manifest.json'));
    return manifest;
  } catch (error) {
    if (published && existsSync(canonicalDirectory)) {
      rmSync(canonicalDirectory, { recursive: true, force: true });
    }
    throw error;
  } finally {
    if (existsSync(publicationDirectory)) {
      rmSync(publicationDirectory, { recursive: true, force: true });
    }
  }
}

export function validateEvidence(input) {
  const manifestPath = typeof input === 'string' ? resolve(input) : null;
  if (manifestPath) {
    const stat = lstatSync(manifestPath);
    invariant(
      stat.isFile() && !stat.isSymbolicLink() && realpathSync(manifestPath) === manifestPath,
      'Evidence manifest is not one canonical regular file',
    );
  }
  const manifest = manifestPath ? readSanitizedJson(manifestPath, '$.manifest') : input;
  const directory = manifestPath ? dirname(manifestPath) : origins.get(manifest);
  invariant(directory, 'Evidence directory is unknown');
  rejectSecrets(manifest);
  validateSchema(evidenceSchema, manifest);
  invariant(manifest.schemaVersion === 1, 'Evidence schema mismatch');
  invariant(
    SHA40.test(manifest.BASE_SHA) &&
      SHA40.test(manifest.CODE_SHA) &&
      SHA40.test(manifest.INTEGRATED_SHA),
    'Evidence SHA invalid',
  );
  invariant(
    manifest.CODE_SHA === manifest.INTEGRATED_SHA,
    'Evidence code/integrated identity mismatch',
  );
  invariant(
    manifest.contractSha256 === contractSha256 && manifest.inputs.contractSha256 === contractSha256,
    'Evidence contract mismatch',
  );
  invariant(
    manifest.counts.source === 'scripts/quality/p00-contract.json',
    'Count provenance mismatch',
  );
  const expectedCounts = Object.fromEntries(contract.jobs.map((job) => [job.name, job.testCount]));
  invariant(
    stableJson(manifest.counts.jobs) === stableJson(expectedCounts),
    'Evidence counts changed',
  );
  invariant(
    Array.isArray(manifest.files) && manifest.files.length === 7,
    'Evidence must reference seven siblings',
  );
  const expectedNames = [
    'README.md',
    'manifest.schema.json',
    'local-full-matrix.json',
    'ci-run-1.json',
    'ci-run-2.json',
    'bundle.json',
    'independent-review.md',
  ];
  const directoryEntries = readdirSync(directory).sort();
  invariant(
    stableJson(directoryEntries) === stableJson([...expectedNames, 'manifest.json'].sort()),
    'Evidence directory must contain exactly eight canonical paths',
  );
  invariant(
    stableJson(manifest.files.map((item) => item.path)) === stableJson(expectedNames),
    'Evidence file list mismatch',
  );
  for (const reference of manifest.files) {
    invariant(SHA64.test(reference.sha256), 'Evidence file hash invalid');
    const path = safeSibling(directory, reference.path);
    const stat = lstatSync(path);
    invariant(
      stat.isFile() && !stat.isSymbolicLink() && realpathSync(path) === path,
      'Evidence sibling is not one canonical regular file: ' + reference.path,
    );
    invariant(
      sha256(readFileSync(path)) === reference.sha256,
      'Evidence file hash mismatch: ' + reference.path,
    );
  }
  const referenceFor = (path) => manifest.files.find((item) => item.path === path);
  for (const [summary, path] of [
    [manifest.local, 'local-full-matrix.json'],
    [manifest.bundle, 'bundle.json'],
    [manifest.review, 'independent-review.md'],
    [manifest.ciRuns[0], 'ci-run-1.json'],
    [manifest.ciRuns[1], 'ci-run-2.json'],
  ])
    invariant(
      summary.path === path && summary.sha256 === referenceFor(path).sha256,
      'Evidence summary/reference cross-binding mismatch: ' + path,
    );
  rejectSecrets(readSanitizedText(join(directory, 'README.md'), '$.README.md'), '$.README.md');
  invariant(
    stableJson(readSanitizedJson(join(directory, 'manifest.schema.json'), '$.manifestSchema')) ===
      stableJson(evidenceSchema),
    'Evidence schema artifact changed',
  );
  const local = readSanitizedJson(join(directory, 'local-full-matrix.json'), '$.local');
  assertAggregate(local, 'local');
  invariant(local.integratedSha === manifest.INTEGRATED_SHA, 'Local evidence SHA mismatch');
  invariant(
    local.inputFingerprintSha256 === manifest.local.inputFingerprintSha256,
    'Local evidence input mismatch',
  );
  invariant(stableJson(local.inputs) === stableJson(manifest.inputs), 'Local inputs mismatch');
  assertPostgresqlObservation(local.postgresqlObservation, manifest.inputs);
  invariant(
    stableJson(local.postgresqlObservation) === stableJson(manifest.postgresqlObservation),
    'Local PostgreSQL observation mismatch',
  );
  const bundle = readSanitizedJson(join(directory, 'bundle.json'), '$.bundle');
  assertBundle(bundle);
  invariant(
    bundle.nodeVersion === manifest.inputs.runtime.node &&
      bundle.zlibVersion === local.platformObservation.zlib,
    'Bundle evidence runtime mismatch',
  );
  invariant(bundle.gzipBytes === manifest.bundle.gzipBytes, 'Bundle measurement mismatch');
  const runs = [
    readSanitizedJson(join(directory, 'ci-run-1.json'), '$.ciRun[0]'),
    readSanitizedJson(join(directory, 'ci-run-2.json'), '$.ciRun[1]'),
  ];
  runs.forEach((run) => validateCiRun(run, local));
  invariant(runs[0].runId !== runs[1].runId, 'CI run IDs are not distinct');
  invariant(
    runs.every((run) => run.integratedSha === manifest.INTEGRATED_SHA),
    'CI integrated SHA mismatch',
  );
  invariant(
    manifest.review.baseSha === manifest.BASE_SHA && manifest.review.codeSha === manifest.CODE_SHA,
    'Review range mismatch',
  );
  invariant(
    manifest.review.critical === 0 && manifest.review.important === 0,
    'Review has blocking findings',
  );
  const reviewMarkdown = readSanitizedText(
    join(directory, 'independent-review.md'),
    '$.independent-review.md',
  );
  const parsedReview = reviewFromMarkdown(reviewMarkdown);
  const expectedLocalSummary = {
    ...referenceFor('local-full-matrix.json'),
    status: local.status,
    integratedSha: local.integratedSha,
    inputFingerprintSha256: local.inputFingerprintSha256,
    platformObservationFingerprintSha256: local.platformObservationFingerprintSha256,
    platformObservation: local.platformObservation,
  };
  const expectedBundleSummary = {
    ...referenceFor('bundle.json'),
    gzipBytes: bundle.gzipBytes,
    limitBytes: bundle.limitBytes,
    debtStatus: bundle.largeChunkDebt.status,
    warningSha256: bundle.largeChunkDebt.messageSha256,
  };
  const expectedReviewSummary = {
    ...referenceFor('independent-review.md'),
    ...parsedReview,
  };
  const expectedCiSummaries = runs.map((run, index) => ({
    ...referenceFor('ci-run-' + (index + 1) + '.json'),
    provider: run.provider,
    runId: run.runId,
    attempt: run.attempt,
    integratedSha: run.integratedSha,
    inputFingerprintSha256: run.inputFingerprintSha256,
    platformObservationFingerprintSha256: run.platformObservationFingerprintSha256,
    platformObservation: run.platformObservation,
  }));
  invariant(
    stableJson(manifest.local) === stableJson(expectedLocalSummary),
    'Local manifest summary differs from its sibling',
  );
  invariant(
    stableJson(manifest.bundle) === stableJson(expectedBundleSummary),
    'Bundle manifest summary differs from its sibling',
  );
  invariant(
    stableJson(manifest.review) === stableJson(expectedReviewSummary),
    'Review manifest summary differs from its sibling',
  );
  invariant(
    stableJson(manifest.ciRuns) === stableJson(expectedCiSummaries),
    'CI manifest summaries differ from their siblings',
  );
  return { files: manifest.files };
}

function print(value) {
  process.stdout.write(value + '\n');
}

async function main(argv) {
  const [action, ...args] = argv;
  if (action === 'bundle') {
    invariant(args.length === 1 || args.length === 2, 'Usage: p00.mjs bundle <dist> [build-log]');
    const value = measureBundle(
      resolve(args[0]),
      contract.bundle.initialGzipLimitBytes,
      args[1] ? resolve(args[1]) : null,
    );
    print(
      'P00_BUNDLE PASS gzip=' +
        value.gzipBytes +
        ' limit=' +
        value.limitBytes +
        ' node=' +
        value.nodeVersion +
        ' zlib=' +
        value.zlibVersion,
    );
    return;
  }
  if (action === 'write-result') {
    writeGateResult(args[0], Number(args[1]), Number(args[2]), resolve(args[3]));
    print('P00_JOB PASS job=' + args[0]);
    return;
  }
  if (action === 'postgres-identity') {
    const value = postgresqlObservation(args[0], args[1], args[2], args[3]);
    writeJson(join(JOB_ARTIFACTS(), 'postgresql-identity.json'), value);
    print(
      'P00_POSTGRESQL PASS major=16 database=' +
        value.databaseName +
        ' identity_sha256=' +
        sha256(value.identity),
    );
    return;
  }
  if (action === 'aggregate') {
    const directory = resolve(args[0]);
    const value = aggregateRequiredGates(directory);
    assertAggregate(value, requiredEnvironment('P00_RUNNER_ROLE'));
    writeJson(directory + '.required-gates.json', value);
    print('P00_REQUIRED_GATES PASS jobs=6 sha=' + value.integratedSha);
    return;
  }
  if (action === 'build-evidence') {
    const value = buildEvidence({
      outputDirectory: resolve(args[0]),
      baseSha: args[1],
      codeSha: args[2],
      integratedSha: args[3],
      localDirectory: resolve(args[4]),
      ciRunPaths: [resolve(args[5]), resolve(args[6])],
      reviewJsonPath: resolve(args[7]),
      reviewMarkdownPath: resolve(args[8]),
    });
    print('P00_EVIDENCE_BUILT files=' + value.files.length);
    return;
  }
  if (action === 'validate-evidence') {
    const value = validateEvidence(resolve(args[0]));
    const manifest = readJson(resolve(args[0]));
    print(
      'P00_EVIDENCE PASS base=' +
        manifest.BASE_SHA +
        ' code=' +
        manifest.CODE_SHA +
        ' integrated=' +
        manifest.INTEGRATED_SHA +
        ' runs=2 files=' +
        value.files.length,
    );
    return;
  }
  throw new Error(
    'Usage: p00.mjs bundle <dist> [build-log]|write-result|postgres-identity|aggregate|build-evidence|validate-evidence',
  );
}

if (process.argv[1] && resolve(process.argv[1]) === MODULE) {
  main(process.argv.slice(2)).catch((error) => {
    process.stderr.write('P00_FAIL ' + error.message + '\n');
    process.exitCode = 1;
  });
}
