import React, { useEffect } from 'react';
import { useSettingsStore } from '../stores/settingsStore';

const ar: Record<string, string> = {
  'Dorzak Merchant': 'دورزاك ميرشنت',
  Operations: 'العمليات',
  Commerce: 'التجارة',
  Customers: 'العملاء',
  Finance: 'المالية',
  System: 'النظام',
  Sell: 'البيع',
  Orders: 'الطلبات',
  Products: 'المنتجات',
  'Online Catalog': 'الكتالوج الإلكتروني',
  Transactions: 'المعاملات',
  Finances: 'المالية',
  Analytics: 'التحليلات',
  Users: 'المستخدمون',
  Settings: 'الإعدادات',
  'Quick Sale': 'بيع سريع',
  'Add Product': 'إضافة منتج',
  'Add a Product': 'إضافة منتج',
  'All branches': 'جميع الفروع',
  'Search products by name or SKU...': 'ابحث عن المنتجات بالاسم أو رمز المنتج...',
  'Search catalog by name, category or SKU...': 'ابحث في الكتالوج بالاسم أو الفئة أو رمز المنتج...',
  'Search by transaction ID or customer...': 'ابحث برقم المعاملة أو العميل...',
  'All Items': 'كل المنتجات',
  Customer: 'العميل',
  New: 'جديد',
  'Walk-in Customer': 'عميل مباشر',
  'Cart is empty. Tap items to add.': 'السلة فارغة. اضغط على المنتجات لإضافتها.',
  'Subtotal:': 'المجموع الفرعي:',
  'Total:': 'الإجمالي:',
  Charge: 'تحصيل',
  'Stock:': 'المخزون:',
  'Products Catalog': 'كتالوج المنتجات',
  'Manage inventory items, pricing, variants, and stock levels':
    'إدارة المنتجات والأسعار والخيارات ومستويات المخزون',
  Product: 'المنتج',
  Category: 'الفئة',
  Price: 'السعر',
  'Stock Status': 'حالة المخزون',
  Actions: 'الإجراءات',
  'in stock': 'متوفر',
  'Out of stock': 'نفد المخزون',
  'Product Categories': 'فئات المنتجات',
  'Organize products for POS and online store display':
    'تنظيم المنتجات لنقطة البيع والمتجر الإلكتروني',
  'Orders & Sales History': 'سجل الطلبات والمبيعات',
  'Complete history of all transactions — click an order to view the receipt':
    'سجل كامل لجميع المعاملات — اضغط على الطلب لعرض الإيصال',
  'Total Revenue': 'إجمالي الإيرادات',
  Completed: 'مكتمل',
  Pending: 'قيد الانتظار',
  Cancelled: 'ملغي',
  'Orders & Transactions': 'الطلبات والمعاملات',
  'Sales Transactions Log': 'سجل معاملات المبيعات',
  'Complete audit log of all sales with payment breakdowns, discounts, and tax details':
    'سجل تدقيق كامل للمبيعات وطرق الدفع والخصومات والضرائب',
  'Total Tax Collected': 'إجمالي الضريبة المحصلة',
  'Discounts Given': 'الخصومات المقدمة',
  'All Methods': 'كل طرق الدفع',
  'Transaction ID': 'رقم المعاملة',
  'Date & Time': 'التاريخ والوقت',
  'Payment Method': 'طريقة الدفع',
  Status: 'الحالة',
  Discount: 'الخصم',
  Tax: 'الضريبة',
  'Net Total': 'الصافي الإجمالي',
  Card: 'بطاقة',
  Cash: 'نقداً',
  Transfer: 'تحويل',
  'Finances & Cash Flow': 'المالية والتدفق النقدي',
  'Revenue breakdown, cash vs card flows, tax collected, and profit summary':
    'تفاصيل الإيرادات والتدفقات النقدية والبطاقات والضرائب والأرباح',
  Daily: 'يومي',
  Weekly: 'أسبوعي',
  Monthly: 'شهري',
  'Gross Revenue': 'إجمالي الإيرادات',
  'Net Revenue': 'صافي الإيرادات',
  'Tax Collected': 'الضريبة المحصلة',
  'Pending Revenue': 'إيرادات معلقة',
  'Cash Flow by Method': 'التدفق النقدي حسب الطريقة',
  'Adjustments Summary': 'ملخص التسويات',
  'Taxes Owed': 'الضرائب المستحقة',
  'Estimated Net': 'الصافي التقديري',
  'Recent Financial Entries': 'أحدث القيود المالية',
  'Export CSV': 'تصدير CSV',
  Description: 'الوصف',
  Date: 'التاريخ',
  Method: 'الطريقة',
  Amount: 'المبلغ',
  'Analytics & Business Reports': 'التحليلات وتقارير الأعمال',
  'Sales trends, revenue breakdown, payment methods, and inventory health':
    'اتجاهات المبيعات وتفاصيل الإيرادات وطرق الدفع وصحة المخزون',
  Today: 'اليوم',
  Week: 'الأسبوع',
  Month: 'الشهر',
  'All Time': 'كل الوقت',
  'Gross Profit': 'إجمالي الربح',
  'Avg Order Value': 'متوسط قيمة الطلب',
  'Total Orders': 'إجمالي الطلبات',
  'Revenue by Payment Method': 'الإيرادات حسب طريقة الدفع',
  'Inventory Health': 'صحة المخزون',
  'Total Products': 'إجمالي المنتجات',
  'Low Stock Alert': 'تنبيه مخزون منخفض',
  'Low Stock Alerts': 'تنبيهات المخزون المنخفض',
  'Top Selling Products': 'المنتجات الأكثر مبيعاً',
  'Products by Category': 'المنتجات حسب الفئة',
  'Customer CRM': 'إدارة علاقات العملاء',
  'Add Customer': 'إضافة عميل',
  'Total Customers': 'إجمالي العملاء',
  'Total Spent': 'إجمالي الإنفاق',
  'Avg Lifetime Value': 'متوسط القيمة الدائمة',
  Name: 'الاسم',
  Phone: 'الهاتف',
  Email: 'البريد الإلكتروني',
  Notes: 'ملاحظات',
  'Customer Detail': 'تفاصيل العميل',
  'Recent Orders': 'الطلبات الأخيرة',
  'Send WhatsApp Message': 'إرسال رسالة واتساب',
  'Online Storefront Customizer': 'تخصيص المتجر الإلكتروني',
  'Configure your public web catalog, online ordering wizard, banner, and WhatsApp integration':
    'إعداد الكتالوج العام والطلب الإلكتروني والواجهة وربط واتساب',
  'Live Catalog Preview': 'معاينة الكتالوج مباشرة',
  'Status & Link': 'الحالة والرابط',
  Branding: 'الهوية البصرية',
  Fulfillment: 'التنفيذ',
  WhatsApp: 'واتساب',
  'Save Settings': 'حفظ الإعدادات',
  'Users & Staff Management': 'إدارة المستخدمين والموظفين',
  'Manage team members, assign roles, and control access permissions':
    'إدارة أعضاء الفريق والأدوار وصلاحيات الوصول',
  'Invite Staff Member': 'دعوة موظف',
  Cancel: 'إلغاء',
  'Invite a New Team Member': 'دعوة عضو جديد للفريق',
  'Business Settings': 'إعدادات النشاط التجاري',
  Configuration: 'الإعدادات',
  General: 'عام',
  'Business Info': 'بيانات النشاط',
  Currency: 'العملة',
  Taxes: 'الضرائب',
  Receipts: 'الإيصالات',
  Payments: 'المدفوعات',
  Integrations: 'التكاملات',
  'Users & Staff': 'المستخدمون والموظفون',
  Subscription: 'الاشتراك',
  'General Store Settings': 'إعدادات المتجر العامة',
  'Your store name, tagline, and contact numbers': 'اسم المتجر والشعار وأرقام التواصل',
  'Business / Store Name *': 'اسم النشاط / المتجر *',
  'Tagline / Slogan': 'الشعار',
  'Phone Number': 'رقم الهاتف',
  'WhatsApp Business Number': 'رقم واتساب للأعمال',
  'WhatsApp tip': 'نصيحة واتساب',
  'Language & Region': 'اللغة والمنطقة',
  'Interface Language': 'لغة الواجهة',
  English: 'الإنجليزية',
  Arabic: 'العربية',
  'Business Information': 'بيانات النشاط التجاري',
  'Legal business details, owner name, and registered address':
    'البيانات القانونية واسم المالك والعنوان المسجل',
  'Account Owner Name': 'اسم مالك الحساب',
  'Contact Email Address': 'البريد الإلكتروني للتواصل',
  'Physical Business Address': 'عنوان النشاط',
  'Street Address': 'عنوان الشارع',
  City: 'المدينة',
  'State / Province': 'الولاية / المنطقة',
  'Postal / ZIP Code': 'الرمز البريدي',
  Country: 'الدولة',
  'Currency & Formatting': 'العملة والتنسيق',
  'Choose your store currency and how prices are displayed': 'اختر عملة المتجر وطريقة عرض الأسعار',
  'Store Currency': 'عملة المتجر',
  'Currency Symbol Position': 'موضع رمز العملة',
  'Before amount': 'قبل المبلغ',
  'After amount': 'بعد المبلغ',
  'Before amount — $100.00': 'قبل المبلغ — $100.00',
  'After amount — 100.00 $': 'بعد المبلغ — 100.00 $',
  'Price Preview': 'معاينة السعر',
  'QAR — Qatari Riyal (ر.ق)': 'ر.ق — الريال القطري (QAR)',
  'USD — US Dollar ($)': 'دولار أمريكي ($)',
  'EUR — Euro (€)': 'يورو (€)',
  'GBP — British Pound (£)': 'جنيه إسترليني (£)',
  'Sales Tax Configuration': 'إعداد ضريبة المبيعات',
  'Receipt Customization': 'تخصيص الإيصال',
  'Payment Methods': 'طرق الدفع',
  'Third-Party Integrations': 'تكاملات الجهات الخارجية',
  'Subscription & Plan': 'الاشتراك والخطة',
  'Settings saved successfully!': 'تم حفظ الإعدادات بنجاح!',
  'Failed to save settings': 'تعذر حفظ الإعدادات',
  'Save Changes': 'حفظ التغييرات',
  'Create Product': 'إنشاء المنتج',
  'Back to Products': 'العودة إلى المنتجات',
  'Basic Information': 'المعلومات الأساسية',
  'Product Name': 'اسم المنتج',
  'SKU / Product Code': 'رمز المنتج',
  'Selling Price': 'سعر البيع',
  'Cost Price': 'سعر التكلفة',
  Inventory: 'المخزون',
  'Track Stock': 'تتبع المخزون',
  Taxable: 'خاضع للضريبة',
  'Show in Online Store': 'إظهار في المتجر الإلكتروني',
  'Featured Product': 'منتج مميز',
  'No records found.': 'لا توجد سجلات.',
  'Loading...': 'جارٍ التحميل...',
};

