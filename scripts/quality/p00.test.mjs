import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { randomBytes } from 'node:crypto';
import {
  existsSync,
  mkdtempSync,
  mkdirSync,
  readFileSync,
  readdirSync,
  realpathSync,
  symlinkSync,
  unlinkSync,
  writeFileSync,
} from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, join, resolve } from 'node:path';
import test from 'node:test';
import { fileURLToPath } from 'node:url';
import * as p00 from './p00.mjs';
import {
  aggregateRequiredGates,
  assertAggregate,
  assertExactKeys,
  buildEvidence,
  contract,
  contractSha256,
  measureBundle,
  sha256,
  stableJson,
  validateSchema,
  validateEvidence,
} from './p00.mjs';

const sha40 = '0123456789abcdef0123456789abcdef01234567';
const sha64 = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';
const repositoryRoot = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const temp = (name) => realpathSync(mkdtempSync(join(tmpdir(), 'dorzak-p00-' + name + '-')));
const json = (path, value) => writeFileSync(path, stableJson(value));
const staticAnalysisDebt = {
  status: 'accepted-versioned-non-increasing',
  baselinePath: 'backend/phpstan-baseline.neon',
  baselineSha256: 'bce2bb249cd8e113909bd12ba6f159ebde50ab657b702ab61ae7768ddcc1bbc5',
  baselineCountDirectives: 312,
  baselineDiagnosticCount: 384,
  historicalRedDiagnosticCount: 384,
  historicalRedOutputSha256: '4f810b0161e0355546d84d5d58bcf3bcbfd5bcacc0ee4857d43b30dc64029a2d',
  larastanVersion: '3.10.0',
  phpstanVersion: '2.2.5',
  phpVersion: '8.5.6',
};
const inputs = () => ({
  contractSha256,
  runtime: { php: '8.5.1', composer: '2.8.0', node: process.versions.node, npm: '10.9.0' },
  lockfileSha256: { composer: sha64, npm: sha64 },
  postgresql: {
    kind: 'oci',
    identity: 'registry.test/postgres@sha256:' + sha64,
    policy: 'postgresql-16-test-closed-transport-v1',
  },
  playwright: { packageVersion: '1.57.0', chromiumRevision: '1234567' },
  bundleAlgorithms: {
    assetSelection: 'html-module-entry-and-modulepreload-v1',
    gzip: 'node-zlib-level-9',
  },
  runnerClasses: { local: 'local-linux-x64', ci: 'ci-linux-x64' },
  staticAnalysisDebt: structuredClone(staticAnalysisDebt),
});
const platformObservation = (runnerRole = 'local') => ({
  os: 'linux',
  arch: 'x64',
  osRelease: 'test-kernel',
  runnerRole,
  runnerClass: inputs().runnerClasses[runnerRole],
  zlib: process.versions.zlib,
  chromiumExecutableSha256: sha64,
});
const record = (job, root, runnerRole = 'local') => {
  const declared = inputs();
  const artifactNames = {
    'composer-validation': ['dispatcherTap', 'p00NodeTap'],
    'php-style-static': [],
    sqlite: ['junit'],
    'postgresql-16': ['junit', 'postgresqlIdentity'],
    frontend: ['vitest', 'bundle', 'viteBuildLog'],
    playwright: ['playwrightJson'],
  };
  const artifactPaths = {
    dispatcherTap: 'dispatcher.tap',
    p00NodeTap: 'p00-node.tap',
    junit: job.name + '.junit.xml',
    postgresqlIdentity: 'postgresql-identity.json',
    vitest: 'vitest.json',
    bundle: 'bundle.json',
    viteBuildLog: 'vite-build.log',
    playwrightJson: 'playwright.json',
  };
  const directory = join(root, job.name);
  mkdirSync(directory);
  const log = 'log for ' + job.name + '\n';
  writeFileSync(join(directory, 'job.log'), log);
  const artifacts = Object.fromEntries(
    artifactNames[job.name].map((name) => {
      const path = artifactPaths[name];
      const bytes =
        name === 'dispatcherTap'
          ? 'TAP version 13\nok 1 - list\nok 2 - invalid\n1..2\n# dispatcher reporter diagnostic\n'
          : name === 'p00NodeTap'
            ? 'TAP version 13\n' +
              Array.from({ length: 9 }, (_, index) => `ok ${index + 1} - node`).join('\n') +
              '\n1..9\n# node reporter diagnostic\n'
            : name === 'junit'
              ? `<testsuites><testsuite tests="${job.testCount}" failures="0" errors="0" skipped="0"></testsuite></testsuites>\n`
              : name === 'vitest'
                ? stableJson({ numTotalTests: 8, numFailedTests: 0, numPendingTests: 0 })
                : name === 'playwrightJson'
                  ? stableJson({ stats: { expected: 9, unexpected: 0, flaky: 0, skipped: 0 } })
                  : name === 'postgresqlIdentity'
                    ? stableJson({ placeholder: true })
                    : name === 'bundle'
                      ? stableJson({ placeholder: true })
                      : 'artifact ' + job.name + ' ' + name + '\n';
      writeFileSync(join(directory, path), bytes);
      return [name, { path, sha256: sha256(bytes) }];
    }),
  );
  return {
    schemaVersion: 1,
    job: job.name,
    integratedSha: sha40,
    status: 'passed',
    command: job.command,
    exitCode: 0,
    retryAttempt: 1,
    testCount: job.testCount,
    failureCount: 0,
    unexplainedSkipCount: 0,
    durationMs: 10,
    contractSha256,
    inputFingerprintSha256: sha256(stableJson(declared)),
    inputs: declared,
    logSha256: sha256(log),
    platformObservationFingerprintSha256: sha256(stableJson(platformObservation(runnerRole))),
    platformObservation: platformObservation(runnerRole),
    artifacts,
  };
};
const resultSet = (directory, mutate = () => {}) => {
  const records = contract.jobs.map((job) => record(job, directory));
  mutate(records);
  for (const value of records) json(join(directory, value.job, 'result.json'), value);
  return records;
};

