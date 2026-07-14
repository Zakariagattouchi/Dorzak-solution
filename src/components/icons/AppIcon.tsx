import React from 'react';
import {
  ArrowRight,
  Bag,
  Bank,
  Box,
  Building,
  Calculator,
  Cash,
  Check,
  CheckCircle,
  Clock,
  Community,
  Copy,
  CreditCard,
  Cutlery,
  DeliveryTruck,
  DollarCircle,
  EditPencil,
  EmptyPage,
  Eye,
  Filter,
  FireFlame,
  Folder,
  GraphUp,
  Home,
  InfoCircle,
  Label,
  LightBulb,
  Link,
  Mail,
  Menu,
  MessageText,
  NavArrowLeft,
  NavArrowRight,
  Page,
  Phone,
  Pin,
  Plus,
  QrCode,
  Refresh,
  ScanQrCode,
  Search,
  Settings,
  Shop,
  Sparks,
  Star,
  StatsUpSquare,
  Table,
  TaskList,
  Trash,
  UserPlus,
  WarningTriangle,
  Whatsapp,
  Xmark,
} from 'iconoir-react';

export type IconName =
  | 'pos'
  | 'products'
  | 'categories'
  | 'orders'
  | 'customers'
  | 'reports'
  | 'storefront'
  | 'settings'
  | 'billing'
  | 'plus'
  | 'search'
  | 'filter'
  | 'edit'
  | 'trash'
  | 'check'
  | 'close'
  | 'chevronRight'
  | 'chevronLeft'
  | 'dollar'
  | 'userPlus'
  | 'arrowRight'
  | 'trending'
  | 'checkCircle'
  | 'alert'
  | 'info'
  | 'menu'
  | 'card'
  | 'cash'
  | 'transfer'
  | 'whatsapp'
  | 'eye'
  | 'star'
  | 'mail'
  | 'sparkles'
  | 'building'
  | 'refresh'
  | 'receipt'
  | 'link'
  | 'lightbulb'
  | 'mapPin'
  | 'note'
  | 'clipboard'
  | 'tag'
  | 'fileText'
  | 'home'
  | 'dineIn'
  | 'qr'
  | 'scanQr'
  | 'table'
  | 'copy'
  | 'phone'
  | 'cart'
  | 'clock'
  | 'delivery'
  | 'flame';

interface AppIconProps {
  name: IconName;
  size?: number;
  className?: string;
  color?: string;
}

const icons: Record<IconName, React.ElementType> = {
  pos: Calculator,
  products: Box,
  categories: Folder,
  orders: Page,
  customers: Community,
  reports: StatsUpSquare,
  storefront: Shop,
  settings: Settings,
  billing: CreditCard,
  plus: Plus,
  search: Search,
  filter: Filter,
  edit: EditPencil,
  trash: Trash,
  check: Check,
  close: Xmark,
  chevronRight: NavArrowRight,
  chevronLeft: NavArrowLeft,
  dollar: DollarCircle,
  userPlus: UserPlus,
  arrowRight: ArrowRight,
  trending: GraphUp,
  checkCircle: CheckCircle,
  alert: WarningTriangle,
  info: InfoCircle,
  menu: Menu,
  card: CreditCard,
  cash: Cash,
  transfer: Bank,
  whatsapp: Whatsapp,
  eye: Eye,
  star: Star,
  mail: Mail,
  sparkles: Sparks,
  building: Building,
  refresh: Refresh,
  receipt: Page,
  link: Link,
  lightbulb: LightBulb,
  mapPin: Pin,
  note: MessageText,
  clipboard: TaskList,
  tag: Label,
  fileText: EmptyPage,
  home: Home,
  dineIn: Cutlery,
  copy: Copy,
  qr: QrCode,
  scanQr: ScanQrCode,
  table: Table,
  phone: Phone,
  cart: Bag,
  clock: Clock,
  delivery: DeliveryTruck,
  flame: FireFlame,
};

export const AppIcon: React.FC<AppIconProps> = ({ name, size = 20, className = '', color }) => {
  const Icon = icons[name] || Box;
  return (
    <Icon
      aria-hidden="true"
      width={size}
      height={size}
      strokeWidth={2}
      className={className}
      color={color}
    />
  );
};
