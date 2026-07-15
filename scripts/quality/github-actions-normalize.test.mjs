import assert from 'node:assert/strict';
import {
  existsSync,
  mkdtempSync,
  mkdirSync,
  readFileSync,
  readdirSync,
  realpathSync,
  rmSync,
  writeFileSync,
} from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import test from 'node:test';
import { aggregateRequiredGates, contract, contractSha256, sha256, stableJson } from './p00.mjs';
import { crc32, inspectZip, normalize } from './github-actions-normalize.mjs';

const sha40 = '0123456789abcdef0123456789abcdef01234567';
const sha64a = 'a'.repeat(64);
const sha64b = 'b'.repeat(64);
const sha64c = 'c'.repeat(64);
const nonce = 'd'.repeat(64);
const identity =
  'docker.io/library/postgres@sha256:c95fd5346040eba2de3c435e14874af18f5d681fb5848d4f081dbead0878af28';
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
const jobs = contract.jobs.map((job) => job.name);
const artifactNames = [...jobs, 'required-gates'];
const temp = (name) => realpathSync(mkdtempSync(join(tmpdir(), 'dorzak-p00-gha-' + name + '-')));

function storedZip(entries) {
  const locals = [];
  const centrals = [];
  let offset = 0;
  for (const entry of entries) {
    const name = Buffer.from(entry.name);
    const content = Buffer.from(entry.content);
    const crc = crc32(content);
    const local = Buffer.alloc(30);
    local.writeUInt32LE(0x04034b50, 0);
    local.writeUInt16LE(20, 4);
    local.writeUInt16LE(0x0800, 6);
    local.writeUInt16LE(0, 8);
    local.writeUInt32LE(crc, 14);
    local.writeUInt32LE(content.length, 18);
    local.writeUInt32LE(content.length, 22);
    local.writeUInt16LE(name.length, 26);
    const central = Buffer.alloc(46);
    central.writeUInt32LE(0x02014b50, 0);
    central.writeUInt16LE((3 << 8) | 20, 4);
    central.writeUInt16LE(20, 6);
    central.writeUInt16LE(0x0800, 8);
    central.writeUInt16LE(0, 10);
    central.writeUInt32LE(crc, 16);
    central.writeUInt32LE(content.length, 20);
    central.writeUInt32LE(content.length, 24);
    central.writeUInt16LE(name.length, 28);
    central.writeUInt32LE((((entry.mode ?? 0o100644) & 0xffff) * 0x10000) >>> 0, 38);
    central.writeUInt32LE(offset, 42);
    locals.push(local, name, content);
    centrals.push(central, name);
    offset += local.length + name.length + content.length;
  }
  const centralBytes = Buffer.concat(centrals);
  const end = Buffer.alloc(22);
  end.writeUInt32LE(0x06054b50, 0);
  end.writeUInt16LE(entries.length, 8);
  end.writeUInt16LE(entries.length, 10);
  end.writeUInt32LE(centralBytes.length, 12);
  end.writeUInt32LE(offset, 16);
  return Buffer.concat([...locals, centralBytes, end]);
}

function inputs() {
  return {
    contractSha256,
    runtime: { php: '8.5.6', composer: '2.9.8', node: '24.18.0', npm: '11.16.0' },
    lockfileSha256: { composer: sha64a, npm: sha64b },
    postgresql: { kind: 'oci', identity, policy: 'postgresql-16-test-closed-transport-v1' },
    playwright: { packageVersion: '1.58.0', chromiumRevision: '1234567' },
    bundleAlgorithms: {
      assetSelection: 'html-module-entry-and-modulepreload-v1',
      gzip: 'node-zlib-level-9',
    },
    runnerClasses: { local: 'local-macos-arm64', ci: 'github-hosted-ubuntu-24.04-x64' },
    staticAnalysisDebt: structuredClone(staticAnalysisDebt),
  };
}

