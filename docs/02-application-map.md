# 02 — Application Architecture Map

```mermaid
graph TD
    App[Kyte Web SaaS App] --> AuthGuard[Auth Guard / User Context]
    AuthGuard --> AuthLayout[Auth Layout - Login / Signup]
    AuthGuard --> AppShell[Main SaaS App Shell]

    AppShell --> Sidebar[Global Collapsible Sidebar Nav]
    AppShell --> Topbar[Global Header - Store Selector, Search, Quick Action]
    AppShell --> RouterOutlet[React Router Outlet]
    AppShell --> ModalHost[Global Modal Host Store]
    AppShell --> ToastHost[Global Toast Notification Host]

    RouterOutlet --> POSPage[POS / Checkout Page]
    RouterOutlet --> ProductsPage[Products Management Page]
    RouterOutlet --> CategoriesPage[Categories Management Page]
    RouterOutlet --> OrdersPage[Orders & Sales Page]
    RouterOutlet --> CustomersPage[Customer CRM Page]
    RouterOutlet --> ReportsPage[Reports & Analytics Page]
    RouterOutlet --> StorefrontPage[Online Store Settings Page]
    RouterOutlet --> SettingsPage[Business Settings Page]
    RouterOutlet --> BillingPage[Subscription & Plans Page]

    POSPage --> CartStore[Cart & Checkout Local State]
    ProductsPage --> ProductStore[Products & Stock State]
    OrdersPage --> OrderStore[Order Transactions State]
    CustomersPage --> CustomerStore[Customer CRM State]
```

## Shell Component Structure
- **AppShell**: Root container with left navigation, top bar, dynamic viewport, modal host, toast host.
- **Sidebar**: Primary left-hand navigation bar supporting 9 primary routes and active highlight indicators.
- **Topbar**: Header containing Store Name, Quick Search input, Notification bell, and "+ Quick Sale" CTA button.
- **ModalHost**: Centralized portal rendering active modals dynamically based on `modalStore` state.
- **ToastHost**: Floating notification overlay for success messages, error warnings, and state updates.
