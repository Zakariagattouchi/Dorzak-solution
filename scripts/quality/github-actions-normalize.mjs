import { createHash } from 'node:crypto';
import {
  existsSync,
  lstatSync,
  mkdirSync,
  mkdtempSync,
  readFileSync,
  readdirSync,
  realpathSync,
  renameSync,
  rmSync,
  writeFileSync,
} from 'node:fs';
import { basename, dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { inflateRawSync } from 'node:zlib';
import {
  aggregateRequiredGates,
  assertAggregate,
  contract,
  contractSha256,
  sha256,
  stableJson,
} from './p00.mjs';

const SHA40 = /^[0-9a-f]{40}$/;
const SHA64 = /^[0-9a-f]{64}$/;
const JOBS = contract.jobs.map((job) => job.name);
const JOB_SET = new Set(JOBS);
const ARTIFACTS = [...JOBS, 'required-gates'];
const MAX_ENTRIES = 128;
const MAX_ENTRY_BYTES = 64 * 1024 * 1024;
const MAX_TOTAL_BYTES = 128 * 1024 * 1024;
const MAX_METADATA_BYTES = 1024 * 1024;

function invariant(condition, message) {
  if (!condition) throw new Error(message);
}

function exactKeys(value, keys, label) {
  invariant(
    value !== null && typeof value === 'object' && !Array.isArray(value),
    label + ' must be an object',
  );
  invariant(
    JSON.stringify(Object.keys(value).sort()) === JSON.stringify([...keys].sort()),
    label + ' keys are not closed',
  );
}

function readJson(path) {
  return JSON.parse(readFileSync(path, 'utf8'));
}

const crcTable = Array.from({ length: 256 }, (_, value) => {
  let crc = value;
  for (let bit = 0; bit < 8; bit += 1) crc = (crc >>> 1) ^ (crc & 1 ? 0xedb88320 : 0);
  return crc >>> 0;
});

export function crc32(bytes) {
  let crc = 0xffffffff;
  for (const byte of bytes) crc = (crc >>> 8) ^ crcTable[(crc ^ byte) & 0xff];
  return (crc ^ 0xffffffff) >>> 0;
}

function safeArchivePath(name) {
  invariant(
    typeof name === 'string' && name.length > 0 && name.length <= 240,
    'Unsafe ZIP entry length',
  );
  invariant(
    !name.includes('\\') && !name.includes('\0') && !name.startsWith('/'),
    'Unsafe ZIP entry path',
  );
  invariant(/^[A-Za-z0-9._/-]+$/.test(name), 'ZIP entry has unsupported characters');
  const directory = name.endsWith('/');
  const body = directory ? name.slice(0, -1) : name;
  const parts = body.split('/');
  invariant(
    parts.length > 0 && parts.every((part) => part !== '' && part !== '.' && part !== '..'),
    'ZIP entry escapes root',
  );
  return { directory, path: parts.join('/') };
}

function findEndOfCentralDirectory(bytes) {
  const minimum = Math.max(0, bytes.length - 22 - 65535);
  for (let offset = bytes.length - 22; offset >= minimum; offset -= 1) {
    if (bytes.readUInt32LE(offset) !== 0x06054b50) continue;
    const commentLength = bytes.readUInt16LE(offset + 20);
    if (offset + 22 + commentLength === bytes.length) return offset;
  }
  throw new Error('ZIP end-of-central-directory is missing');
}

function containsZip64Extra(bytes, offset, length) {
  const end = offset + length;
  invariant(end <= bytes.length, 'ZIP extra-field boundary is invalid');
  let cursor = offset;
  while (cursor < end) {
    invariant(cursor + 4 <= end, 'ZIP extra-field header is truncated');
    const id = bytes.readUInt16LE(cursor);
    const size = bytes.readUInt16LE(cursor + 2);
    cursor += 4;
    invariant(cursor + size <= end, 'ZIP extra-field payload is truncated');
    if (id === 0x0001) return true;
    cursor += size;
  }
  invariant(cursor === end, 'ZIP extra-field boundary is invalid');
  return false;
}

export function inspectZip(bytes) {
  invariant(Buffer.isBuffer(bytes) && bytes.length >= 22, 'ZIP bytes are invalid');
  const eocd = findEndOfCentralDirectory(bytes);
  const disk = bytes.readUInt16LE(eocd + 4);
  const centralDisk = bytes.readUInt16LE(eocd + 6);
  const diskEntries = bytes.readUInt16LE(eocd + 8);
  const totalEntries = bytes.readUInt16LE(eocd + 10);
  const centralSize = bytes.readUInt32LE(eocd + 12);
  const centralOffset = bytes.readUInt32LE(eocd + 16);
  invariant(
    disk === 0 && centralDisk === 0 && diskEntries === totalEntries,
    'Multi-disk ZIP is prohibited',
  );
  invariant(totalEntries > 0 && totalEntries <= MAX_ENTRIES, 'ZIP entry count is invalid');
  invariant(
    totalEntries !== 0xffff && centralSize !== 0xffffffff && centralOffset !== 0xffffffff,
    'ZIP64 is prohibited',
  );
  invariant(centralOffset + centralSize === eocd, 'ZIP central directory boundary is invalid');
  const entries = [];
  const names = new Set();
  let cursor = centralOffset;
  let totalUncompressed = 0;
  for (let index = 0; index < totalEntries; index += 1) {
    invariant(bytes.readUInt32LE(cursor) === 0x02014b50, 'ZIP central entry signature is invalid');
    const madeBy = bytes.readUInt16LE(cursor + 4);
    const flags = bytes.readUInt16LE(cursor + 8);
    const method = bytes.readUInt16LE(cursor + 10);
    const expectedCrc32 = bytes.readUInt32LE(cursor + 16);
    const compressedSize = bytes.readUInt32LE(cursor + 20);
    const uncompressedSize = bytes.readUInt32LE(cursor + 24);
    const nameLength = bytes.readUInt16LE(cursor + 28);
    const extraLength = bytes.readUInt16LE(cursor + 30);
    const commentLength = bytes.readUInt16LE(cursor + 32);
    const startDisk = bytes.readUInt16LE(cursor + 34);
    const externalAttributes = bytes.readUInt32LE(cursor + 38);
    const localOffset = bytes.readUInt32LE(cursor + 42);
    const end = cursor + 46 + nameLength + extraLength + commentLength;
    invariant(end <= eocd && startDisk === 0, 'ZIP central entry boundary is invalid');
    invariant((flags & 0x0001) === 0, 'Encrypted ZIP entries are prohibited');
    invariant(method === 0 || method === 8, 'ZIP compression method is unsupported');
    invariant(
      compressedSize !== 0xffffffff &&
        uncompressedSize !== 0xffffffff &&
        localOffset !== 0xffffffff,
      'ZIP64 entry is prohibited',
    );
    invariant(
      !containsZip64Extra(bytes, cursor + 46 + nameLength, extraLength),
      'ZIP64 extra field is prohibited',
    );
    invariant(uncompressedSize <= MAX_ENTRY_BYTES, 'ZIP entry is too large');
    totalUncompressed += uncompressedSize;
    invariant(totalUncompressed <= MAX_TOTAL_BYTES, 'ZIP payload is too large');
    const name = bytes.subarray(cursor + 46, cursor + 46 + nameLength).toString('utf8');
    const safe = safeArchivePath(name);
    invariant(!names.has(safe.path), 'Duplicate ZIP path is prohibited');
    names.add(safe.path);
    const host = madeBy >>> 8;
    const mode = externalAttributes >>> 16;
    if (host === 3 && mode !== 0) {
      const type = mode & 0o170000;
      invariant(
        safe.directory ? type === 0o040000 : type === 0o100000,
        'ZIP symlink or special file is prohibited',
      );
    } else {
      const dosDirectory = (externalAttributes & 0x10) !== 0;
      invariant(dosDirectory === safe.directory, 'ZIP file type is ambiguous');
    }
    entries.push({
      ...safe,
      compressedSize,
      uncompressedSize,
      expectedCrc32,
      flags,
      method,
      localOffset,
      centralOffset,
    });
    cursor = end;
  }
  invariant(cursor === eocd, 'ZIP central directory has trailing data');
  return entries;
}

export function extractZip(zipPath, destination) {
  const zipStat = lstatSync(zipPath);
  invariant(
    zipStat.isFile() && !zipStat.isSymbolicLink() && realpathSync(zipPath) === resolve(zipPath),
    'ZIP path is unsafe',
  );
  invariant(!existsSync(destination), 'ZIP destination already exists');
  const bytes = readFileSync(zipPath);
  const entries = inspectZip(bytes);
  mkdirSync(destination, { recursive: false, mode: 0o700 });
  for (const entry of entries) {
    invariant(
      entry.localOffset + 30 <= entry.centralOffset,
      'ZIP local header boundary is invalid',
    );
    invariant(
      bytes.readUInt32LE(entry.localOffset) === 0x04034b50,
      'ZIP local entry signature is invalid',
    );
    const localFlags = bytes.readUInt16LE(entry.localOffset + 6);
    const localMethod = bytes.readUInt16LE(entry.localOffset + 8);
    const localCrc32 = bytes.readUInt32LE(entry.localOffset + 14);
    const localCompressedSize = bytes.readUInt32LE(entry.localOffset + 18);
    const localUncompressedSize = bytes.readUInt32LE(entry.localOffset + 22);
    const localNameLength = bytes.readUInt16LE(entry.localOffset + 26);
    const localExtraLength = bytes.readUInt16LE(entry.localOffset + 28);
    const localHeaderEnd = entry.localOffset + 30 + localNameLength + localExtraLength;
    invariant(localHeaderEnd <= entry.centralOffset, 'ZIP local metadata boundary is invalid');
    const localName = bytes
      .subarray(entry.localOffset + 30, entry.localOffset + 30 + localNameLength)
      .toString('utf8');
    invariant(
      localName === (entry.directory ? entry.path + '/' : entry.path),
      'ZIP local/central name mismatch',
    );
    invariant(
      localFlags === entry.flags && localMethod === entry.method,
      'ZIP local/central metadata mismatch',
    );
    invariant(
      !containsZip64Extra(bytes, entry.localOffset + 30 + localNameLength, localExtraLength),
      'ZIP64 local extra field is prohibited',
    );
    if ((localFlags & 0x0008) === 0) {
      invariant(
        localCrc32 === entry.expectedCrc32 &&
          localCompressedSize === entry.compressedSize &&
          localUncompressedSize === entry.uncompressedSize,
        'ZIP local/central size or CRC mismatch',
      );
    }
    const dataStart = localHeaderEnd;
    const dataEnd = dataStart + entry.compressedSize;
    invariant(dataEnd <= entry.centralOffset, 'ZIP compressed data boundary is invalid');
    const output = resolve(destination, entry.path);
    invariant(output.startsWith(resolve(destination) + '/'), 'ZIP output escapes destination');
    if (entry.directory) {
      mkdirSync(output, { recursive: true, mode: 0o700 });
      continue;
    }
    mkdirSync(dirname(output), { recursive: true, mode: 0o700 });
    const compressed = bytes.subarray(dataStart, dataEnd);
    const content =
      entry.method === 0
        ? Buffer.from(compressed)
        : inflateRawSync(compressed, { maxOutputLength: Math.max(1, entry.uncompressedSize) });
    invariant(content.length === entry.uncompressedSize, 'ZIP uncompressed size mismatch');
    invariant(crc32(content) === entry.expectedCrc32, 'ZIP CRC mismatch');
    writeFileSync(output, content, { flag: 'wx', mode: 0o600 });
  }
  return entries;
}

function assertClosedTree(root) {
  const canonicalRoot = realpathSync(root);
  const visit = (directory) => {
    for (const name of readdirSync(directory)) {
      const path = join(directory, name);
      const stat = lstatSync(path);
      invariant(!stat.isSymbolicLink(), 'Extracted symlink is prohibited');
      invariant(
        realpathSync(path).startsWith(canonicalRoot + '/') || realpathSync(path) === canonicalRoot,
        'Extracted path escapes root',
      );
      if (stat.isDirectory()) visit(path);
      else invariant(stat.isFile(), 'Extracted special file is prohibited');
    }
  };
  visit(canonicalRoot);
}

function validateMetadata(metadata, expectedSha, expectedNonce) {
  exactKeys(
    metadata,
    [
      'schemaVersion',
      'repository',
      'integrationBranch',
      'workflowPath',
      'evidenceNonce',
      'run',
      'jobs',
      'artifacts',
      'checks',
    ],
    '$.metadata',
  );
  invariant(metadata.schemaVersion === 1, 'Metadata schema mismatch');
  invariant(metadata.repository === 'Zakariagattouchi/Dorzak-solution', 'Repository mismatch');
  invariant(metadata.integrationBranch === 'main', 'Integration branch mismatch');
  invariant(
    metadata.workflowPath === '.github/workflows/p00-quality.yml',
    'Workflow path mismatch',
  );
  invariant(
    metadata.evidenceNonce === expectedNonce && SHA64.test(expectedNonce),
    'Evidence nonce mismatch',
  );
  exactKeys(
    metadata.run,
    [
      'id',
      'attempt',
      'event',
      'status',
      'conclusion',
      'headSha',
      'headBranch',
      'path',
      'displayTitle',
    ],
    '$.metadata.run',
  );
  invariant(Number.isSafeInteger(metadata.run.id) && metadata.run.id > 0, 'Run ID is invalid');
  invariant(
    metadata.run.attempt === 1 && metadata.run.event === 'workflow_dispatch',
    'Run is not a new workflow dispatch',
  );
  invariant(
    metadata.run.status === 'completed' && metadata.run.conclusion === 'success',
    'Run did not pass',
  );
  invariant(metadata.run.headSha === expectedSha && SHA40.test(expectedSha), 'Run SHA mismatch');
  invariant(metadata.run.headBranch === 'main', 'Run branch mismatch');
  invariant(
    metadata.run.path === '.github/workflows/p00-quality.yml',
    'Run workflow path mismatch',
  );
  invariant(metadata.run.displayTitle === 'p00-' + expectedNonce, 'Run title/nonce mismatch');

  invariant(
    Array.isArray(metadata.jobs) && metadata.jobs.length === 7,
    'Provider job count mismatch',
  );
  const jobNames = metadata.jobs.map((job) => job.name);
  invariant(
    JSON.stringify([...jobNames].sort()) === JSON.stringify([...ARTIFACTS].sort()),
    'Provider job names mismatch',
  );
  invariant(new Set(jobNames).size === jobNames.length, 'Provider jobs are duplicated');
  invariant(
    new Set(metadata.jobs.map((job) => job.id)).size === metadata.jobs.length,
    'Provider job IDs are duplicated',
  );
  for (const job of metadata.jobs) {
    exactKeys(
      job,
      ['id', 'name', 'status', 'conclusion', 'headSha', 'runAttempt', 'runnerName', 'labels'],
      '$.metadata.jobs[]',
    );
    invariant(Number.isSafeInteger(job.id) && job.id > 0, 'Provider job ID is invalid');
    invariant(
      job.status === 'completed' && job.conclusion === 'success',
      'Provider job did not pass: ' + job.name,
    );
    invariant(
      job.headSha === expectedSha && job.runAttempt === 1,
      'Provider job identity mismatch: ' + job.name,
    );
    invariant(
      typeof job.runnerName === 'string' && job.runnerName.length > 0,
      'Provider runner name is missing',
    );
    invariant(
      Array.isArray(job.labels) && job.labels.includes('ubuntu-24.04'),
      'Provider runner label mismatch',
    );
  }

  invariant(
    Array.isArray(metadata.artifacts) && metadata.artifacts.length === 7,
    'Provider artifact count mismatch',
  );
  const artifactNames = metadata.artifacts.map((artifact) => artifact.name);
  invariant(
    JSON.stringify([...artifactNames].sort()) === JSON.stringify([...ARTIFACTS].sort()),
    'Provider artifact names mismatch',
  );
  invariant(
    new Set(artifactNames).size === artifactNames.length,
    'Provider artifacts are duplicated',
  );
  invariant(
    new Set(metadata.artifacts.map((artifact) => artifact.id)).size === metadata.artifacts.length,
    'Provider artifact IDs are duplicated',
  );
  for (const artifact of metadata.artifacts) {
    exactKeys(
      artifact,
      ['id', 'name', 'sizeInBytes', 'expired', 'digest', 'runId', 'headSha'],
      '$.metadata.artifacts[]',
    );
    invariant(Number.isSafeInteger(artifact.id) && artifact.id > 0, 'Artifact ID is invalid');
    invariant(
      Number.isSafeInteger(artifact.sizeInBytes) &&
        artifact.sizeInBytes > 0 &&
        artifact.sizeInBytes <= MAX_TOTAL_BYTES,
      'Artifact size is invalid',
    );
    invariant(
      artifact.expired === false && /^sha256:[0-9a-f]{64}$/.test(artifact.digest),
      'Artifact digest/expiry is invalid',
    );
    invariant(
      artifact.runId === metadata.run.id && artifact.headSha === expectedSha,
      'Artifact run identity mismatch',
    );
  }

  invariant(
    Array.isArray(metadata.checks) && metadata.checks.length === 1,
    'Required check is ambiguous',
  );
  const check = metadata.checks[0];
  exactKeys(
    check,
    ['id', 'name', 'status', 'conclusion', 'headSha', 'appId', 'appSlug'],
    '$.metadata.checks[0]',
  );
  invariant(
    Number.isSafeInteger(check.id) &&
      check.id > 0 &&
      Number.isSafeInteger(check.appId) &&
      check.appId > 0,
    'Required check identity is invalid',
  );
  invariant(
    check.name === 'required-gates' &&
      check.status === 'completed' &&
      check.conclusion === 'success',
    'Required check did not pass',
  );
  invariant(
    check.headSha === expectedSha && check.appSlug === 'github-actions',
    'Required check app/SHA mismatch',
  );
}

function validatePostgresqlObservation(observation, aggregate) {
  exactKeys(
    observation,
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
  invariant(
    observation.kind === 'oci' && observation.identity === aggregate.inputs.postgresql.identity,
    'PostgreSQL identity mismatch',
  );
  invariant(
    /^docker\.io\/library\/postgres@sha256:[0-9a-f]{64}$/.test(observation.identity),
    'PostgreSQL identity is mutable',
  );
  invariant(
    SHA64.test(observation.attestationSha256) &&
      SHA64.test(observation.instanceNonceSha256) &&
      SHA64.test(observation.endpointSha256),
    'PostgreSQL observation hash is invalid',
  );
  invariant(
    Number.isInteger(observation.serverVersionNum) &&
      observation.serverVersionNum >= 160000 &&
      observation.serverVersionNum < 170000,
    'PostgreSQL major is not 16',
  );
  invariant(
    typeof observation.databaseName === 'string' && /_test$/.test(observation.databaseName),
    'PostgreSQL database is not a test candidate',
  );
  const reference = aggregate.jobs.find((job) => job.job === 'postgresql-16')?.artifacts
    ?.postgresqlIdentity;
  invariant(
    reference?.sha256 === sha256(stableJson(observation)),
    'PostgreSQL observation is not bound to its job artifact',
  );
}

export function normalize({
  metadataPath,
  zipDirectory,
  outputPath,
  expectedSha,
  expectedNonce,
  faultPoint = null,
}) {
  invariant([null, 'after-temp', 'after-rename'].includes(faultPoint), 'Unknown fault point');
  invariant(resolve(outputPath) === outputPath, 'Normalizer output path must be absolute');
  invariant(!existsSync(outputPath), 'Normalizer output already exists');
  const metadataStat = lstatSync(metadataPath);
  invariant(
    metadataStat.isFile() &&
      !metadataStat.isSymbolicLink() &&
      realpathSync(metadataPath) === resolve(metadataPath),
    'Metadata path is unsafe',
  );
  invariant(
    metadataStat.size > 0 && metadataStat.size <= MAX_METADATA_BYTES,
    'Metadata file size is invalid',
  );
  const metadata = readJson(metadataPath);
  validateMetadata(metadata, expectedSha, expectedNonce);
  const zipDirectoryStat = lstatSync(zipDirectory);
  invariant(
    zipDirectoryStat.isDirectory() && !zipDirectoryStat.isSymbolicLink(),
    'ZIP directory is unsafe',
  );
  const canonicalZipDirectory = realpathSync(zipDirectory);
  invariant(canonicalZipDirectory === resolve(zipDirectory), 'ZIP directory is unsafe');
  const zipNames = readdirSync(canonicalZipDirectory).sort();
  invariant(
    JSON.stringify(zipNames) === JSON.stringify(ARTIFACTS.map((name) => name + '.zip').sort()),
    'ZIP directory is not closed',
  );
  const parent = resolve(dirname(outputPath));
  mkdirSync(parent, { recursive: true });
  invariant(realpathSync(parent) === parent, 'Normalizer output parent is unsafe');
  const publication = mkdtempSync(join(parent, '.' + basename(outputPath) + '.tmp-'));
  let published = false;
  try {
    const extractedRoot = join(publication, 'artifacts');
    mkdirSync(extractedRoot, { mode: 0o700 });
    for (const artifact of metadata.artifacts) {
      const zipPath = join(canonicalZipDirectory, artifact.name + '.zip');
      const stat = lstatSync(zipPath);
      invariant(
        stat.isFile() && !stat.isSymbolicLink() && realpathSync(zipPath) === zipPath,
        'Artifact ZIP is unsafe',
      );
      invariant(
        stat.size === artifact.sizeInBytes,
        'Provider artifact size mismatch: ' + artifact.name,
      );
      invariant(
        'sha256:' + createHash('sha256').update(readFileSync(zipPath)).digest('hex') ===
          artifact.digest,
        'Provider artifact digest mismatch: ' + artifact.name,
      );
      extractZip(zipPath, join(extractedRoot, artifact.name));
      assertClosedTree(join(extractedRoot, artifact.name));
    }
    const jobsRoot = join(publication, 'jobs');
    mkdirSync(jobsRoot, { mode: 0o700 });
    for (const job of JOBS) renameSync(join(extractedRoot, job), join(jobsRoot, job));
    const requiredRoot = join(extractedRoot, 'required-gates');
    invariant(
      JSON.stringify(readdirSync(requiredRoot)) === JSON.stringify(['required-gates.json']),
      'Required aggregate artifact is not closed',
    );
    const aggregate = aggregateRequiredGates(jobsRoot);
    assertAggregate(aggregate, 'ci');
    invariant(
      readFileSync(join(requiredRoot, 'required-gates.json'), 'utf8') === stableJson(aggregate),
      'Downloaded aggregate differs from recomputation',
    );
    const observation = readJson(join(jobsRoot, 'postgresql-16', 'postgresql-identity.json'));
    validatePostgresqlObservation(observation, aggregate);
    const normalized = {
      schemaVersion: 1,
      provider: 'github-actions',
      runId: String(metadata.run.id),
      attempt: 1,
      integratedSha: expectedSha,
      contractSha256,
      inputFingerprintSha256: aggregate.inputFingerprintSha256,
      inputs: aggregate.inputs,
      platformObservationFingerprintSha256: aggregate.platformObservationFingerprintSha256,
      platformObservation: aggregate.platformObservation,
      postgresqlObservation: observation,
      requiredGate: { status: 'passed', jobs: 6 },
      jobs: aggregate.jobs,
    };
    const temporaryOutput = join(publication, basename(outputPath));
    writeFileSync(temporaryOutput, stableJson(normalized), { flag: 'wx', mode: 0o600 });
    if (faultPoint === 'after-temp') throw new Error('Injected normalizer fault after temp');
    renameSync(temporaryOutput, outputPath);
    published = true;
    if (faultPoint === 'after-rename') throw new Error('Injected normalizer fault after rename');
    return normalized;
  } finally {
    if (faultPoint === 'after-rename' && existsSync(outputPath))
      rmSync(outputPath, { force: true });
    rmSync(publication, { recursive: true, force: true });
    if (!published && existsSync(outputPath)) rmSync(outputPath, { force: true });
  }
}

if (process.argv[1] && resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  const [metadataPath, zipDirectory, outputPath, expectedSha, expectedNonce] =
    process.argv.slice(2);
  invariant(
    metadataPath && zipDirectory && outputPath && expectedSha && expectedNonce,
    'usage: github-actions-normalize.mjs METADATA ZIP_DIR OUTPUT SHA NONCE',
  );
  const value = normalize({
    metadataPath: resolve(metadataPath),
    zipDirectory: resolve(zipDirectory),
    outputPath: resolve(outputPath),
    expectedSha,
    expectedNonce,
  });
  process.stdout.write(
    'P00_GITHUB_NORMALIZE PASS run=' + value.runId + ' sha=' + value.integratedSha + '\n',
  );
}
