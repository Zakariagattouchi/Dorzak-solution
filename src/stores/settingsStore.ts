import { create } from 'zustand';
import { initialAccountInfo } from '../data/mockData';
import { settingsApi } from '../api/endpoints';
import { toAccountInfo, settingsGroupPayloads } from '../api/adapters';

const storageKey = 'dorzak-merchant-lang';
type Account = typeof initialAccountInfo;

const persistedLang = (): 'en' | 'ar' => {
  try { return (localStorage.getItem(storageKey) as 'en' | 'ar') || 'en'; } catch { return 'en'; }
};

const applyDir = (language: 'en' | 'ar') => {
  document.documentElement.lang = language;
  document.documentElement.dir = language === 'ar' ? 'rtl' : 'ltr';
};

interface SettingsState {
  accountInfo: Account;
  loading: boolean;
  fetchSettings: () => Promise<void>;
  updateSettings: (updates: Partial<Account>) => Promise<void>;
  setLanguage: (language: 'en' | 'ar') => void;
  toggleOnlineStore: () => Promise<void>;
}

export const useSettingsStore = create<SettingsState>((set, get) => ({
  accountInfo: { ...initialAccountInfo, language: persistedLang() },
  loading: false,

  fetchSettings: async () => {
    set({ loading: true });
    try {
      const res: any = await settingsApi.get();
      const mapped = toAccountInfo(res.data);
      const accountInfo = { ...initialAccountInfo, ...mapped } as Account;
      applyDir(accountInfo.language);
      set({ accountInfo, loading: false });
    } catch {
      set({ loading: false });
    }
  },

  updateSettings: async (updates) => {
    const merged = { ...get().accountInfo, ...updates } as Account;
    const groups = settingsGroupPayloads(merged, updates);

    let envelope: any = null;
    for (const [group, payload] of groups) {
      const res: any = await settingsApi.update(group, payload);
      envelope = res.data;
    }

    const accountInfo = envelope
      ? ({ ...initialAccountInfo, ...toAccountInfo(envelope) } as Account)
      : merged;
    applyDir(accountInfo.language);
    set({ accountInfo });
  },

  setLanguage: (language) => {
    applyDir(language);
    try { localStorage.setItem(storageKey, language); } catch { /* ignore */ }
    set((s) => ({ accountInfo: { ...s.accountInfo, language } }));
  },

  toggleOnlineStore: async () => {
    await get().updateSettings({ onlineStoreEnabled: !get().accountInfo.onlineStoreEnabled });
  },
}));
