import { describe, expect, test } from 'vitest';
import { initialAccountInfo } from '../data/mockData';
import { settingsGroupPayloads, toOrder, toProduct } from './adapters';

describe('API adapters', () => {
  test('normalizes DTO numbers, media, QAR orders, and settings payloads', () => {
    expect(toProduct({ id: 7, name: 'Hoodie', price: '49.99', cost: null, stock: '10', min_stock: 2, track_stock: true, image_url: '/storage/a.jpg' })).toMatchObject({
      id: '7', price: 49.99, cost: 0, stock: 10, imageUrl: '/storage/a.jpg',
    });
    expect(toOrder({ id: 2, order_number: 'ORD-1000', customer_name: 'Walk-in', subtotal: '49.99', discount: 0, tax_amount: 0, total: '49.99', status: 'COMPLETE', payment_method: 'CASH', currency_code: 'QAR', items: [] })).toMatchObject({
      id: 'ORD-1000', total: 49.99, currencyCode: 'QAR',
    });
    expect(settingsGroupPayloads(
      { ...initialAccountInfo, currency: 'QAR', symbolPlacement: 'BEFORE' },
      { currency: 'QAR' },
    )).toEqual([['currency', { currency: 'QAR', symbol_placement: 'BEFORE' }]]);
  });
});
