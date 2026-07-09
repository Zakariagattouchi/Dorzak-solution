import { create } from 'zustand';

interface UIState {
  sidebarCollapsed: boolean;
  searchQuery: string;
  toggleSidebar: () => void;
  setSearchQuery: (query: string) => void;
}

export const useUIStore = create<UIState>((set) => ({
  sidebarCollapsed: false,
  searchQuery: '',
  toggleSidebar: () => set((state) => ({ sidebarCollapsed: !state.sidebarCollapsed })),
  setSearchQuery: (searchQuery) => set({ searchQuery }),
}));
