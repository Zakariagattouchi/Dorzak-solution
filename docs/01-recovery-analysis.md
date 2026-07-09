# 01 — Recovery Analysis Report

## 1. Deployed Stack Identification
- **Framework / Library**: React 17+ with React DOM
- **Bundler / Build Tool**: Webpack 5 with CSS Modules (`icons_icon-...__hash`)
- **Routing**: React Router DOM (Single-page app architecture with dynamic path routing)
- **State Management**: React Context + Local Component State / Zustand pattern
- **Design System / Styling**: Webpack CSS Modules + Custom Utility Classes + Clean SaaS Design System
- **Iconography**: Custom SVG Icon Registry + Embedded Icomoon Vector Glyphs
- **Backend / API Infrastructure**: RESTful JSON endpoints (`/api/kyte-web/*`), Firebase Authentication, Google Cloud Storage, Firestore Web Sockets

## 2. Evidence Collected
- **HAR Archive**: `web.kyteapp.com.har` (31 MB) containing 257 total network entries & 111 API requests
- **JS Bundles**: 52 extracted production JS bundles with minified React component symbols & CSS module definitions
- **Screenshots**: Over 600 full-page and component screenshots capturing states, modals, drawers, tables, and forms
- **API Payloads**: Full JSON request/response logs for Account Info, Products, Customers, Orders, Subscriptions, Preferences
- **HTML DOM Captures**: Complete single-page app shell structure with mounting points (`#root`)

## 3. Discovered SaaS Application Domains
1. **POS / Checkout Shell**: Interactive cart, quick sale, item search, customer attachment, payment processing simulation
2. **Products & Catalog Shell**: Product list table, category drawers, product creation/editing modal, stock tracking
3. **Orders & Transactions Shell**: Order history table, order status pills (Completed, Pending, Cancelled), order detail panel, receipt generator
4. **Customers Shell**: Customer list table, customer details, customer creation modal, search & filter
5. **Reports & Analytics Shell**: Revenue stats, cash flow metrics, sales breakdown charts, top products summary
6. **Storefront / Online Store Shell**: Online catalog toggle, banner upload, domain settings, customer ordering settings
7. **Settings & Account Shell**: Business info form, user/staff management, subscription plans, integration toggles
