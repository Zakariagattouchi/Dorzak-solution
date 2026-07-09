import { Product, Category, Customer, Order, initialAccountInfo, initialCategories, initialProducts, initialCustomers, initialOrders } from '../data/mockData';

// In-memory persistent state for API simulation
let accountData = { ...initialAccountInfo };
let productsData = [...initialProducts];
let categoriesData = [...initialCategories];
let customersData = [...initialCustomers];
let ordersData = [...initialOrders];

const delay = (ms: number = 150) => new Promise(resolve => setTimeout(resolve, ms));

export const mockApi = {
  async getAccountInfo() {
    await delay();
    return { ...accountData };
  },

  async updateAccountInfo(updates: Partial<typeof initialAccountInfo>) {
    await delay();
    accountData = { ...accountData, ...updates };
    return { ...accountData };
  },

  async getProducts() {
    await delay();
    return [...productsData];
  },

  async createProduct(product: Omit<Product, 'id'>) {
    await delay();
    const newProd: Product = {
      ...product,
      id: `prod_${Date.now()}`
    };
    productsData.unshift(newProd);
    return newProd;
  },

  async updateProduct(id: string, updates: Partial<Product>) {
    await delay();
    productsData = productsData.map(p => p.id === id ? { ...p, ...updates } : p);
    return productsData.find(p => p.id === id);
  },

  async deleteProduct(id: string) {
    await delay();
    productsData = productsData.filter(p => p.id !== id);
    return { success: true };
  },

  async getCategories() {
    await delay();
    return [...categoriesData];
  },

  async createCategory(name: string, color: string = '#3b82f6') {
    await delay();
    const newCat: Category = {
      id: `cat_${Date.now()}`,
      name,
      productCount: 0,
      color
    };
    categoriesData.push(newCat);
    return newCat;
  },

  async getCustomers() {
    await delay();
    return [...customersData];
  },

  async createCustomer(customer: Omit<Customer, 'id' | 'totalOrders' | 'totalSpent'>) {
    await delay();
    const newCust: Customer = {
      ...customer,
      id: `cust_${Date.now()}`,
      totalOrders: 0,
      totalSpent: 0
    };
    customersData.unshift(newCust);
    return newCust;
  },

  async getOrders() {
    await delay();
    return [...ordersData];
  },

  async createOrder(orderData: Omit<Order, 'id' | 'date'>) {
    await delay();
    const now = new Date();
    const formattedDate = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
    
    const newOrder: Order = {
      ...orderData,
      id: `ORD-${Math.floor(1000 + Math.random() * 9000)}`,
      date: formattedDate
    };
    ordersData.unshift(newOrder);

    // Update customer stats if customer matched
    const custIndex = customersData.findIndex(c => c.name.toLowerCase() === orderData.customerName.toLowerCase());
    if (custIndex !== -1) {
      customersData[custIndex].totalOrders += 1;
      customersData[custIndex].totalSpent += orderData.total;
    }

    return newOrder;
  }
};
