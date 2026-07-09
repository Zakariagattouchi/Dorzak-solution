# 03 — Route Map

| Path | Route Name | Page Component | Layout | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| `/` | Root | `Navigate to="/pos"` | `AppShell` | Default landing redirect |
| `/pos` | POS / Quick Sale | `POSPage` | `AppShell` | Point-of-sale checkout grid, cart drawer, payment modal |
| `/products` | Products List | `ProductsPage` | `AppShell` | DataTable of products, stock counters, quick edit |
| `/products/new` | Create Product | `ProductCreateModal` | `AppShell` | Form modal for creating new product |
| `/products/:id` | Edit Product | `ProductEditModal` | `AppShell` | Form modal for editing product details |
| `/categories` | Categories | `CategoriesPage` | `AppShell` | Category manager list, category modal |
| `/orders` | Orders History | `OrdersPage` | `AppShell` | Sales table, status filters, receipt modal |
| `/orders/:id` | Order Detail | `OrderDetailModal` | `AppShell` | View order breakdown, print receipt, refund action |
| `/customers` | Customer CRM | `CustomersPage` | `AppShell` | Customer table, total spend, customer detail |
| `/customers/new` | Create Customer | `CustomerCreateModal` | `AppShell` | Form modal for adding customer |
| `/reports` | Reports & Stats | `ReportsPage` | `AppShell` | Revenue analytics, cash flow, top items |
| `/storefront` | Online Catalog | `StorefrontPage` | `AppShell` | Online store toggle, banner upload, URL settings |
| `/settings` | Settings | `SettingsPage` | `AppShell` | Business profile, tax rates, currency, receipt setup |
| `/billing` | Plans & Billing | `BillingPage` | `AppShell` | Subscription status, upgrade modal, payment methods |
| `/login` | Auth Login | `LoginPage` | `AuthLayout` | Owner/staff login form |