function platform(index) {
  return {
    os: 'linux',
    arch: 'x64',
    osRelease: 'ephemeral-' + index,
    runnerRole: 'ci',
    runnerClass: 'github-hosted-ubuntu-24.04-x64',
    zlib: '1.3.1',
    chromiumExecutableSha256: sha64c,
  };
}

function rawArtifacts(job) {
  if (job.name === 'composer-validation')
    return {
      'dispatcher.tap': 'TAP version 13\nok 1 - list\nok 2 - invalid\n1..2\n',
      'p00-node.tap':
        'TAP version 13\n' +
        Array.from({ length: 9 }, (_, index) => `ok ${index + 1} - node`).join('\n') +
        '\n1..9\n',
    };
  if (job.name === 'sqlite' || job.name === 'postgresql-16')
    return {
      [job.name + '.junit.xml']:
        `<testsuite tests="${job.testCount}" failures="0" errors="0" skipped="0"></testsuite>\n`,
    };
  if (job.name === 'frontend')
    return {
      'vitest.json': stableJson({ numTotalTests: 10, numFailedTests: 0, numPendingTests: 0 }),
      'bundle.json': stableJson({ fixture: 'bundle' }),
      'vite-build.log': 'accepted fixture warning\n',
    };
  if (job.name === 'playwright')
    return {
      'playwright.json': stableJson({
        stats: { expected: 9, unexpected: 0, flaky: 0, skipped: 0 },
      }),
    };
  return {};
}

function fixture(name) {
  const root = temp(name);
  const jobRoot = join(root, 'jobs');
  const zipRoot = join(root, 'zips');
  mkdirSync(jobRoot);
  mkdirSync(zipRoot);
  const declaredInputs = inputs();
  const pgObservation = {
    kind: 'oci',
    identity,
    attestationSha256: sha64a,
    instanceNonceSha256: sha64b,
    endpointSha256: sha64c,
    serverVersionNum: 160014,
    databaseName: 'dorzak_fixture_test',
  };
  const records = contract.jobs.map((job, index) => {
    const directory = join(jobRoot, job.name);
    mkdirSync(directory);
    const raw = rawArtifacts(job);
    if (job.name === 'postgresql-16') raw['postgresql-identity.json'] = stableJson(pgObservation);
    for (const [path, content] of Object.entries(raw))
      writeFileSync(join(directory, path), content);
    const mapping = {
      'composer-validation': { dispatcherTap: 'dispatcher.tap', p00NodeTap: 'p00-node.tap' },
      'php-style-static': {},
      sqlite: { junit: 'sqlite.junit.xml' },
      'postgresql-16': {
        junit: 'postgresql-16.junit.xml',
        postgresqlIdentity: 'postgresql-identity.json',
      },
      frontend: { vitest: 'vitest.json', bundle: 'bundle.json', viteBuildLog: 'vite-build.log' },
      playwright: { playwrightJson: 'playwright.json' },
    };
    const artifacts = Object.fromEntries(
      Object.entries(mapping[job.name]).map(([key, path]) => [
        key,
        { path, sha256: sha256(readFileSync(join(directory, path))) },
      ]),
    );
    const log = 'log for ' + job.name + '\n';
    writeFileSync(join(directory, 'job.log'), log);
    const observation = platform(index);
    const record = {
      schemaVersion: 1,
      job: job.name,
      command: job.command,
      integratedSha: sha40,
      status: 'passed',
      exitCode: 0,
      retryAttempt: 1,
      testCount: job.testCount,
      failureCount: 0,
      unexplainedSkipCount: 0,
      durationMs: 10,
      contractSha256,
      inputFingerprintSha256: sha256(stableJson(declaredInputs)),
      inputs: declaredInputs,
      platformObservationFingerprintSha256: sha256(stableJson(observation)),
      platformObservation: observation,
      logSha256: sha256(log),
      artifacts,
    };
    writeFileSync(join(directory, 'result.json'), stableJson(record));
    return record;
  });
  const aggregate = aggregateRequiredGates(jobRoot);
  const zipBytes = {};
  for (const job of jobs) {
    const directory = join(jobRoot, job);
    zipBytes[job] = storedZip(
      readdirSync(directory)
        .sort()
        .map((path) => ({
          name: path,
          content: readFileSync(join(directory, path)),
        })),
    );
  }
  zipBytes['required-gates'] = storedZip([
    { name: 'required-gates.json', content: stableJson(aggregate) },
  ]);
  const artifacts = artifactNames.map((artifact, index) => {
    const path = join(zipRoot, artifact + '.zip');
    writeFileSync(path, zipBytes[artifact]);
    return {
      id: 2000 + index,
      name: artifact,
      sizeInBytes: zipBytes[artifact].length,
      expired: false,
      digest: 'sha256:' + sha256(zipBytes[artifact]),
      runId: 1001,
      headSha: sha40,
    };
  });
  const metadata = {
    schemaVersion: 1,
    repository: 'Zakariagattouchi/Dorzak-solution',
    integrationBranch: 'main',
    workflowPath: '.github/workflows/p00-quality.yml',
    evidenceNonce: nonce,
    run: {
      id: 1001,
      attempt: 1,
      event: 'workflow_dispatch',
      status: 'completed',
      conclusion: 'success',
      headSha: sha40,
      headBranch: 'main',
      path: '.github/workflows/p00-quality.yml',
      displayTitle: 'p00-' + nonce,
    },
    jobs: artifactNames.map((job, index) => ({
      id: 3000 + index,
      name: job,
      status: 'completed',
      conclusion: 'success',
      headSha: sha40,
      runAttempt: 1,
      runnerName: 'GitHub Actions ' + index,
      labels: ['ubuntu-24.04'],
    })),
    artifacts,
    checks: [
      {
        id: 4001,
        name: 'required-gates',
        status: 'completed',
        conclusion: 'success',
        headSha: sha40,
        appId: 15368,
        appSlug: 'github-actions',
      },
    ],
  };
  const metadataPath = join(root, 'metadata.json');
  writeFileSync(metadataPath, stableJson(metadata));
  return { root, jobRoot, zipRoot, metadataPath, metadata, records, aggregate };
}

