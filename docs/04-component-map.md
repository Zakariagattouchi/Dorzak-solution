# 04 — Component Map

## Component Hierarchy & Directory Tree
```
src/components/
├── navigation/
│   ├── Sidebar.tsx
│   ├── SidebarItem.tsx
│   ├── Topbar.tsx
│   └── StoreSelectorDropdown.tsx
├── ui/
│   ├── AppButton.tsx
│   ├── IconButton.tsx
│   ├── Badge.tsx
│   ├── StatusPill.tsx
│   ├── Loader.tsx
│   └── Skeleton.tsx
├── forms/
│   ├── FormField.tsx
│   ├── TextInput.tsx
│   ├── NumberInput.tsx
│   ├── SelectInput.tsx
│   ├── CheckboxInput.tsx
│   ├── ToggleSwitch.tsx
│   └── SearchInput.tsx
├── modals/
│   ├── ModalHost.tsx
│   ├── BaseModal.tsx
│   ├── ProductFormModal.tsx
│   ├── CustomerFormModal.tsx
│   ├── CategoryFormModal.tsx
│   └── PaymentModal.tsx
├── tables/
│   ├── DataTable.tsx
│   ├── TableHeader.tsx
│   ├── TableRow.tsx
│   ├── TableActions.tsx
│   └── Pagination.tsx
├── feedback/
│   ├── ToastHost.tsx
│   ├── Toast.tsx
│   └── Alert.tsx
└── commerce/
    ├── ProductGridCard.tsx
    ├── CartPanel.tsx
    ├── CartItemRow.tsx
    └── QuantityStepper.tsx
```

## Detailed Component Specifications
- **ToggleSwitch**: Controlled boolean slider with animated motion (`transform translateX`), emerald active state.
- **CheckboxInput**: Accessible checkbox with custom check icon, label wrapper, hover focus ring.
- **StatusPill**: Color-coded badge (Green for Paid/Completed, Yellow for Pending, Red for Cancelled).
- **DataTable**: Generic sortable data table with select-all header checkbox, row action menu, empty state.
- **CartPanel**: Real-time cart calculation (Subtotal, Tax, Discount, Total), quantity adjustment, customer selector.
