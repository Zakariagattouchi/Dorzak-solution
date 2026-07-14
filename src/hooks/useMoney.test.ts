import { expect, test } from 'vitest';
import { formatMoney } from './useMoney';

test('formats placement, overrides, and fraction digits deterministically', () => {
  const before = {
    currency: 'QAR',
    currencySymbol: 'QAR',
    symbolPlacement: 'BEFORE',
  } as const;
  expect(formatMoney(49.99, before)).toBe('QAR 49.99');
  expect(formatMoney(49.99, { ...before, symbolPlacement: 'AFTER' })).toBe('49.99 QAR');
  expect(formatMoney(49.99, before, 2, 'USD')).toBe('$49.99');
  expect(formatMoney(49.99, before, 0)).toBe('QAR 50');
});