function writeMetadata(value, path) {
  writeFileSync(path, stableJson(value));
}

test('normalizes one exact successful attempt-one run and preserves per-job observations', () => {
  const value = fixture('valid');
  const output = join(value.root, 'ci-run.json');
  const normalized = normalize({
    metadataPath: value.metadataPath,
    zipDirectory: value.zipRoot,
    outputPath: output,
    expectedSha: sha40,
    expectedNonce: nonce,
  });
  assert.equal(normalized.provider, 'github-actions');
  assert.equal(normalized.runId, '1001');
  assert.equal(normalized.jobs.length, 6);
  assert.equal(new Set(normalized.jobs.map((job) => job.platformObservation.osRelease)).size, 6);
  assert.deepEqual(normalized.inputs.staticAnalysisDebt, staticAnalysisDebt);
  assert.equal(
    normalized.jobs.every(
      (job) => stableJson(job.inputs.staticAnalysisDebt) === stableJson(staticAnalysisDebt),
    ),
    true,
  );
  assert.deepEqual(JSON.parse(readFileSync(output, 'utf8')), normalized);
});

test('rejects old-name and wrong-case repository identity with no output', () => {
  const mutations = [
    ['old-name', 'Zakariagattouchi/dorzak'],
    ['wrong-case', 'Zakariagattouchi/dorzak-solution'],
  ];
  const outcomes = mutations.map(([name, repository]) => {
    const item = fixture('repository-' + name);
    const changed = structuredClone(item.metadata);
    const output = join(item.root, 'rejected.json');
    changed.repository = repository;
    writeMetadata(changed, item.metadataPath);
    let rejected = false;
    try {
      normalize({
        metadataPath: item.metadataPath,
        zipDirectory: item.zipRoot,
        outputPath: output,
        expectedSha: sha40,
        expectedNonce: nonce,
      });
    } catch {
      rejected = true;
    }
    return { name, rejected, outputExists: existsSync(output) };
  });
  assert.deepEqual(
    outcomes,
    mutations.map(([name]) => ({ name, rejected: true, outputExists: false })),
  );
});

