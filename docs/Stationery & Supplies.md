# 📦 Stationery & Supplies Master Technical Plan

Please refer to the complete technical implementation and architecture master document:
👉 **[docs/stationery_and_supplies_master_plan.md](file:///home/agent47/Sites/b2bvikingERP/docs/stationery_and_supplies_master_plan.md)**

### Quick Reference:
- **Module Name:** `Stationery & Supplies`
- **Sidebar Integration:** Under admin sidebar with icon `fas fa-pencil-ruler`
- **Sub-Menus:**
  1. 📋 **Items Master & Stock** (`admin.stationery-supplies.index`)
  2. 📥 **Purchases (Stock In)** (`admin.stationery-supplies.purchases`)
  3. 📤 **Department Issues (Stock Out)** (`admin.stationery-supplies.consumptions`)
  4. 📊 **Expense & Audit Ledger** (`admin.stationery-supplies.ledger`)
- **Key Capabilities:**
  - Automated Weighted Average Cost (WAC) calculations on purchase
  - Negative-stock prevention guard
  - Live stock status badges (🔴 Out of Stock, 🟡 Low Stock Alert, 🟢 Healthy)
  - Departmental cost center expense tracking (Warehouse Packing, Shipping, Office, etc.)
  - 100% isolated from customer commercial sales catalog (future-ready commercial toggle included)

