import { test, expect } from '@playwright/test';

test.describe('Interactive Frontend Controls & POS Flow', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/checkout');
  });

  test('adds product to cart and updates subtotal & total', async ({ page }) => {
    const productCard = page.locator('h5:has-text("Dorzak Signature Cotton Hoodie")');
    await productCard.click();

    const cartPanel = page.locator('.card').last();
    await expect(cartPanel).toContainText('Dorzak Signature Cotton Hoodie');

    const chargeButton = page.locator('button:has-text("Charge $49.99")');
    await expect(chargeButton).toBeVisible();
  });

  test('navigates to product creation page', async ({ page }) => {
    await page.click('button:has-text("Add Product")');
    await expect(page).toHaveURL(/\/products\/create$/);
    await expect(page.locator('h2')).toContainText('Add a product');
  });
});
