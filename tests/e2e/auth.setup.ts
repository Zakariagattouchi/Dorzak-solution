import { expect, test as setup } from '@playwright/test';
import {
  backendUrl,
  frontendUrl,
  merchantEmail,
  merchantPassword,
  storageStatePath,
  tokenKey,
} from './support/e2e';

setup('authenticate the deterministic merchant', async ({ page, request }) => {
  const response = await request.post(`${backendUrl}/api/v1/auth/login`, {
    data: { email: merchantEmail, password: merchantPassword, device_name: 'playwright-setup' },
  });
  expect(response.ok()).toBeTruthy();
  const payload = (await response.json()) as { data: { token: string } };
  expect(payload.data.token).toMatch(/\|/);

  await page.goto(frontendUrl);
  await page.evaluate(
    ({ key, token }) => localStorage.setItem(key, token),
    { key: tokenKey, token: payload.data.token },
  );
  await page.goto('/checkout');
  await expect(page.getByRole('complementary', { name: 'Primary navigation' })).toBeVisible();
  await page.context().storageState({ path: storageStatePath });
});
