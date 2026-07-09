# 06 — API Contract Map

## Extracted Backend Endpoint Contracts (from HAR & JS Analysis)

### 1. Account Info
- **Endpoint**: `GET /api/kyte-web/account-info/:uid`
- **Purpose**: Fetch current store profile, business name, currency, language.
- **Response Shape**:
```json
{
  "id": "6mxHACXrknbFUl",
  "businessName": "My Kyte Store",
  "email": "owner@kyteapp.com",
  "currency": "USD",
  "symbol": "$",
  "phone": "+123456789",
  "country": "US"
}
```

### 2. Products List & Count
- **Endpoint**: `GET /api/kyte-web/products/:uid/count` & `GET /api/kyte-web/products/:uid`
- **Purpose**: Retrieve product inventory items.
- **Response Shape**:
```json
{
  "count": 48,
  "items": [
    {
      "id": "prod_101",
      "name": "Classic T-Shirt",
      "price": 25.00,
      "cost": 10.00,
      "stock": 150,
      "category": "Apparel",
      "code": "TS-001",
      "imageUrl": "https://storage.googleapis.com/kyte-7c484.appspot.com/sample1.png"
    }
  ]
}
```

### 3. Customer CRM
- **Endpoint**: `GET /api/kyte-web/customer/:uid/count` & `GET /api/kyte-web/customer/:uid`
- **Response Shape**:
```json
{
  "count": 12,
  "items": [
    {
      "id": "cust_301",
      "name": "Jane Doe",
      "email": "jane@example.com",
      "phone": "+1 555-0199",
      "totalOrders": 5,
      "totalSpent": 185.50
    }
  ]
}
```

### 4. Sales & Orders
- **Endpoint**: `GET /api/kyte-web/sale/:uid/count` & `POST /api/kyte-web/sale`
- **Request Shape**:
```json
{
  "customerId": "cust_301",
  "items": [
    { "productId": "prod_101", "quantity": 2, "unitPrice": 25.00 }
  ],
  "paymentMethod": "CASH",
  "subtotal": 50.00,
  "discount": 0,
  "total": 50.00
}
```

### 5. Subscription Summary
- **Endpoint**: `GET /subscription/summary`
- **Response Shape**:
```json
{
  "plan": "PRO",
  "status": "ACTIVE",
  "renewsAt": "2027-01-01T00:00:00Z"
}
```
