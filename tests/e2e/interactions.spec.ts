import { expect, test } from './fixtures/merchant';

test.beforeEach(async ({ page }) => page.goto('/checkout'));

test('selects a hoodie variant and charges in QAR', async ({ page }) => {
  await page.getByRole('button', { name: 'Choose Dorzak Signature Cotton Hoodie' }).click();
  const dialog = page.getByRole('dialog', { name: 'Choose Dorzak Signature Cotton Hoodie options' });
  await dialog.getByRole('button', { name: 'Small', exact: true }).click();
  await dialog.getByRole('button', { name: 'Black', exact: true }).click();
  await dialog.getByRole('button', { name: 'Add to Cart • QAR 49.99' }).click();
  await expect(page.getByText('Dorzak Signature Cotton Hoodie').last()).toBeVisible();
  await expect(page.getByRole('button', { name: 'Charge QAR 49.99' })).toBeEnabled();
});

test('opens the current product creation dialog', async ({ page }) => {
  const posAddProduct = page
    .getByRole('main')
    .getByRole('button', { name: 'Add Product', exact: true });
  await expect(posAddProduct).toHaveCount(1);
  await posAddProduct.click();
  await expect(page.getByRole('dialog', { name: 'Create Production Product' })).toBeVisible();
  await expect(page).toHaveURL(/\/checkout$/);
});
