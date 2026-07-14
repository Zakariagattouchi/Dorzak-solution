import { expect, test as base } from '@playwright/test';
import { backendUrl, merchantEmail, merchantPassword } from '../support/e2e';

type MerchantFixtures = { restoreCanonicalSettings: void };

export const test = base.extend<MerchantFixtures>({
  restoreCanonicalSettings: [
    async ({ request }, use) => {
      const login = await request.post(`${backendUrl}/api/v1/auth/login`, {
        data: { email: merchantEmail, password: merchantPassword, device_name: 'playwright-reset' },
      });
      expect(login.ok()).toBeTruthy();
      const token = ((await login.json()) as { data: { token: string } }).data.token;
      const headers = { Authorization: `Bearer ${token}`, Accept: 'application/json' };

      try {
        await use();
      } finally {
        const general = await request.put(`${backendUrl}/api/v1/settings/general`, {
          headers,
          data: {
            business_name: 'Dorzak E2E Merchant',
            tagline: 'Deterministic browser fixture',
            phone: null,
            whatsapp: null,
            language: 'en',
          },
        });
        const currency = await request.put(`${backendUrl}/api/v1/settings/currency`, {
          headers,
          data: { currency: 'QAR', symbol_placement: 'BEFORE' },
        });
        expect(general.ok()).toBeTruthy();
        expect(currency.ok()).toBeTruthy();
      }
    },
    { auto: true },
  ],
});

export { expect } from '@playwright/test';