Object.assign(ar, {
  // Global shell and accessibility
  'Primary navigation': 'التنقل الرئيسي',
  'Open navigation': 'فتح قائمة التنقل',
  'Close navigation': 'إغلاق قائمة التنقل',
  'Close dialog': 'إغلاق النافذة',
  'United States': 'الولايات المتحدة',
  'United Kingdom': 'المملكة المتحدة',
  Canada: 'كندا',
  Brazil: 'البرازيل',
  Mexico: 'المكسيك',
  Panama: 'بنما',
  Colombia: 'كولومبيا',
  Argentina: 'الأرجنتين',
  Australia: 'أستراليا',
  Germany: 'ألمانيا',
  France: 'فرنسا',
  Qatar: 'قطر',
  'All branches ·': 'جميع الفروع ·',
  'Online Catalog •': 'الكتالوج الإلكتروني •',

  // Demo catalog content
  'Apparel & Fashion': 'الأزياء والملابس',
  'Electronics & Tech': 'الإلكترونيات والتقنية',
  'Coffee & Beverages': 'القهوة والمشروبات',
  Accessories: 'الإكسسوارات',
  'Home & Office': 'المنزل والمكتب',
  'Dorzak Signature Cotton Hoodie': 'هودي دورزاك القطني المميز',
  'Wireless Noise-Canceling Earbuds': 'سماعات لاسلكية عازلة للضوضاء',
  'Artisan Cold Brew Coffee (750ml)': 'قهوة كولد برو مختصة (750 مل)',
  'Artisan Cold Brew Coffee': 'قهوة كولد برو مختصة',
  'Minimalist Leather Cardholder': 'حافظة بطاقات جلدية بسيطة',
  'Ergonomic Desk Mat': 'حصيرة مكتب مريحة',
  'Stainless Steel Water Bottle (1L)': 'زجاجة ماء من الستانلس ستيل (1 لتر)',
  'SKU:': 'رمز المنتج:',
  items: 'منتجات',
  'Assigned Products': 'المنتجات المعينة',
  'New Category': 'فئة جديدة',
  'Category Name': 'اسم الفئة',

  // Product creation and editing
  'Highlight Product': 'تمييز المنتج',
  '★ Highlight product': '★ تمييز المنتج',
  'Show on Online Catalog': 'إظهار في الكتالوج الإلكتروني',
  'Save Product': 'حفظ المنتج',
  LABEL: 'الوسم',
  'Label Color': 'لون الوسم',
  'Photo Image URL': 'رابط صورة المنتج',
  'Basic Product Details': 'بيانات المنتج الأساسية',
  'e.g. Cotton T-Shirt': 'مثال: قميص قطني',
  'Reduced Price ($)': 'السعر المخفض',
  'Optional sale price': 'سعر التخفيض الاختياري',
  'Label Name': 'اسم الوسم',
  'e.g. NEW / SALE': 'مثال: جديد / تخفيض',
  'Suggest Description (AI)': 'اقتراح وصف بالذكاء الاصطناعي',
  'Write item description for receipt and catalog...': 'اكتب وصف المنتج للإيصال والكتالوج...',
  'Product code (SKU / Barcode)': 'رمز المنتج / الباركود',
  'Automatic Registration': 'التسجيل التلقائي',
  'Select a photo to auto-fill product details using AI recognition.':
    'اختر صورة لتعبئة بيانات المنتج تلقائياً باستخدام الذكاء الاصطناعي.',
  'Select a Photo': 'اختيار صورة',
  'Stock Management': 'إدارة المخزون',
  'Manage stock for this product': 'إدارة مخزون هذا المنتج',
  'On hand (Quantity)': 'الكمية المتوفرة',
  'Minimum (Safety Alert)': 'الحد الأدنى للتنبيه',
  'Product Variations': 'خيارات المنتج',
  'Variation (e.g. Size L / Red)': 'الخيار (مثال: مقاس كبير / أحمر)',
  Stock: 'المخزون',
  '+ Add': '+ إضافة',
  'Product Title *': 'اسم المنتج *',
  'Product name *': 'اسم المنتج *',
  'Price ($) *': 'السعر *',
  'e.g. TS-1001': 'مثال: TS-1001',
  'Product Description': 'وصف المنتج',
  'Detailed description for receipt and online storefront catalog...':
    'وصف تفصيلي للإيصال وكتالوج المتجر الإلكتروني...',
  'Unit of Measure': 'وحدة القياس',
  'Pieces (pcs)': 'قطع',
  'Kilograms (kg)': 'كيلوغرام',
  'Box / Pack': 'علبة / حزمة',
  'Meters (m)': 'متر',
  'Bottle / Can': 'زجاجة / علبة',
  'SKU / Barcode': 'رمز المنتج / الباركود',
  'Image URL': 'رابط الصورة',
  'Selling Price ($) *': 'سعر البيع *',
  'Cost Price ($)': 'سعر التكلفة',
  'ESTIMATED PROFIT MARGIN': 'هامش الربح التقديري',
  'Item is Subject to Sales Tax': 'المنتج خاضع لضريبة المبيعات',
  'Track Inventory Stock Quantity': 'تتبع كمية المخزون',
  'Current Stock Quantity': 'كمية المخزون الحالية',
  'Minimum Stock Alert Level': 'حد تنبيه المخزون',
  'Add Product Variant': 'إضافة خيار للمنتج',
  'Variant (e.g. Size M / Blue)': 'الخيار (مثال: مقاس متوسط / أزرق)',
  'Price ($)': 'السعر',
  'Display in Online Store Catalog': 'العرض في كتالوج المتجر الإلكتروني',
  'Mark as Featured Product': 'تحديد كمنتج مميز',

  // Orders, customers, finance, and analytics
  'Search by Order ID or Customer Name...': 'ابحث برقم الطلب أو اسم العميل...',
  'All Orders': 'كل الطلبات',
  'Order ID': 'رقم الطلب',
  Payment: 'الدفع',
  Total: 'الإجمالي',
  Receipt: 'الإيصال',
  'registered customers': 'عملاء مسجلون',
  '↑ Export': '↑ تصدير',
  '↓ Import Customers': '↓ استيراد العملاء',
  '+ Register Customer': '+ تسجيل عميل',
  'Search customers by name, email or phone...':
    'ابحث عن العملاء بالاسم أو البريد الإلكتروني أو الهاتف...',
  BALANCE: 'الرصيد',
  ADDRESS: 'العنوان',
  "Let's register your first customer": 'سجّل أول عميل لديك',
  'After tax deduction': 'بعد خصم الضريبة',
  'rate applied': 'نسبة مطبقة',
  'Awaiting payment': 'بانتظار الدفع',
  'Card / POS Terminal': 'بطاقة / جهاز نقطة البيع',
  'Cash in Register': 'النقد في الصندوق',
  'Bank Transfer / Pix': 'تحويل بنكي / Pix',
  '% of total': '٪ من الإجمالي',
  'Sale to': 'بيع إلى',
  'item(s)': 'منتج',
  sales: 'مبيعات',
  'Est. after cost': 'تقديري بعد التكلفة',
  'Per transaction': 'لكل معاملة',
  'Completed sales': 'مبيعات مكتملة',
  'Bank Transfer': 'تحويل بنكي',
  'est. rev': 'إيراد تقديري',

  // Storefront and public catalog
  'Store Status & Link': 'حالة المتجر والرابط',
  'Visual Branding & Banner': 'الهوية البصرية والواجهة',
  'Branding & Banner': 'الهوية البصرية والواجهة',
  'Delivery & Pickup': 'التوصيل والاستلام',
  'Delivery, Pickup & Dine-in': 'التوصيل والاستلام والطلبات داخل المطعم',
  'WhatsApp Checkout': 'إتمام الطلب عبر واتساب',
  'Online Catalog Activation': 'تفعيل الكتالوج الإلكتروني',
  'Publish your online store for public customer orders':
    'انشر متجرك الإلكتروني لاستقبال طلبات العملاء.',
  'CUSTOM STORE WEB ADDRESS': 'عنوان المتجر الإلكتروني',
  'Copy Link': 'نسخ الرابط',
  'Store Web Slug (.dorzak.com)': 'معرّف رابط المتجر (.dorzak.com)',
  'e.g. my-store-name': 'مثال: اسم-متجري',
  'Save Storefront Options': 'حفظ إعدادات المتجر',
  'Fulfillment & Shipping Rules': 'قواعد الاستلام والتوصيل والطلبات داخل المطعم',
  'Enable Home Delivery Service': 'تفعيل خدمة التوصيل للمنزل',
  'Enable In-Store Pickup': 'تفعيل الاستلام من المتجر',
  'Enable Dine-in Table Ordering': 'تفعيل الطلب من الطاولات',
  'Customers scan a table QR code, open the storefront, and place an order linked to that table.':
    'يمسح العميل رمز QR للطاولة، ويفتح المتجر، ويرسل طلباً مرتبطاً بهذه الطاولة.',
  'Dine-in table QR codes': 'رموز QR لطاولات المطعم',
  'Enter how many tables you have. Dorzak will generate one QR code per table and attach the table number to every order.':
    'أدخل عدد الطاولات لديك. سيولد دورزاك رمز QR لكل طاولة ويربط رقم الطاولة بكل طلب.',
  'Number of tables': 'عدد الطاولات',
  'table QR codes ready': 'رموز QR للطاولات جاهزة',
  'Print QR Codes': 'طباعة رموز QR',
  Table: 'الطاولة',
  'Open QR': 'فتح رمز QR',
  'Dine-in': 'داخل المطعم',
  'Pay at table': 'الدفع على الطاولة',
  'A staff member will collect payment at your table.': 'سيقوم الموظف بتحصيل الدفع على طاولتك.',
  'Choose your table': 'اختر طاولتك',
  'You scanned this table QR code.': 'تم فتح الطلب من رمز هذه الطاولة.',
  'Table number': 'رقم الطاولة',
  Bag: 'السلة',
  'Search catalog items...': 'ابحث في منتجات الكتالوج...',
  'All Products': 'كل المنتجات',
  'Add to Bag': 'أضف إلى السلة',

  // Staff and permissions
  'Dorzak Merchant Pro Plan': 'خطة دورزاك ميرشنت برو',
  'of unlimited staff users active • Upgrade anytime to add more':
    'من عدد غير محدود من الموظفين النشطين • يمكنك الترقية في أي وقت',
  OWNER: 'المالك',
  MANAGER: 'المدير',
  CASHIER: 'أمين الصندوق',
  VIEWER: 'مستعرض',
  'Full access': 'صلاحية كاملة',
  'Billing management': 'إدارة الفوترة',
  'Full access (no billing)': 'صلاحية كاملة دون الفوترة',
  'Staff management': 'إدارة الموظفين',
  'POS Checkout': 'إتمام البيع في نقطة البيع',
  'View Orders': 'عرض الطلبات',
  'View-only access': 'صلاحية عرض فقط',
  Reports: 'التقارير',
  '(You)': '(أنت)',
  '• Joined': '• انضم في',
  Joined: 'تاريخ الانضمام',
  Owner: 'مالك',
  Cashier: 'أمين صندوق',
  Manager: 'مدير',
  Remove: 'إزالة',
  'Invite staff members and assign role-based access permissions':
    'ادعُ الموظفين وحدد صلاحيات الوصول حسب الدور.',
  '+ Invite Staff Member': '+ دعوة موظف',
  'Cashier (POS & Orders)': 'أمين صندوق (نقطة البيع والطلبات)',
  'Manager (Full Access, no billing)': 'مدير (صلاحية كاملة دون الفوترة)',
  'Cancel Subscription': 'إلغاء الاشتراك',

  // Settings: general and business
  'e.g. Dorzak Merchant': 'مثال: دورزاك ميرشنت',
  'e.g. Quality products, fast delivery': 'مثال: منتجات عالية الجودة وتوصيل سريع',
  'Your WhatsApp number is used for direct customer order notifications from the online catalog.':
    'يُستخدم رقم واتساب لإرسال إشعارات طلبات العملاء مباشرة من الكتالوج الإلكتروني.',
  'e.g. 123 Main St, Suite 4': 'مثال: شارع حمد الكبير، مبنى 4',
  'e.g. 8.5': 'مثال: 8.5',
  'e.g. US-991827364': 'مثال: US-991827364',
  'ZIP / Postal Code': 'الرمز البريدي',
  'CAD — Canadian Dollar (CA$)': 'دولار كندي (CA$)',
  'BRL — Brazilian Real (R$)': 'ريال برازيلي (R$)',
  'MXN — Mexican Peso ($)': 'بيزو مكسيكي ($)',
  'COP — Colombian Peso ($)': 'بيزو كولومبي ($)',
  'ARS — Argentine Peso ($)': 'بيزو أرجنتيني ($)',
  'AUD — Australian Dollar (A$)': 'دولار أسترالي (A$)',

  // Settings: tax and receipts
  'Configure sales tax rates applied to POS and online transactions':
    'اضبط نسب ضريبة المبيعات المطبقة على نقطة البيع والمعاملات الإلكترونية.',
  'Charge Sales Taxes': 'تحصيل ضريبة المبيعات',
  'Apply tax rate to all taxable products in sales and receipts':
    'تطبيق نسبة الضريبة على جميع المنتجات الخاضعة لها في المبيعات والإيصالات.',
  'Default Sales Tax Rate (%)': 'نسبة ضريبة المبيعات الافتراضية (٪)',
  'Tax Registration ID / VAT Number': 'الرقم الضريبي / رقم ضريبة القيمة المضافة',
  'Tax already included in product prices': 'الضريبة مشمولة في أسعار المنتجات',
  "Enable this if your product prices already include the tax amount (prices shown to customers won't change)":
    'فعّل هذا الخيار إذا كانت أسعار المنتجات تشمل الضريبة مسبقاً؛ لن تتغير الأسعار المعروضة للعملاء.',
  'Personalize the header and footer text shown on printed and emailed receipts':
    'خصّص نص رأس وتذييل الإيصالات المطبوعة والمرسلة بالبريد.',
  'Receipt Header Message': 'رسالة رأس الإيصال',
  'Appears at the top of every receipt, above the order items':
    'تظهر أعلى كل إيصال قبل عناصر الطلب.',
  'Receipt Notes / Footer Text': 'ملاحظات الإيصال / نص التذييل',
  'e.g. Returns accepted within 30 days': 'مثال: يُقبل الإرجاع خلال 30 يوماً',
  'Print Options': 'خيارات الطباعة',
  'Print store logo on receipt': 'طباعة شعار المتجر على الإيصال',
  'Show your business logo at the top of printed receipts':
    'إظهار شعار النشاط أعلى الإيصالات المطبوعة.',
  'Print business address on receipt': 'طباعة عنوان النشاط على الإيصال',
  'Include your store address in the receipt footer': 'إضافة عنوان المتجر في تذييل الإيصال.',
  'Show tax breakdown on receipt': 'إظهار تفاصيل الضريبة على الإيصال',
  'Display the applied tax amount separately on receipts': 'عرض مبلغ الضريبة المطبق بصورة منفصلة.',
  'Auto-print receipt after sale': 'طباعة الإيصال تلقائياً بعد البيع',
  'Automatically open the print dialog after completing a sale at the POS':
    'فتح نافذة الطباعة تلقائياً بعد إتمام البيع.',
  'Receipt Preview': 'معاينة الإيصال',
  'Thank you for supporting our local business!': 'شكراً لدعمكم لنشاطنا المحلي!',
  'Returns accepted within 30 days with receipt.': 'يُقبل الإرجاع خلال 30 يوماً مع الإيصال.',

  // Settings: payments and integrations
  'Choose which payment options appear during POS checkout':
    'اختر طرق الدفع التي تظهر عند إتمام البيع.',
  'Accept physical cash payments': 'قبول المدفوعات النقدية',
  'Credit and debit card payments via POS machine':
    'قبول بطاقات الائتمان والخصم عبر جهاز نقطة البيع.',
  'Bank Transfer / Pix / Wire': 'تحويل بنكي / Pix / حوالة',
  'Direct bank transfers, Pix, or wire payments': 'قبول التحويلات البنكية المباشرة وPix والحوالات.',
  'WhatsApp Order (Online)': 'طلب واتساب (إلكتروني)',
  'Orders submitted via WhatsApp from the online catalog':
    'الطلبات المرسلة عبر واتساب من الكتالوج الإلكتروني.',
  'Connect your store to Facebook, Google Analytics, and other marketing tools':
    'اربط متجرك بفيسبوك وتحليلات Google وأدوات التسويق الأخرى.',
  'Facebook & Instagram Shop': 'متجر فيسبوك وإنستغرام',
  'Sync products and promote via Meta Social Commerce': 'زامن المنتجات وروّج لها عبر منصات Meta.',
  'Connect Facebook': 'ربط فيسبوك',
  Disconnect: 'قطع الاتصال',
  'Facebook Pixel ID': 'معرّف Facebook Pixel',
  'Track customer actions on your catalog for Meta ad retargeting':
    'تتبع إجراءات العملاء لإعادة الاستهداف بإعلانات Meta.',
  'Google Analytics 4': 'تحليلات Google 4',
  'Track catalog visitor data and conversion metrics with GA4':
    'تتبع بيانات الزوار ومؤشرات التحويل باستخدام GA4.',
  'Google Analytics Measurement ID': 'معرّف قياس Google Analytics',

  // Subscription and billing
  'Plans & Subscription': 'الخطط والاشتراك',
  'Manage your Dorzak Merchant plan and feature modules': 'إدارة خطة دورزاك ميرشنت ووحدات المزايا.',
  'Dorzak Merchant Pro': 'دورزاك ميرشنت برو',
  'PRO ACTIVE': 'خطة برو نشطة',
  'Renews automatically on Jan 1, 2027': 'يتجدد تلقائياً في 1 يناير 2027',
  'Manage Billing': 'إدارة الفوترة',
  'Included PRO Features': 'مزايا برو المتضمنة',
  'Unlimited POS Checkout & Orders': 'مبيعات وطلبات غير محدودة',
  'Unlimited Catalog & Stock Sync': 'مزامنة غير محدودة للكتالوج والمخزون',
  'Customer CRM & Spend Tracking': 'إدارة العملاء وتتبع الإنفاق',
  'Public Online Storefront Link': 'رابط متجر إلكتروني عام',
  'Need Enterprise Custom Features?': 'هل تحتاج إلى مزايا مخصصة للمؤسسات؟',
  'Upgrade to Enterprise for multi-store sync, custom API webhooks, and dedicated account manager support.':
    'رقِّ إلى خطة المؤسسات لمزامنة عدة متاجر وواجهات API مخصصة ومدير حساب مخصص.',
  'Contact Sales': 'تواصل مع المبيعات',
  'Manage your Dorzak Merchant plan, features, and billing cycle':
    'إدارة خطة دورزاك ميرشنت والمزايا ودورة الفوترة.',
  'CURRENT PLAN': 'الخطة الحالية',
  'Renews July 5, 2027 • Monthly billing': 'يتجدد في 5 يوليو 2027 • فوترة شهرية',
  'per month': 'شهرياً',
  'Unlimited Products': 'منتجات غير محدودة',
  'WhatsApp Ordering': 'الطلب عبر واتساب',
  'Multiple Staff Users': 'عدة مستخدمين من الموظفين',
  'Advanced Analytics': 'تحليلات متقدمة',
  'Priority Support': 'دعم ذو أولوية',
  'Download Invoice': 'تنزيل الفاتورة',

  // Modals and transient actions
  'Create Category': 'إنشاء فئة',
  'Save Category': 'حفظ الفئة',
  'Category Name *': 'اسم الفئة *',
  'e.g. Footwear': 'مثال: أحذية',
  'Accent Color': 'اللون المميز',
  'Add New Customer': 'إضافة عميل جديد',
  'Save Customer': 'حفظ العميل',
  'Full Name *': 'الاسم الكامل *',
  'e.g. Jane Doe': 'مثال: نورة محمد',
  'Phone Number *': 'رقم الهاتف *',
  'Email Address': 'البريد الإلكتروني',
  'Complete Sale & Payment': 'إتمام البيع والدفع',
  'Complete Sale': 'إتمام البيع',
  'Customer:': 'العميل:',
  'Total Payable:': 'إجمالي المستحق:',
  'Select Payment Method': 'اختر طريقة الدفع',
  'Card / POS': 'بطاقة / نقطة بيع',
  'Create Production Product': 'إنشاء منتج',
  Basic: 'الأساسيات',
  Pricing: 'التسعير',
  Variants: 'الخيارات',
  Online: 'إلكتروني',
  'Sales Receipt': 'إيصال المبيعات',
  'Date:': 'التاريخ:',
  'Discount:': 'الخصم:',
  'Thank you for shopping with': 'شكراً لتسوقك لدى',
  Close: 'إغلاق',
  'Print Receipt': 'طباعة الإيصال',
  Item: 'المنتج',
  Qty: 'الكمية',
});

