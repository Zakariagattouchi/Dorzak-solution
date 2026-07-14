import { defineConfig, devices } from '@playwright/test';
import { backendUrl, frontendUrl, storageStatePath } from './tests/e2e/support/e2e';

const required = (name: string): string => {
  const value = process.env[name];
  if (!value) throw new Error(`Missing required E2E input: ${name}`);
  return value;
};

const e2eSupervisorEnv = Object.fromEntries(
  [
    'P00_E2E_SUPERVISOR_DB_URL',
    'P00_E2E_SERVICE_LIFECYCLE_ID',
    'P00_E2E_SERVICE_ATTESTATION_PATH',
    'P00_E2E_SERVICE_ATTESTATION_SHA256',
    'P00_PG_IDENTITY',
    'P00_PG_INSTANCE_NONCE_SHA256',
  ].map((name) => [name, required(name)]),
);

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: false,
  forbidOnly: true,
  retries: 0,
  workers: 1,
  reporter: [['line'], ['json', { outputFile: 'test-results/results.json' }]],
  use: {
    baseURL: frontendUrl,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    {
      name: 'setup',
      testMatch: /auth\.setup\.ts/,
    },
    {
      name: 'chromium',
      dependencies: ['setup'],
      testIgnore: [/auth\.setup\.ts/, /auth\.smoke\.spec\.ts/],
      use: { ...devices['Desktop Chrome'], storageState: storageStatePath },
    },
    {
      name: 'login-smoke',
      testMatch: /auth\.smoke\.spec\.ts/,
      use: {
        ...devices['Desktop Chrome'],
        storageState: { cookies: [], origins: [] },
      },
    },
  ],
  webServer: [
    {
      command: 'php artisan e2e:serve --host=127.0.0.1 --port=8000 --no-interaction',
      cwd: './backend',
      url: `${backendUrl}/up`,
      reuseExistingServer: false,
      env: {
        ...e2eSupervisorEnv,
        APP_ENV: 'e2e',
        P00_E2E_PHASE: 'supervisor',
        APP_KEY: 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
        APP_URL: backendUrl,
        FRONTEND_URL: frontendUrl,
        CACHE_STORE: 'array',
        SESSION_DRIVER: 'array',
        QUEUE_CONNECTION: 'sync',
        MAIL_MAILER: 'array',
      },
    },
    {
      command: 'npm run dev -- --host 127.0.0.1 --strictPort',
      url: frontendUrl,
      reuseExistingServer: false,
    },
  ],
});
