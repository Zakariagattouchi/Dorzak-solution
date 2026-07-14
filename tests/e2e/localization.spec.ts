import { expect, test } from './fixtures/merchant';

async function enableArabic(page: import('@playwright/test').Page) {
  await page.goto('/config');
  await expect(page.getByLabel('Business / Store Name *')).toHaveValue('Dorzak E2E Merchant');
  await page.getByLabel('Interface Language').selectOption('ar');
  await expect(page.locator('html')).toHaveAttribute('lang', 'ar');
  await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');
  await page.getByRole('button', { name: 'حفظ التغييرات' }).click();
  await expect(page.getByText('تم حفظ الإعدادات بنجاح!')).toBeVisible();
}

test('switches the persisted interface to Arabic RTL and Qatari riyals', async ({ page }) => {
  await enableArabic(page);
  await page.getByRole('button', { name: 'العملة', exact: true }).click();
  await page.getByLabel('عملة المتجر').selectOption('QAR');
  await page.getByRole('button', { name: 'حفظ التغييرات' }).click();
  await expect(page.getByText('تم حفظ الإعدادات بنجاح!')).toBeVisible();
  await expect(page.locator('.business-context')).toContainText('QAR');
  await page.goto('/checkout');
  await expect(page.getByText('QAR 49.99').first()).toBeVisible();
  await page.goto('/config');
  await page.getByLabel('لغة الواجهة').selectOption('en');
  await expect(page.locator('html')).toHaveAttribute('dir', 'ltr');
});

test('translates every primary application section into Arabic', async ({ page }) => {
  await enableArabic(page);
  const routes = [
    ['/checkout', 'السلة فارغة'],
    ['/products', 'كتالوج المنتجات'],
    ['/products/create', 'بيانات المنتج الأساسية'],
    ['/categories', 'فئات المنتجات'],
    ['/orders', 'الطلبات'],
    ['/customers', 'إجمالي العملاء'],
    ['/sales', 'سجل معاملات المبيعات'],
    ['/finances', 'المالية والتدفق النقدي'],
    ['/analytics', 'التحليلات وتقارير الأعمال'],
    ['/catalog', 'تخصيص المتجر الإلكتروني'],
    ['/users', 'إدارة المستخدمين والموظفين'],
    ['/billing', 'الخطط والاشتراك'],
    ['/catalog/preview', 'هودي دورزاك القطني المميز'],
  ] as const;

  for (const [route, arabicText] of routes) {
    await page.goto(route);
    await expect(page.locator('main.app-content, main.store-content')).toContainText(arabicText);
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
