import { IconName } from '../components/icons/AppIcon';

export interface NavItem {
  label: string;
  path: string;
  icon: IconName;
  badge?: string;
  group: 'Operations' | 'Commerce' | 'Customers' | 'Finance' | 'System' | 'Platform';
  ability: string;
}

export const mainNavigation: NavItem[] = [
  { label: 'Sell', path: '/checkout', icon: 'pos', group: 'Operations', ability: 'orders.create' },
  { label: 'Orders', path: '/orders', icon: 'orders', group: 'Operations', ability: 'orders.view' },
  {
    label: 'Products',
    path: '/products',
    icon: 'products',
    group: 'Commerce',
    ability: 'products.view',
  },
  {
    label: 'Categories',
    path: '/categories',
    icon: 'categories',
    group: 'Commerce',
    ability: 'products.view',
  },
  {
    label: 'Online Catalog',
    path: '/catalog',
    icon: 'storefront',
    group: 'Commerce',
    ability: 'settings.manage',
  },
  {
    label: 'Customers',
    path: '/customers',
    icon: 'customers',
    group: 'Customers',
    ability: 'customers.view',
  },
  {
    label: 'Marketing',
    path: '/marketing',
    icon: 'trending',
    group: 'Customers',
    ability: 'settings.manage',
  },
  {
    label: 'Transactions',
    path: '/sales',
    icon: 'trending',
    group: 'Finance',
    ability: 'orders.view',
  },
  {
    label: 'Finances',
    path: '/finances',
    icon: 'dollar',
    group: 'Finance',
    ability: 'reports.view',
  },
  {
    label: 'Analytics',
    path: '/analytics',
    icon: 'reports',
    group: 'Finance',
    ability: 'reports.view',
  },
  { label: 'Users', path: '/users', icon: 'userPlus', group: 'System', ability: 'staff.view' },
  {
    label: 'Settings',
    path: '/config',
    icon: 'settings',
    group: 'System',
    ability: 'settings.manage',
  },
];