const textNodes = new Set<Text>();
Object.assign(ar, {
  'Edit Product': 'تعديل المنتج',
  'Prices are displayed in QAR': 'تُعرض الأسعار بالريال القطري',
  'Arabic Product Translation': 'الترجمة العربية للمنتج',
  'Generate editable Arabic product content from the English details.':
    'أنشئ محتوى عربياً قابلاً للتعديل من البيانات الإنجليزية.',
  'Generate Arabic': 'إنشاء العربية',
  'Arabic Product Name': 'اسم المنتج بالعربية',
  'Arabic Description': 'الوصف بالعربية',
  'Variation Groups': 'مجموعات الخيارات',
  'Create groups such as Size and Color, then manage each generated combination.':
    'أنشئ مجموعات مثل المقاس واللون ثم أدر كل تركيبة.',
  'Add Group': 'إضافة مجموعة',
  'Group Name': 'اسم المجموعة',
  Required: 'مطلوب',
  Optional: 'اختياري',
  'Add Option': 'إضافة خيار',
  'Generate Combinations': 'إنشاء التركيبات',
  'Product Photo': 'صورة المنتج',
  'Choose JPG, PNG or WebP': 'اختر JPG أو PNG أو WebP',
  Inventory: 'المخزون',
  'Low-stock Alert': 'تنبيه انخفاض المخزون',
  Choose: 'اختر',
  'Choose options': 'اختر المواصفات',
  None: 'بدون',
  'Add to Cart': 'أضف إلى السلة',
  'This combination is unavailable.': 'هذه التركيبة غير متاحة.',
  'This combination is out of stock.': 'هذه التركيبة غير متوفرة.',
  'Payment Confirmed': 'تم تأكيد الدفع',
  'paid successfully': 'تم دفعها بنجاح',
  'The receipt is ready to print.': 'الإيصال جاهز للطباعة.',
  'New Sale': 'عملية بيع جديدة',
  'View & Print Receipt': 'عرض وطباعة الإيصال',
  'Print Receipt': 'طباعة الإيصال',
  'Active Orders': 'الطلبات النشطة',
  'Track confirming orders through preparation and delivery. Completed orders move to Sales History.':
    'تابع الطلبات من التأكيد إلى التحضير والتوصيل. تنتقل الطلبات المكتملة إلى سجل المبيعات.',
  'ACTIVE ORDER VALUE': 'قيمة الطلبات النشطة',
  Confirming: 'قيد التأكيد',
  Accepted: 'مقبول',
  Preparing: 'قيد التحضير',
  'Out For Delivery': 'خرج للتوصيل',
  Complete: 'مكتمل',
  'Verify Payment': 'التحقق من الدفع',
  'Pending Verification': 'بانتظار التحقق',
  Fawran: 'فوراً',
  'Fawran Transfer': 'تحويل فوراً',
  'Enable Fawran Transfer': 'تفعيل تحويل فوراً',
  'Fawran Alias': 'الاسم المستعار لفوراً',
  'Fawran Mobile': 'رقم جوال فوراً',
  'Fawran IBAN': 'رقم الآيبان لفوراً',
  'Checkout Methods': 'طرق إتمام الطلب',
  'Store Link Slug': 'معرّف رابط المتجر',
  'Cover Banner': 'صورة الغلاف',
  'Store Logo': 'شعار المتجر',
  Address: 'العنوان',
  'City / Area': 'المدينة / المنطقة',
  'Location Pin': 'تحديد الموقع',
  'Search address or area': 'ابحث عن عنوان أو منطقة',
  Search: 'بحث',
  'Current Location': 'الموقع الحالي',
  'Click the map to place the delivery pin.': 'اضغط على الخريطة لتحديد موقع التوصيل.',
  'Full Name *': 'الاسم الكامل *',
  'Phone Number *': 'رقم الهاتف *',
  'Email Address': 'البريد الإلكتروني',
});