const staticDebtFixture = (name) => {
  const root = temp('static-debt-' + name);
  mkdirSync(join(root, 'backend'));
  writeFileSync(
    join(root, staticAnalysisDebt.baselinePath),
    readFileSync(join(repositoryRoot, staticAnalysisDebt.baselinePath)),
  );
  json(join(root, 'backend/composer.lock'), {
    packages: [{ name: 'larastan/larastan', version: 'v3.10.0' }],
    'packages-dev': [{ name: 'phpstan/phpstan', version: '2.2.5' }],
  });
  return root;
};

test('contract freezes ordered jobs, counts, budget, and open Vite debt', () => {
  assert.deepEqual(
    contract.jobs.map((job) => [job.name, job.testCount]),
    [
      ['composer-validation', 11],
      ['php-style-static', 0],
      ['sqlite', 446],
      ['postgresql-16', 450],
      ['frontend', 8],
      ['playwright', 9],
    ],
  );
  assert.equal(contract.bundle.initialGzipLimitBytes, 216797);
  assert.equal(contract.bundle.debtStatus, 'accepted-open');
  assert.equal(contract.bundle.expectedOccurrences, 1);
  assert.deepEqual(contract.staticAnalysisDebt, staticAnalysisDebt);
  assert.deepEqual(p00.measureStaticAnalysisDebt(), staticAnalysisDebt);
  const validDebtRoot = staticDebtFixture('valid');
  assert.deepEqual(
    p00.measureStaticAnalysisDebt({ root: validDebtRoot, phpVersion: '8.5.6' }),
    staticAnalysisDebt,
  );
  const baselinePath = join(validDebtRoot, staticAnalysisDebt.baselinePath);
  const baselineBytes = readFileSync(baselinePath);
  writeFileSync(baselinePath, Buffer.concat([baselineBytes, Buffer.from('# tampered bytes\n')]));
  assert.throws(
    () => p00.measureStaticAnalysisDebt({ root: validDebtRoot, phpVersion: '8.5.6' }),
    /baseline/i,
  );
  writeFileSync(baselinePath, baselineBytes);
  writeFileSync(
    baselinePath,
    baselineBytes
      .toString('utf8')
      .replace(/count:\s+(\d+)/, (_, count) => `count: ${Number(count) + 1}`),
  );
  assert.throws(
    () => p00.measureStaticAnalysisDebt({ root: validDebtRoot, phpVersion: '8.5.6' }),
    /baseline/i,
  );
  writeFileSync(baselinePath, baselineBytes);
  const lockPath = join(validDebtRoot, 'backend/composer.lock');
  const lock = JSON.parse(readFileSync(lockPath, 'utf8'));
  lock.packages[0].version = 'v3.10.1';
  json(lockPath, lock);
  assert.throws(
    () => p00.measureStaticAnalysisDebt({ root: validDebtRoot, phpVersion: '8.5.6' }),
    /Larastan/i,
  );
  lock.packages[0].version = 'v3.10.0';
  lock['packages-dev'][0].version = '2.2.6';
  json(lockPath, lock);
  assert.throws(
    () => p00.measureStaticAnalysisDebt({ root: validDebtRoot, phpVersion: '8.5.6' }),
    /PHPStan/i,
  );
  lock['packages-dev'][0].version = '2.2.5';
  json(lockPath, lock);
  assert.throws(
    () => p00.measureStaticAnalysisDebt({ root: validDebtRoot, phpVersion: '8.5.7' }),
    /PHP version/i,
  );
  const linkedDebtRoot = staticDebtFixture('linked');
  unlinkSync(join(linkedDebtRoot, staticAnalysisDebt.baselinePath));
  symlinkSync(
    join(repositoryRoot, staticAnalysisDebt.baselinePath),
    join(linkedDebtRoot, staticAnalysisDebt.baselinePath),
  );
  assert.throws(
    () => p00.measureStaticAnalysisDebt({ root: linkedDebtRoot, phpVersion: '8.5.6' }),
    /baseline/i,
  );
  const packageJson = JSON.parse(readFileSync(join(repositoryRoot, 'package.json'), 'utf8'));
  assert.equal(packageJson.scripts['bundle:check'], 'node scripts/quality/p00.mjs bundle dist');
  const viteConfig = readFileSync(join(repositoryRoot, 'vite.config.ts'), 'utf8');
  assert.match(viteConfig, /esbuild:\s*{\s*legalComments:\s*'eof'/);
  assert.match(viteConfig, /modulePreload:\s*{\s*polyfill:\s*false/);
  assert.doesNotMatch(
    readFileSync(join(repositoryRoot, 'scripts/quality/run-p00'), 'utf8'),
    /bundle dist \d+/,
  );
  assert.doesNotMatch(
    readFileSync(join(repositoryRoot, 'scripts/quality/p00.mjs'), 'utf8'),
    /measureBundle\(\s*resolve\(args\[0\]\),\s*Number\(args\[1\]\)/,
  );
});

test('bundle gate measures entry/preload and rejects escape, absent debt, and growth', () => {
  const root = temp('bundle');
  mkdirSync(join(root, 'dist/assets'), { recursive: true });
  writeFileSync(
    join(root, 'dist/index.html'),
    '<script type="module" src="/assets/entry.js"></script><link rel="modulepreload" href="/assets/vendor.js">',
  );
  writeFileSync(join(root, 'dist/assets/entry.js'), 'A'.repeat(500001));
  writeFileSync(join(root, 'dist/assets/vendor.js'), 'export const value = 1;');
  writeFileSync(join(root, 'vite.log'), contract.bundle.warning + '\n');
  const measured = measureBundle(
    join(root, 'dist'),
    contract.bundle.initialGzipLimitBytes,
    join(root, 'vite.log'),
    join(root, 'artifacts'),
  );
  assert.deepEqual(
    measured.files.map((file) => file.path),
    ['assets/entry.js', 'assets/vendor.js'],
  );
  assert.deepEqual(measured.largeChunkDebt.affectedFiles, ['assets/entry.js']);
  assert.equal(measured.largeChunkDebt.messageSha256, sha256(contract.bundle.warning));
  assert.deepEqual(JSON.parse(readFileSync(join(root, 'artifacts/bundle.json'))), measured);

  const standaloneRoot = join(root, 'standalone-root');
  const standaloneEnvironment = { ...process.env, P00_ARTIFACT_DIR: standaloneRoot };
  delete standaloneEnvironment.P00_JOB_ARTIFACT_DIR;
  delete standaloneEnvironment.P00_JOB;
  const standaloneOutput = execFileSync(
    process.execPath,
    ['scripts/quality/p00.mjs', 'bundle', join(root, 'dist'), join(root, 'vite.log')],
    { cwd: repositoryRoot, encoding: 'utf8', env: standaloneEnvironment },
  );
  assert.match(
    standaloneOutput,
    /^P00_BUNDLE PASS gzip=\d+ limit=216797 node=24\.18\.0 zlib=1\.3\.1-e00f703\n$/,
  );
  assert.equal(existsSync(join(standaloneRoot, 'standalone-bundle/bundle.json')), true);

  const jobArtifacts = join(root, 'job-artifacts');
  const jobEnvironment = {
    ...standaloneEnvironment,
    P00_ARTIFACT_DIR: join(root, 'unused-standalone-root'),
    P00_JOB_ARTIFACT_DIR: jobArtifacts,
  };
  execFileSync(
    process.execPath,
    ['scripts/quality/p00.mjs', 'bundle', join(root, 'dist'), join(root, 'vite.log')],
    { cwd: repositoryRoot, encoding: 'utf8', env: jobEnvironment },
  );
  assert.equal(existsSync(join(jobArtifacts, 'bundle.json')), true);
  assert.equal(
    existsSync(join(root, 'unused-standalone-root/standalone-bundle/bundle.json')),
    false,
  );

  const zlibDescriptor = Object.getOwnPropertyDescriptor(process.versions, 'zlib');
  Object.defineProperty(process.versions, 'zlib', { ...zlibDescriptor, value: 'unexpected-zlib' });
  try {
    assert.throws(
      () => measureBundle(join(root, 'missing-dist'), contract.bundle.initialGzipLimitBytes),
      /zlib runtime differs from the P00 bundle contract/,
    );
  } finally {
    Object.defineProperty(process.versions, 'zlib', zlibDescriptor);
  }

  writeFileSync(
    join(root, 'dist/index.html'),
    '<script type="module" src="../escape.js"></script>',
  );
  assert.throws(() =>
    measureBundle(
      join(root, 'dist'),
      contract.bundle.initialGzipLimitBytes,
      null,
      join(root, 'escape-artifacts'),
    ),
  );
  writeFileSync(join(root, 'dist/assets/entry.js'), 'export {};');
  writeFileSync(join(root, 'dist/assets/vendor.js'), 'export const value = 1;');
  writeFileSync(
    join(root, 'dist/index.html'),
    '<script type="module" src="/assets/vendor.js"></script>',
  );
  assert.throws(() =>
    measureBundle(
      join(root, 'dist'),
      contract.bundle.initialGzipLimitBytes,
      null,
      join(root, 'no-debt-artifacts'),
    ),
  );
  writeFileSync(join(root, 'dist/assets/entry.js'), randomBytes(600000));
  writeFileSync(
    join(root, 'dist/index.html'),
    '<script type="module" src="/assets/entry.js"></script>',
  );
  const overArtifacts = join(root, 'over-artifacts');
  assert.throws(
    () =>
      measureBundle(join(root, 'dist'), contract.bundle.initialGzipLimitBytes, null, overArtifacts),
    /Initial JavaScript gzip budget exceeded/,
  );
  const overBudget = JSON.parse(readFileSync(join(overArtifacts, 'bundle.json'), 'utf8'));
  assert.equal(overBudget.limitBytes, 216797);
  assert.equal(overBudget.zlibVersion, '1.3.1-e00f703');
  assert.ok(overBudget.gzipBytes > overBudget.limitBytes);
});

test('aggregate accepts only six same-SHA same-input exact-count attempt-one records', () => {
  const pass = temp('aggregate-pass');
  resultSet(pass);
  assert.equal(aggregateRequiredGates(pass).status, 'passed');
  const mutations = [
    (values) => values.pop(),
    (values) => {
      values[0].integratedSha = 'f'.repeat(40);
    },
    (values) => {
      values[1].status = 'failed';
      values[1].exitCode = 1;
    },
    (values) => {
      values[2].retryAttempt = 2;
    },
    (values) => {
      values[3].unexplainedSkipCount = 1;
    },
    (values) => {
      values[4].testCount = 7;
    },
    (values) => {
      values[5].inputFingerprintSha256 = 'f'.repeat(64);
    },
    (values) => {
      values[0].inputs.staticAnalysisDebt.baselineDiagnosticCount += 1;
      values[0].inputFingerprintSha256 = sha256(stableJson(values[0].inputs));
    },
  ];
  for (const [index, mutate] of mutations.entries()) {
    const directory = temp('aggregate-fail-' + index);
    resultSet(directory, mutate);
    assert.throws(() => aggregateRequiredGates(directory));
  }
  const aggregate = aggregateRequiredGates(pass);
  for (const mutate of [
    (value) => {
      value.retryAttempt = 2;
    },
    (value) => {
      value.testCount -= 1;
    },
    (value) => {
      value.failureCount = 1;
    },
    (value) => {
      value.unexplainedSkipCount = 1;
    },
  ]) {
    const changed = structuredClone(aggregate);
    mutate(changed);
    assert.throws(() => assertAggregate(changed, 'local'));
  }
});

test('evidence builder validates hashes, review, two runs, debt, and secret rejection', () => {
  const root = temp('evidence');
  const local = join(root, 'local');
  mkdirSync(local);
  resultSet(local);
  let aggregate = aggregateRequiredGates(local);
  const postgresqlObservation = {
    kind: inputs().postgresql.kind,
    identity: inputs().postgresql.identity,
    attestationSha256: sha64,
    instanceNonceSha256: sha64,
    endpointSha256: sha64,
    serverVersionNum: 160000,
    databaseName: 'dorzak_test',
  };
  json(join(local, 'postgresql-16/postgresql-identity.json'), postgresqlObservation);
  json(join(local, 'frontend/bundle.json'), {
    schemaVersion: 1,
    gzipBytes: 210000,
    minifiedBytes: 500001,
    limitBytes: contract.bundle.initialGzipLimitBytes,
    nodeVersion: process.versions.node,
    zlibVersion: process.versions.zlib,
    files: [{ path: 'assets/index.js', minifiedBytes: 500001, gzipBytes: 210000 }],
    largeChunkDebt: {
      status: 'accepted-open',
      thresholdBytes: 500000,
      affectedFiles: ['assets/index.js'],
      message: contract.bundle.warning,
      messageSha256: sha256(contract.bundle.warning),
      occurrenceCount: 1,
    },
  });
  for (const [job, artifact] of [
    ['postgresql-16', 'postgresqlIdentity'],
    ['frontend', 'bundle'],
  ]) {
    const resultPath = join(local, job, 'result.json');
    const result = JSON.parse(readFileSync(resultPath, 'utf8'));
    const reference = result.artifacts[artifact];
    reference.sha256 = sha256(readFileSync(join(local, job, reference.path)));
    json(resultPath, result);
  }
  aggregate = aggregateRequiredGates(local);
  const reviewJson = join(root, 'review.json');
  const reviewMarkdown = join(root, 'review.md');
  json(reviewJson, {
    schemaVersion: 1,
    baseSha: sha40,
    codeSha: sha40,
    critical: 0,
    important: 0,
    minor: [],
  });
  writeFileSync(
    reviewMarkdown,
    [
      '# Independent P00 Review',
      '',
      'BASE_SHA: ' + sha40,
      'CODE_SHA: ' + sha40,
      'Critical: 0',
      'Important: 0',
      '',
      '## Minor',
      '',
      '- None.',
      '',
    ].join('\n'),
  );
  const ci = (runId, observation) => {
    const ciPlatform = platformObservation('ci');
    const jobs = structuredClone(aggregate.jobs).map((job) => ({
      ...job,
      platformObservation: ciPlatform,
      platformObservationFingerprintSha256: sha256(stableJson(ciPlatform)),
    }));
    jobs.find((job) => job.job === 'postgresql-16').artifacts.postgresqlIdentity.sha256 = sha256(
      stableJson(observation),
    );
    return {
      schemaVersion: 1,
      provider: 'approved-provider',
      runId,
      attempt: 1,
      integratedSha: sha40,
      contractSha256,
      inputFingerprintSha256: aggregate.inputFingerprintSha256,
      inputs: structuredClone(aggregate.inputs),
      platformObservationFingerprintSha256: sha256(stableJson(ciPlatform)),
      platformObservation: ciPlatform,
      postgresqlObservation: observation,
      requiredGate: { status: 'passed', jobs: 6 },
      jobs,
    };
  };
  const ci1 = join(root, 'ci-1.json');
  const ci2 = join(root, 'ci-2.json');
  const secondPostgresqlObservation = {
    ...postgresqlObservation,
    attestationSha256: '1'.repeat(64),
    instanceNonceSha256: '2'.repeat(64),
    endpointSha256: '3'.repeat(64),
    databaseName: 'dorzak_second_test',
  };
  json(ci1, ci('run-1', postgresqlObservation));
  json(ci2, ci('run-2', secondPostgresqlObservation));
  const manifest = buildEvidence({
    outputDirectory: join(root, 'output'),
    baseSha: sha40,
    codeSha: sha40,
    integratedSha: sha40,
    localDirectory: local,
    ciRunPaths: [ci1, ci2],
    reviewJsonPath: reviewJson,
    reviewMarkdownPath: reviewMarkdown,
  });
  assert.equal(validateEvidence(manifest).files.length, 7);
  assert.equal(readdirSync(join(root, 'output')).length, 8);
  assert.deepEqual(manifest.inputs.staticAnalysisDebt, staticAnalysisDebt);
  assert.deepEqual(
    JSON.parse(readFileSync(join(root, 'output/local-full-matrix.json'))).inputs.staticAnalysisDebt,
    staticAnalysisDebt,
  );
  assert.deepEqual(
    JSON.parse(readFileSync(join(root, 'output/ci-run-1.json'))).inputs.staticAnalysisDebt,
    staticAnalysisDebt,
  );
  const generatedDebtSchema = JSON.parse(readFileSync(join(root, 'output/manifest.schema.json')))
    .properties.inputs.properties.staticAnalysisDebt;
  assert.equal(generatedDebtSchema.additionalProperties, false);
  assert.deepEqual(generatedDebtSchema.required, Object.keys(staticAnalysisDebt));
  for (const [name, value] of Object.entries(staticAnalysisDebt)) {
    assert.deepEqual(generatedDebtSchema.properties[name], { const: value });
  }
  const manifestPath = join(root, 'output', 'manifest.json');
  const originalManifest = JSON.parse(readFileSync(manifestPath, 'utf8'));
  for (const mutate of [
    (value) => {
      value.local.platformObservation.os = 'different-os';
    },
    (value) => {
      value.bundle.gzipBytes -= 1;
    },
    (value) => {
      value.review.minor = ['tampered summary'];
    },
    (value) => {
      value.ciRuns[0].provider = 'different-provider';
    },
    (value) => {
      value.inputs.staticAnalysisDebt.baselineDiagnosticCount += 1;
    },
  ]) {
    const changedSummary = structuredClone(originalManifest);
    mutate(changedSummary);
    json(manifestPath, changedSummary);
    assert.throws(() => validateEvidence(manifestPath));
  }
  json(manifestPath, originalManifest);
  assert.notDeepEqual(
    JSON.parse(readFileSync(ci1)).postgresqlObservation,
    JSON.parse(readFileSync(ci2)).postgresqlObservation,
  );
  assert.throws(() => stableJson({ authorization: 'Bearer unsafe-value' }));
  const crossBinding = ci('run-2', secondPostgresqlObservation);
  crossBinding.jobs[0].inputs.staticAnalysisDebt.baselineDiagnosticCount += 1;
  crossBinding.jobs[0].inputFingerprintSha256 = sha256(stableJson(crossBinding.jobs[0].inputs));
  json(ci2, crossBinding);
  assert.throws(() =>
    buildEvidence({
      outputDirectory: join(root, 'cross-binding-rejected'),
      baseSha: sha40,
      codeSha: sha40,
      integratedSha: sha40,
      localDirectory: local,
      ciRunPaths: [ci1, ci2],
      reviewJsonPath: reviewJson,
      reviewMarkdownPath: reviewMarkdown,
    }),
  );
  json(ci2, ci('run-2', secondPostgresqlObservation));
  const unboundPostgresql = ci('run-2', secondPostgresqlObservation);
  unboundPostgresql.postgresqlObservation.databaseName = 'unbound_test';
  json(ci2, unboundPostgresql);
  assert.throws(() =>
    buildEvidence({
      outputDirectory: join(root, 'postgresql-unbound-rejected'),
      baseSha: sha40,
      codeSha: sha40,
      integratedSha: sha40,
      localDirectory: local,
      ciRunPaths: [ci1, ci2],
      reviewJsonPath: reviewJson,
      reviewMarkdownPath: reviewMarkdown,
    }),
  );
  json(ci2, ci('run-2', secondPostgresqlObservation));
  json(reviewJson, {
    schemaVersion: 1,
    baseSha: sha40,
    codeSha: sha40,
    critical: 1,
    important: 0,
    minor: [],
  });
  const rejected = join(root, 'rejected');
  assert.throws(() =>
    buildEvidence({
      outputDirectory: rejected,
      baseSha: sha40,
      codeSha: sha40,
      integratedSha: sha40,
      localDirectory: local,
      ciRunPaths: [ci1, ci2],
      reviewJsonPath: reviewJson,
      reviewMarkdownPath: reviewMarkdown,
    }),
  );
  assert.equal(existsSync(rejected), false);
  json(reviewJson, {
    schemaVersion: 1,
    baseSha: sha40,
    codeSha: sha40,
    critical: 0,
    important: 0,
    minor: [],
  });
  for (const faultPoint of ['after-temp', 'after-rename']) {
    const name = 'fault-' + faultPoint;
    const path = join(root, name);
    assert.throws(() =>
      buildEvidence({
        outputDirectory: path,
        baseSha: sha40,
        codeSha: sha40,
        integratedSha: sha40,
        localDirectory: local,
        ciRunPaths: [ci1, ci2],
        reviewJsonPath: reviewJson,
        reviewMarkdownPath: reviewMarkdown,
        faultPoint,
      }),
    );
    assert.equal(existsSync(path), false);
    assert.equal(
      readdirSync(root).some((entry) => entry.startsWith('.' + name + '.tmp-')),
      false,
    );
  }
});

test('aggregate rejects changed raw bytes, extra roots, and symlinks', () => {
  const changed = temp('raw-changed');
  const records = resultSet(changed);
  const reference = Object.values(records.find((value) => value.job === 'sqlite').artifacts)[0];
  writeFileSync(join(changed, 'sqlite', reference.path), 'tampered\n');
  assert.throws(() => aggregateRequiredGates(changed));
  const extra = temp('raw-extra');
  resultSet(extra);
  writeFileSync(join(extra, 'unexpected'), 'extra\n');
  assert.throws(() => aggregateRequiredGates(extra));
  const linked = temp('raw-link');
  resultSet(linked);
  symlinkSync(join(linked, 'sqlite'), join(linked, 'sqlite-alias'));
  assert.throws(() => aggregateRequiredGates(linked));

  const acceptedCanonicalLinks = [];
  for (const name of ['result.json', 'job.log']) {
    const directory = temp('canonical-link-' + name.replace('.', '-'));
    const external = temp('canonical-link-target-' + name.replace('.', '-'));
    resultSet(directory);
    const canonicalPath = join(directory, 'sqlite', name);
    const externalPath = join(external, name);
    writeFileSync(externalPath, readFileSync(canonicalPath));
    unlinkSync(canonicalPath);
    symlinkSync(externalPath, canonicalPath);
    try {
      aggregateRequiredGates(directory);
      acceptedCanonicalLinks.push(name);
    } catch (error) {
      assert.equal(error.message, 'Aggregate canonical file is unsafe: sqlite/' + name);
    }
  }
  assert.deepEqual(acceptedCanonicalLinks, []);
});

test('aggregate cross-binds portable inputs while preserving per-job platform observations', () => {
  const varied = temp('platform-varied');
  resultSet(varied, (values) => {
    values[5].platformObservation.osRelease = 'different-ephemeral-image-release';
    values[5].platformObservationFingerprintSha256 = sha256(
      stableJson(values[5].platformObservation),
    );
  });
  const variedAggregate = aggregateRequiredGates(varied);
  assert.deepEqual(variedAggregate.inputs.staticAnalysisDebt, staticAnalysisDebt);
  assert.notEqual(
    variedAggregate.jobs[0].platformObservationFingerprintSha256,
    variedAggregate.jobs[5].platformObservationFingerprintSha256,
  );
  assertAggregate(variedAggregate, 'local');
  const directory = temp('runner-mismatch');
  resultSet(directory, (values) => {
    values[5].platformObservation.runnerClass = 'different-runner';
    values[5].platformObservationFingerprintSha256 = sha256(
      stableJson(values[5].platformObservation),
    );
  });
  assert.throws(() => aggregateRequiredGates(directory));
  const swapped = temp('runner-role-swapped');
  resultSet(swapped, (values) => {
    for (const value of values) {
      value.platformObservation = platformObservation('ci');
      value.platformObservationFingerprintSha256 = sha256(stableJson(value.platformObservation));
    }
  });
  assert.throws(() => assertAggregate(aggregateRequiredGates(swapped), 'local'));
});

test('secret rejection covers keys, headers, query strings, URLs and token families', () => {
  for (const value of [
    { apiKey: 'x' },
    { clientSecret: 'x' },
    { accessToken: 'x' },
    { dbUrl: 'x' },
    'Cookie: sid=x',
    'Authorization: Basic dXNlcjpwYXNz',
    '?token=x',
    'postgresql://user:pass@example.test/db',
    'AKIA1234567890ABCDEF',
    'ghp_abcdefghijklmnopqrstuvwxyz',
    'eyJabc.def.ghi',
  ])
    assert.throws(() => stableJson(value));
});

test('closed schema validator rejects missing, extra, type and pattern mutations', () => {
  const schema = {
    type: 'object',
    additionalProperties: false,
    required: ['sha'],
    properties: { sha: { type: 'string', pattern: '^[0-9a-f]{4}$' } },
  };
  validateSchema(schema, { sha: 'abcd' });
  assert.throws(() => validateSchema(schema, {}));
  assert.throws(() => validateSchema(schema, { sha: 'abcd', extra: true }));
  assert.throws(() => validateSchema(schema, { sha: 1 }));
  assert.throws(() => validateSchema(schema, { sha: 'zzzz' }));
});

test('exact-key helper rejects unknown evidence fields', () => {
  assertExactKeys({ schemaVersion: 1, state: 'passed' }, ['schemaVersion', 'state'], '$.sample');
  assert.throws(() =>
    assertExactKeys(
      { schemaVersion: 1, state: 'passed', credential: 'x' },
      ['schemaVersion', 'state'],
      '$.sample',
    ),
  );
});
