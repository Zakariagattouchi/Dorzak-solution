# Missing Items & Manual Review Notes

## Unrecovered Third-Party Assets
1. **Third-Party Payment Gateway Webhooks**: Stripe / Firebase Payment SDK tokens were stripped for security compliance. Payments are currently processed through the mock API layer (`src/api/mockApi.ts`).
2. **Custom Cloud Storage Images**: Remote image URLs extracted from HAR (`https://storage.googleapis.com/...`) rely on public Unsplash placeholders for offline reproducibility.

---

## Assumptions Made
- Default currency set to USD ($) with options for EUR (€), GBP (£), CAD ($).
- Default tax rate set to 8.5% adjustable in Business Settings.
- POS cart automatically calculates totals in real-time without requiring a page refresh.