test('rejects retry, wrong event/SHA/nonce, failed or extra provider records, and wrong digest', () => {
  const mutations = [
    (value) => {
      value.run.attempt = 2;
    },
    (value) => {
      value.run.event = 'push';
    },
    (value) => {
      value.run.headSha = 'f'.repeat(40);
    },
    (value) => {
      value.evidenceNonce = 'e'.repeat(64);
    },
    (value) => {
      value.jobs[0].conclusion = 'failure';
    },
    (value) => {
      value.jobs.push(structuredClone(value.jobs[0]));
    },
    (value) => {
      value.jobs[1].id = value.jobs[0].id;
    },
    (value) => {
      value.artifacts[1].id = value.artifacts[0].id;
    },
    (value) => {
      value.artifacts[0].sizeInBytes += 1;
    },
    (value) => {
      value.artifacts[0].digest = 'sha256:' + 'f'.repeat(64);
    },
    (value) => {
      value.checks[0].appSlug = 'untrusted-app';
    },
  ];
  for (const [index, mutate] of mutations.entries()) {
    const item = fixture('metadata-' + index);
    const changed = structuredClone(item.metadata);
    mutate(changed);
    writeMetadata(changed, item.metadataPath);
    assert.throws(() =>
      normalize({
        metadataPath: item.metadataPath,
        zipDirectory: item.zipRoot,
        outputPath: join(item.root, 'rejected.json'),
        expectedSha: sha40,
        expectedNonce: nonce,
      }),
    );
    assert.equal(existsSync(join(item.root, 'rejected.json')), false);
  }
});

test('rejects escape, duplicate, symlink, encryption, ZIP64, and oversized metadata', () => {
  assert.throws(() => inspectZip(storedZip([{ name: '../escape', content: 'x' }])));
  assert.throws(() =>
    inspectZip(
      storedZip([
        { name: 'same', content: 'a' },
        { name: 'same', content: 'b' },
      ]),
    ),
  );
  assert.throws(() => inspectZip(storedZip([{ name: 'link', content: 'target', mode: 0o120777 }])));
  const encrypted = storedZip([{ name: 'file', content: 'x' }]);
  encrypted.writeUInt16LE(encrypted.readUInt16LE(6) | 1, 6);
  encrypted.writeUInt16LE(
    encrypted.readUInt16LE(encrypted.indexOf(Buffer.from([0x50, 0x4b, 0x01, 0x02])) + 8) | 1,
    encrypted.indexOf(Buffer.from([0x50, 0x4b, 0x01, 0x02])) + 8,
  );
  assert.throws(() => inspectZip(encrypted));
  const zip64 = storedZip([{ name: 'file', content: 'x' }]);
  const end = zip64.length - 22;
  zip64.writeUInt16LE(0xffff, end + 10);
  assert.throws(() => inspectZip(zip64));
  const oversized = storedZip([{ name: 'file', content: 'x' }]);
  const central = oversized.indexOf(Buffer.from([0x50, 0x4b, 0x01, 0x02]));
  oversized.writeUInt32LE(64 * 1024 * 1024 + 1, central + 24);
  assert.throws(() => inspectZip(oversized));
});

