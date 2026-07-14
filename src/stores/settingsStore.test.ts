import { beforeEach, expect, test, vi } from 'vitest';
import { settingsApi } from '../api/endpoints';
import { initialAccountInfo } from '../data/mockData';
import { useSettingsStore } from './settingsStore';

vi.mock('../api/endpoints', () => ({
  settingsApi: { get: vi.fn(), update: vi.fn() },
}));

const envelope = (language: 'en' | 'ar', placement: 'BEFORE' | 'AFTER') => ({
  data: {
    general: { business_name: 'Dorzak', language },
    currency: { currency: 'QAR', currency_symbol: 'QAR', symbol_placement: placement },
  },
});

beforeEach(() => {
  localStorage.clear();
  vi.clearAllMocks();
  document.documentElement.lang = 'en';
  document.documentElement.dir = 'ltr';
  useSettingsStore.setState({ accountInfo: { ...initialAccountInfo }, loading: false });
});

test('hydrates server settings, direction, and grouped currency updates', async () => {
  vi.mocked(settingsApi.get).mockResolvedValue(envelope('ar', 'BEFORE'));
  await useSettingsStore.getState().fetchSettings();
  expect(useSettingsStore.getState().accountInfo.currency).toBe('QAR');
  expect(document.documentElement).toHaveAttribute('lang', 'ar');
  expect(document.documentElement).toHaveAttribute('dir', 'rtl');

  vi.mocked(settingsApi.update).mockResolvedValue(envelope('en', 'AFTER'));
  await useSettingsStore.getState().updateSettings({
    currency: 'QAR',
    symbolPlacement: 'AFTER',
  });
  expect(settingsApi.update).toHaveBeenCalledTimes(1);
  expect(settingsApi.update).toHaveBeenCalledWith('currency', {
    currency: 'QAR',
    symbol_placement: 'AFTER',
  });
  expect(document.documentElement).toHaveAttribute('lang', 'en');
  expect(document.documentElement).toHaveAttribute('dir', 'ltr');
});