const originals = new WeakMap<Text, string>();
const attrElements = new Set<Element>();
const originalAttrs = new WeakMap<Element, Map<string, string>>();
let applying = false;
let previousLanguage: 'en' | 'ar' = 'en';
let previousCurrency = 'USD';

const normalizedLookup = new Map(
  Object.entries(ar).map(([key, value]) => [key.toLocaleLowerCase(), value]),
);

function translateCore(value: string) {
  const exact = ar[value] || normalizedLookup.get(value.toLocaleLowerCase());
  if (exact) return exact;
  if (/^All Items \(\d+\)$/.test(value)) return value.replace('All Items', 'كل المنتجات');
  if (value === 'All Items (') return 'كل المنتجات (';
  if (/^Stock:\s*\d+/.test(value)) return value.replace('Stock:', 'المخزون:');
  if (/^Charge\s/.test(value)) return value.replace('Charge', 'تحصيل');
  if (/^\d+\s+in stock$/.test(value)) return value.replace('in stock', 'متوفر');
  if (/^\d+\s+item\(s\)$/.test(value)) return value.replace('item(s)', 'منتج');
  if (/^\d+\s+variants?$/.test(value)) return value.replace(/variants?/, 'خيارات');
  if (/^•\s*\d+\s+variants?$/.test(value)) return value.replace(/variants?/, 'خيارات');

  if (/^\d+\s+sales$/.test(value)) return value.replace('sales', 'مبيعات');
  if (/^\d+\s+completed sales$/.test(value))
    return value.replace('completed sales', 'مبيعات مكتملة');
  if (/^\d+\s+items$/.test(value)) return value.replace('items', 'منتجات');
  if (/^Bag \(\d+\)/.test(value)) return value.replace('Bag', 'السلة');
  if (value === 'Bag (') return 'السلة (';
  if (/^\d+(?:\.\d+)?%\s+rate applied$/.test(value))
    return value.replace('rate applied', 'نسبة مطبقة');
  if (/^Item \d+/.test(value)) return value.replace('Item', 'المنتج');
  if (/^Sales Receipt —/.test(value)) return value.replace('Sales Receipt', 'إيصال المبيعات');
  if (value === 'Date: ') return 'التاريخ: ';
  if (value === 'Thank you for shopping with ') return 'شكراً لتسوقك لدى ';
  if (/^TOTAL PAID \(/.test(value)) return value.replace('TOTAL PAID', 'الإجمالي المدفوع');
  if (value === 'Complete Sale (') return 'إتمام البيع (';
  if (/^Subtotal\b/.test(value)) return value.replace('Subtotal', 'المجموع الفرعي');
  if (/^Tax \(/.test(value)) return value.replace('Tax', 'الضريبة');
  if (/^TOTAL\b/.test(value)) return value.replace('TOTAL', 'الإجمالي');
  if (/^Remove\s+/.test(value)) return value.replace('Remove', 'إزالة');
  if (/^\S.*\seach$/.test(value)) return value.replace(/\seach$/, ' لكل وحدة');
  if (/^Complete Sale \(/.test(value)) return value.replace('Complete Sale', 'إتمام البيع');
  if (/^Added ".+" to cart$/.test(value)) {
    let message = value.replace(/^Added /, 'تمت إضافة ').replace(/ to cart$/, ' إلى السلة');
    for (const [englishName, arabicName] of [
      ['Dorzak Signature Cotton Hoodie', 'هودي دورزاك القطني المميز'],
      ['Wireless Noise-Canceling Earbuds', 'سماعات لاسلكية عازلة للضوضاء'],
      ['Artisan Cold Brew Coffee (750ml)', 'قهوة كولد برو مختصة (750 مل)'],
      ['Minimalist Leather Cardholder', 'حافظة بطاقات جلدية بسيطة'],
      ['Ergonomic Desk Mat', 'حصيرة مكتب مريحة'],
      ['Stainless Steel Water Bottle (1L)', 'زجاجة ماء من الستانلس ستيل (1 لتر)'],
    ]) {
      message = message.replace(englishName, arabicName);
    }
    return message;
  }
  return value;
}

function transform(value: string, language: 'en' | 'ar', currency: string) {
  const leading = value.match(/^\s*/)?.[0] || '';
  const trailing = value.match(/\s*$/)?.[0] || '';
  let core = value.trim();
  if (!core) return value;

  if (currency === 'QAR') {
    core = core.replace(/\$\s?([\d,.]+)/g, 'QAR $1');
  }
  if (language === 'ar') core = translateCore(core);
  return `${leading}${core}${trailing}`;
}

function process(
  root: ParentNode,
  language: 'en' | 'ar',
  currency: string,
  sourceLanguage = language,
  sourceCurrency = currency,
) {
  const elements =
    root instanceof Element
      ? [root, ...Array.from(root.querySelectorAll('*'))]
      : Array.from(root.querySelectorAll('*'));

  applying = true;
  for (const element of elements) {
    if (element.closest('script, style, code')) continue;

    for (const child of Array.from(element.childNodes)) {
      if (child.nodeType !== Node.TEXT_NODE) continue;
      const node = child as Text;
      const current = node.nodeValue || '';
      const stored = originals.get(node);
      if (!stored) {
        originals.set(node, current);
        textNodes.add(node);
      } else if (current !== transform(stored, sourceLanguage, sourceCurrency)) {
        originals.set(node, current);
      }
      const next = transform(originals.get(node) || current, language, currency);
      if (node.nodeValue !== next) node.nodeValue = next;
    }

    for (const attribute of ['placeholder', 'title', 'aria-label', 'alt']) {
      const current = element.getAttribute(attribute);
      if (!current) continue;
      let map = originalAttrs.get(element);
      if (!map) {
        map = new Map();
        originalAttrs.set(element, map);
        attrElements.add(element);
      }
      const stored = map.get(attribute);
      if (!stored) {
        map.set(attribute, current);
      } else if (current !== transform(stored, sourceLanguage, sourceCurrency)) {
        map.set(attribute, current);
      }
      const next = transform(map.get(attribute) || current, language, currency);
      if (current !== next) element.setAttribute(attribute, next);
    }
  }
  applying = false;
}

function restore() {
  applying = true;
  for (const node of textNodes) {
    if (node.isConnected) node.nodeValue = originals.get(node) || node.nodeValue;
  }
  for (const element of attrElements) {
    if (!element.isConnected) continue;
    originalAttrs.get(element)?.forEach((value, key) => element.setAttribute(key, value));
  }
  applying = false;
}

export const LocaleBridge: React.FC = () => {
  const { language, currency } = useSettingsStore((state) => state.accountInfo);

  useEffect(() => {
    document.documentElement.lang = language;
    document.documentElement.dir = language === 'ar' ? 'rtl' : 'ltr';
    process(document.body, language, currency, previousLanguage, previousCurrency);
    previousLanguage = language;
    previousCurrency = currency;

    const observer = new MutationObserver(() => {
      if (applying) return;
      process(document.body, language, currency);
    });
    const refreshAfterNavigation = () => {
      window.requestAnimationFrame(() => process(document.body, language, currency));
    };
    observer.observe(document.body, { childList: true, subtree: true, characterData: true });
    document.addEventListener('click', refreshAfterNavigation, true);
    window.addEventListener('popstate', refreshAfterNavigation);
    return () => {
      observer.disconnect();
      document.removeEventListener('click', refreshAfterNavigation, true);
      window.removeEventListener('popstate', refreshAfterNavigation);
    };
  }, [language, currency]);

  return null;
};
