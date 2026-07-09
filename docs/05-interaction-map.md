# 05 — Interaction Map

## Primary User Flows & State Transitions

### 1. POS / Quick Sale Flow
- **User Action**: Click product card on POS grid.
- **State Change**: `cartStore.addItem(product)`.
- **UI Feedback**: Item appears in `CartPanel`, subtotal updates dynamically, toast notification *"Item added to cart"*.
- **User Action**: Click "Charge $XX.XX".
- **State Change**: Opens `PaymentModal`.
- **User Action**: Select Payment Method (Cash/Card/Transfer) -> Click "Complete Sale".
- **State Change**: Saves order to `orderStore`, clears cart, opens receipt modal, shows toast *"Sale Completed Successfully"*.

### 2. Product Management Flow
- **User Action**: Click "+ New Product" button.
- **State Change**: `modalStore.openModal('PRODUCT_CREATE')`.
- **UI Feedback**: `ProductFormModal` slides in.
- **User Action**: Fill Title, Price, Category, Stock -> Click "Save Product".
- **State Change**: Adds item to `productStore`, closes modal, shows success toast *"Product created"*.

### 3. Customer CRM Flow
- **User Action**: Click "+ Add Customer" in POS or Customers page.
- **State Change**: Opens `CustomerFormModal`.
- **User Action**: Enter Name, Phone, Email -> Click "Save".
- **State Change**: Adds customer to `customerStore`, updates active cart customer if triggered from POS.

### 4. Settings & Toggle Switches
- **User Action**: Click Toggle Switch (e.g. "Enable Online Catalog").
- **State Change**: `settingsStore.toggleOnlineStore()`.
- **UI Feedback**: Toggle switch animates smoothly, toast *"Online Store Enabled"*.