test('rejects changed reporter bytes and wrong runner class', () => {
  const reporter = fixture('reporter');
  writeFileSync(
    join(reporter.jobRoot, 'sqlite', 'sqlite.junit.xml'),
    '<testsuite tests="445" failures="0" errors="0" skipped="0"></testsuite>\n',
  );
  const reporterZip = storedZip(
    readdirSync(join(reporter.jobRoot, 'sqlite'))
      .sort()
      .map((path) => ({
        name: path,
        content: readFileSync(join(reporter.jobRoot, 'sqlite', path)),
      })),
  );
  writeFileSync(join(reporter.zipRoot, 'sqlite.zip'), reporterZip);
  reporter.metadata.artifacts.find((artifact) => artifact.name === 'sqlite').digest =
    'sha256:' + sha256(reporterZip);
  reporter.metadata.artifacts.find((artifact) => artifact.name === 'sqlite').sizeInBytes =
    reporterZip.length;
  writeMetadata(reporter.metadata, reporter.metadataPath);
  assert.throws(() =>
    normalize({
      metadataPath: reporter.metadataPath,
      zipDirectory: reporter.zipRoot,
      outputPath: join(reporter.root, 'rejected.json'),
      expectedSha: sha40,
      expectedNonce: nonce,
    }),
  );

  const runner = fixture('runner');
  const resultPath = join(runner.jobRoot, 'playwright', 'result.json');
  const result = JSON.parse(readFileSync(resultPath, 'utf8'));
  result.platformObservation.runnerClass = 'wrong-runner';
  result.platformObservationFingerprintSha256 = sha256(stableJson(result.platformObservation));
  writeFileSync(resultPath, stableJson(result));
  const runnerZip = storedZip(
    readdirSync(join(runner.jobRoot, 'playwright'))
      .sort()
      .map((path) => ({
        name: path,
        content: readFileSync(join(runner.jobRoot, 'playwright', path)),
      })),
  );
  writeFileSync(join(runner.zipRoot, 'playwright.zip'), runnerZip);
  runner.metadata.artifacts.find((artifact) => artifact.name === 'playwright').digest =
    'sha256:' + sha256(runnerZip);
  runner.metadata.artifacts.find((artifact) => artifact.name === 'playwright').sizeInBytes =
    runnerZip.length;
  writeMetadata(runner.metadata, runner.metadataPath);
  assert.throws(() =>
    normalize({
      metadataPath: runner.metadataPath,
      zipDirectory: runner.zipRoot,
      outputPath: join(runner.root, 'rejected.json'),
      expectedSha: sha40,
      expectedNonce: nonce,
    }),
  );

  const debt = fixture('static-debt-one-job');
  const debtJobDirectory = join(debt.jobRoot, 'sqlite');
  const debtResultPath = join(debtJobDirectory, 'result.json');
  const debtResult = JSON.parse(readFileSync(debtResultPath, 'utf8'));
  debtResult.inputs.staticAnalysisDebt.baselineDiagnosticCount += 1;
  debtResult.inputFingerprintSha256 = sha256(stableJson(debtResult.inputs));
  writeFileSync(debtResultPath, stableJson(debtResult));
  const debtZip = storedZip(
    readdirSync(debtJobDirectory)
      .sort()
      .map((path) => ({ name: path, content: readFileSync(join(debtJobDirectory, path)) })),
  );
  writeFileSync(join(debt.zipRoot, 'sqlite.zip'), debtZip);
  const debtArtifact = debt.metadata.artifacts.find((artifact) => artifact.name === 'sqlite');
  debtArtifact.digest = 'sha256:' + sha256(debtZip);
  debtArtifact.sizeInBytes = debtZip.length;
  writeMetadata(debt.metadata, debt.metadataPath);
  const debtOutput = join(debt.root, 'rejected.json');
  assert.throws(() =>
    normalize({
      metadataPath: debt.metadataPath,
      zipDirectory: debt.zipRoot,
      outputPath: debtOutput,
      expectedSha: sha40,
      expectedNonce: nonce,
    }),
  );
  assert.equal(existsSync(debtOutput), false);
});

test('cleans partial publication after injected faults', () => {
  for (const faultPoint of ['after-temp', 'after-rename']) {
    const value = fixture('fault-' + faultPoint);
    const output = join(value.root, 'ci-run.json');
    assert.throws(() =>
      normalize({
        metadataPath: value.metadataPath,
        zipDirectory: value.zipRoot,
        outputPath: output,
        expectedSha: sha40,
        expectedNonce: nonce,
        faultPoint,
      }),
    );
    assert.equal(existsSync(output), false);
    assert.equal(
      readdirSync(value.root).some((entry) => entry.startsWith('.ci-run.json.tmp-')),
      false,
    );
    rmSync(value.root, { recursive: true, force: true });
  }
});
