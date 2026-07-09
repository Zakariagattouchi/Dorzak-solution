import { test, expect } from '@playwright/test';

test.describe('SaaS Global Navigation & App Shell', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('redirects to default Sell checkout view and renders sidebar', async ({ page }) => {
    await expect(page).toHaveURL(/\/checkout$/);
    const sidebar = page.locator('aside.app-sidebar');
    await expect(sidebar).toBeVisible();
    await expect(sidebar).toContainText('Dorzak Merchant');
  });

  test('navigates seamlessly across all connected Dorzak Merchant routes', async ({ page }) => {
    const routes = [
      { name: 'Products', path: '/products', heading: 'Products Catalog' },
      { name: 'Orders', path: '/orders', heading: 'Orders & Transactions' },
      { name: 'Online Catalog', path: '/catalog', heading: 'Online Storefront Customizer' },
      { name: 'Customers', path: '/customers', heading: 'Customer CRM' },
      { name: 'Transactions', path: '/sales', heading: 'Transactions' },
      { name: 'Finances', path: '/finances', heading: 'Finances' },
      { name: 'Analytics', path: '/analytics', heading: 'Reports' },
      { name: 'Users', path: '/users', heading: 'Users & Staff' },
      { name: 'Settings', path: '/config', heading: 'Business Settings' },
      { name: 'Sell', path: '/checkout', heading: 'Search products' }
    ];

    for (const r of routes) {
      await page.click(`aside.app-sidebar a:has-text("${r.name}")`);
      await expect(page).toHaveURL(new RegExp(`${r.path}$`));
      const activeLink = page.locator('aside.app-sidebar a.active');
      await expect(activeLink).toContainText(r.name);
    }
  });
});
