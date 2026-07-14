import { expect, test } from './fixtures/merchant';

test('redirects to checkout and renders the protected shell', async ({ page }) => {
  await page.goto('/');
  await expect(page).toHaveURL(/\/checkout$/);
  await expect(page.getByRole('complementary', { name: 'Primary navigation' })).toContainText(
    'Dorzak Merchant',
  );
});

test('navigates through every merchant route semantically', async ({ page }) => {
  await page.goto('/checkout');
  const routes = [
    ['Products', '/products', 'Products Catalog'],
    ['Orders', '/orders', 'Orders'],
    ['Online Catalog', '/catalog', 'Online Storefront Customizer'],
    ['Customers', '/customers', 'Customers'],
    ['Transactions', '/sales', 'Sales Transactions Log'],
    ['Finances', '/finances', 'Finances & Cash Flow'],
    ['Analytics', '/analytics', 'Analytics & Business Reports'],
    ['Users', '/users', 'Users & Staff Management'],
    ['Settings', '/config', 'General Store Settings'],
  ] as const;

  for (const [name, path, heading] of routes) {
    const link = page.getByRole('link', { name, exact: true });
    await link.click();
    await expect(page).toHaveURL(new RegExp(`${path}$`));
    await expect(link).toHaveAttribute('aria-current', 'page');
    await expect(page.getByRole('heading', { name: heading, exact: true })).toBeVisible();
  }
});
