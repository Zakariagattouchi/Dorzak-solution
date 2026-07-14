import { expect, test } from '@playwright/test';
import { merchantEmail, merchantPassword } from './support/e2e';

test('a merchant signs in through the real UI', async ({ page }) => {
  await page.goto('/');
  await expect(page).toHaveURL(/\/login$/);
  await page.getByLabel('Email').fill(merchantEmail);
  await page.getByLabel('Password').fill(merchantPassword);
  await page.getByRole('button', { name: 'Sign In' }).click();
  await expect(page).toHaveURL(/\/checkout$/);
  await expect(page.getByRole('complementary', { name: 'Primary navigation' })).toBeVisible();
  await expect(page.locator('.business-context')).toContainText('QAR');
});
