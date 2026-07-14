import React from 'react';
import { createBrowserRouter, Navigate } from 'react-router-dom';
import { AppShell } from '../layouts/AppShell';
import { POSPage } from '../pages/pos/POSPage';
import { ProductsPage } from '../pages/products/ProductsPage';
import { ProductCreatePage } from '../pages/products/ProductCreatePage';
import { CategoriesPage } from '../pages/categories/CategoriesPage';
import { OrdersPage } from '../pages/orders/OrdersPage';
import { CustomersPage } from '../pages/customers/CustomersPage';
import { SalesPage } from '../pages/sales/SalesPage';
import { FinancesPage } from '../pages/finances/FinancesPage';
import { ReportsPage } from '../pages/reports/ReportsPage';
import { StorefrontPage } from '../pages/storefront/StorefrontPage';
import { StorefrontPreviewPage } from '../pages/storefront/StorefrontPreviewPage';
import { OrderStatusPage } from '../pages/storefront/OrderStatusPage';
import { SettingsPage } from '../pages/settings/SettingsPage';
import { MarketingPage } from '../pages/marketing/MarketingPage';
import { UsersPage } from '../pages/users/UsersPage';
import { BillingPage } from '../pages/billing/BillingPage';
import { LoginPage } from '../pages/auth/LoginPage';
import { SignupPage } from '../pages/auth/SignupPage';
import { PlatformShell } from '../layouts/PlatformShell';
import { PlatformPage } from '../pages/platform/PlatformPage';

export const router = createBrowserRouter([
  {
    path: '/',
    element: <AppShell />,
    children: [
      { index: true, element: <Navigate to="/checkout" replace /> },
      { path: 'checkout', element: <POSPage /> },
      { path: 'products', element: <ProductsPage /> },
      { path: 'products/create', element: <ProductCreatePage /> },
      { path: 'products/:id/edit', element: <ProductCreatePage /> },
      { path: 'categories', element: <CategoriesPage /> },
      { path: 'catalog', element: <StorefrontPage /> },
      { path: 'orders', element: <OrdersPage /> },
      { path: 'customers', element: <CustomersPage /> },
      { path: 'marketing', element: <MarketingPage /> },
      { path: 'sales', element: <SalesPage /> },
      { path: 'finances', element: <FinancesPage /> },
      { path: 'analytics', element: <ReportsPage /> },
      { path: 'reports', element: <ReportsPage /> },
      { path: 'users', element: <UsersPage /> },
      { path: 'config', element: <SettingsPage /> },
      { path: 'settings', element: <SettingsPage /> },
      { path: 'billing', element: <BillingPage /> },
      { path: '*', element: <Navigate to="/checkout" replace /> },
    ],
  },
  {
    path: '/catalog/preview',
    element: <StorefrontPreviewPage />,
  },
  {
    path: '/store/:slug',
    element: <StorefrontPreviewPage />,
  },
  {
    path: '/store/:slug/orders/:orderNumber',
    element: <OrderStatusPage />,
  },
  {
    path: '/platform',
    element: (
      <PlatformShell>
        <PlatformPage />
      </PlatformShell>
    ),
  },
  {
    path: '/login',
    element: <LoginPage />,
  },
  {
    path: '/signup',
    element: <SignupPage />,
  },
]);
