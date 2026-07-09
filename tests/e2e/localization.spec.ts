import { test, expect } from '@playwright/test';

async function enableArabic(page: import('@playwright/test').Page) {
  await page.goto('/config');
  await page.locator('select:has(option[value="ar"])').selectOption('ar');
  await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');
}

test('switches the full interface to Arabic RTL and uses Qatari riyals', async ({ page }) => {
  await enableArabic(page);
  await expect(page.locator('html')).toHaveAttribute('lang', 'ar');
  await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');
  await expect(page.locator('body')).toHaveCSS('font-family', /Araboto/);
  await expect(page.locator('aside.app-sidebar')).toContainText('العمليات');

  await page.getByRole('button', { name: 'العملة' }).click();
  await page.locator('select:has(option[value="QAR"])').selectOption('QAR');
  await expect(page.getByText('QAR 49.99')).toBeVisible();

  await page.getByRole('button', { name: 'حفظ التغييرات' }).click();
  await expect(page.getByText('تم حفظ الإعدادات بنجاح!')).toBeVisible();
  expect(await page.evaluate(() => JSON.parse(localStorage.getItem('dorzak-merchant-settings') || '{}').currency)).toBe('QAR');
  await expect(page.locator('.business-context')).toContainText('QAR');
  await page.locator('a[href="/checkout"]').click();
  await expect(page.getByText('QAR 49.99')).toBeVisible();

  await page.locator('a[href="/config"]').click();
  await page.locator('select:has(option[value="en"])').selectOption('en');
  await expect(page.locator('html')).toHaveAttribute('dir', 'ltr');
  await expect(page.locator('aside.app-sidebar')).toContainText('Operations');
});

test('translates every primary application section into Arabic', async ({ page }) => {
  await enableArabic(page);

  const routes = [
    ['/checkout', 'السلة فارغة'],
    ['/products', 'كتالوج المنتجات'],
    ['/products/create', 'بيانات المنتج الأساسية'],
    ['/categories', 'فئات المنتجات'],
    ['/orders', 'سجل الطلبات والمبيعات'],
    ['/customers', 'إجمالي العملاء'],
    ['/sales', 'سجل معاملات المبيعات'],
    ['/finances', 'المالية والتدفق النقدي'],
    ['/analytics', 'التحليلات وتقارير الأعمال'],
    ['/catalog', 'تخصيص المتجر الإلكتروني'],
    ['/users', 'إدارة المستخدمين والموظفين'],
    ['/billing', 'الخطط والاشتراك'],
    ['/catalog/preview', 'كل المنتجات'],
  ] as const;

  for (const [route, arabicText] of routes) {
    await page.goto(route);
    await expect(page.locator('body')).toContainText(arabicText);
    await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');
  }
});

test('translates every Settings subsection into Arabic', async ({ page }) => {
  await enableArabic(page);

  const tabs = [
    ['بيانات النشاط', 'بيانات النشاط التجاري'],
    ['العملة', 'العملة والتنسيق'],
    ['الضرائب', 'إعداد ضريبة المبيعات'],
    ['الإيصالات', 'تخصيص الإيصال'],
    ['المدفوعات', 'طرق الدفع'],
    ['التكاملات', 'تكاملات الجهات الخارجية'],
    ['المستخدمون والموظفون', 'إدارة المستخدمين والموظفين'],
    ['الاشتراك', 'الاشتراك والخطة'],
  ] as const;

  for (const [tab, arabicHeading] of tabs) {
    await page.getByRole('button', { name: tab, exact: true }).click();
    await expect(page.locator('main')).toContainText(arabicHeading);
  }
});
