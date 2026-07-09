# 09 — Reconstruction Plan

## Phase Alignment & Step-by-Step Execution Plan

### Step 1: Project Setup & Foundation
- Initialize React + TypeScript + Vite project structure in `/Users/barsha/Documents/recover Kyte`.
- Configure `package.json` with dependencies (`react`, `react-dom`, `react-router-dom`, `zustand`, `lucide-react` / SVG icons).
- Implement design system tokens in `src/styles/tokens.css` and global styling in `src/styles/base.css`.

### Step 2: Core Architecture & App Shell
- Build `AppShell` layout with responsive left `Sidebar`, dynamic `Topbar`, `RouterOutlet`, `ModalHost`, `ToastHost`.
- Implement `navigation.ts` configuration driving the sidebar with active state matching.

### Step 3: Global State Management (Zustand)
- Build stores: `uiStore`, `modalStore`, `toastStore`, `cartStore`, `productStore`, `customerStore`, `orderStore`, `settingsStore`.

### Step 4: Reusable Component Library
- UI Components: `AppButton`, `IconButton`, `Badge`, `StatusPill`, `Loader`.
- Form Controls: `TextInput`, `NumberInput`, `SelectInput`, `CheckboxInput`, `ToggleSwitch`, `SearchInput`.
- Data Tables: `DataTable`, `TableHeader`, `TableRow`, `TableActions`, `Pagination`.
- Modals & Drawers: `BaseModal`, `ProductFormModal`, `CustomerFormModal`, `CategoryFormModal`, `PaymentModal`.

### Step 5: Page Views & Routing
- Reconstruct 9 connected SaaS pages: POS Checkout, Products, Categories, Orders, Customers, Reports, Storefront, Settings, Billing.

### Step 6: Mock API Client Layer
- Implement `src/api/mockApi.ts` fulfilling backend contracts extracted from HAR.

### Step 7: E2E Testing Suite (Playwright)
- Write E2E tests in `tests/e2e/` verifying navigation, forms, modals, table actions, cart calculations, and toast feedback.

### Step 8: Visual QA & Documentation Summary
- Verify pixel-perfect alignment against screenshots, generate `walkthrough.md` and `reconstruction-summary.md`.
