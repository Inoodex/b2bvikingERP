# 🎛️ Spec: Enterprise Feature Toggles & Business Rules Control Center
**Module:** System-Wide Settings & Configuration (`00_master_plans`)  
**Phase:** Post-Phase 5 (System Hardening & Final Admin Polish)  
**Status:** 📌 Queued for Implementation (Immediately After Phase 5)  
**Document Standard:** Enterprise Architecture Specification  

---

## 1. Business Objective & Purpose

In enterprise ERP environments, business policies change frequently. A company may want automatic replenishment enabled during peak sales seasons, but disabled during cash-flow audits. Similarly, certain clients require mandatory automated vendor emails upon PO approvals, while others prefer manual review.

The **Enterprise Feature Toggles Center** provides a single, unified control panel in the Admin UI (`Admin ➔ Settings ➔ Business Rules & Feature Toggles`) where Super Admins can turn core automations and business constraints **ON / OFF** with instant real-time toggles without any code modifications.

---

## 2. Complete Inventory of System Toggles

### A. 🏭 Inventory & WMS Toggles (Phase 4)
| Toggle Key | Display Label | Description | Default State |
| :--- | :--- | :--- | :---: |
| `inventory_auto_replenish` | **Enable Auto-Replenishment Engine** | Nightly cron (`01:00 AM`) automatically scans low-stock products and generates Draft POs for primary suppliers. | `OFF` |
| `inventory_month_end_snapshot` | **Enable Month-End Valuation Cron** | Freezes inventory quantities and FIFO valuation amounts on the final day of each month for audit compliance. | `ON` |
| `inventory_enforce_bins` | **Enforce Warehouse Bin Selection** | Requires operators to select a specific `WarehouseBin` during GRN receiving and Delivery Order picking. | `OFF` |
| `inventory_low_stock_email` | **Send Low-Stock Email Alerts** | Sends instant email alerts to the Warehouse Manager whenever an item hits its reorder threshold. | `ON` |

---

### B. 📧 Automated Communications & Notifications
| Toggle Key | Display Label | Description | Default State |
| :--- | :--- | :--- | :---: |
| `mail_auto_po_to_vendor` | **Auto-Email Purchase Order to Vendor** | Automatically attaches and sends the official PO PDF to the vendor's email upon management approval. | `OFF` |
| `mail_quote_expiry_reminders` | **Quotation Expiry Reminders** | Daily cron checks expiring quotations and sends email reminders to customers before price locks expire. | `ON` |
| `mail_delivery_challan_customer` | **Auto-Email Delivery Challan (DO)** | Emails the signed delivery challan PDF to the customer when a shipment transitions to `Dispatched`. | `ON` |
| `mail_sales_invoice_customer` | **Auto-Email Commercial Invoices** | Automatically emails the official sales invoice to corporate accounts upon invoice finalization. | `ON` |

---

### C. 📦 Commercial Sales & Credit Control (Phase 3)
| Toggle Key | Display Label | Description | Default State |
| :--- | :--- | :--- | :---: |
| `sales_strict_credit_lock` | **Enforce Strict Credit Limit Lock** | Blocks order confirmation if a customer's total outstanding balance exceeds their credit limit. | `ON` |
| `sales_multi_level_approval` | **Enable Sales Order Approvals** | Routes sales orders exceeding threshold amounts through the multi-level approval workflow. | `ON` |
| `sales_allow_partial_do` | **Allow Partial Delivery Orders (DO)** | Permits warehouse managers to dispatch partial shipments across multiple delivery trips. | `ON` |

---

### D. 🛒 Procurement & Sourcing (Phase 2)
| Toggle Key | Display Label | Description | Default State |
| :--- | :--- | :--- | :---: |
| `procurement_require_cs_matrix` | **Require 3-Quote CS before PO** | Enforces minimum 3-vendor Comparison Statement approval before generating a formal Purchase Order. | `ON` |
| `procurement_auto_landed_cost` | **Auto-Allocate Landed Costs** | Distributes custom duty, freight, and port clearing costs across received GRN items on batch creation. | `ON` |

---

### E. 📈 Financial Accounting & General Ledger (Phase 5)
| Toggle Key | Display Label | Description | Default State |
| :--- | :--- | :--- | :---: |
| `accounts_auto_post_invoices` | **Auto-Post Sales Journals** | Automatically creates and posts Debit/Credit journal lines upon sales invoice confirmation. | `ON` |
| `accounts_auto_post_grn` | **Auto-Post Inventory Receipts** | Posts Goods-in-Transit (SIT) and Accounts Payable liability journals on GRN verification. | `ON` |
| `accounts_lock_closed_periods` | **Lock Closed Fiscal Years** | Strict prohibition on backdated journal postings or modifications to closed fiscal years. | `ON` |

---

## 3. Technical Implementation Architecture

### 3.1 Database Persistence
Store toggles in `general_settings` table via JSON column `feature_toggles` or discrete boolean fields:
```sql
ALTER TABLE general_settings ADD COLUMN feature_toggles JSON NULL;
```

### 3.2 Global Helper Accessor
Create a clean system helper:
```php
if (!function_exists('is_feature_enabled')) {
    function is_feature_enabled(string $key, bool $default = true): bool {
        $settings = \App\Models\GeneralSetting::first();
        $toggles = $settings?->feature_toggles ?? [];
        return isset($toggles[$key]) ? (bool) $toggles[$key] : $default;
    }
}
```

### 3.3 Command / Service Integration Example
Inside `AutoReplenishmentCron.php`:
```php
public function handle()
{
    if (!is_feature_enabled('inventory_auto_replenish', false)) {
        $this->info('Auto-Replenishment Engine is currently disabled in System Settings. Skipping execution.');
        return 0;
    }
    
    // Proceed with replenishment logic...
}
```

---

## 4. Execution Plan (Post-Phase 5)
1. **Step 1:** Create migration adding `feature_toggles` JSON to `general_settings`.
2. **Step 2:** Add tab **"Feature Toggles & Business Rules"** under `Admin ➔ Settings`.
3. **Step 3:** Bind toggles across Cron commands, Observers, and Approval Services.
4. **Step 4:** Test all switches (Enable ➔ Verify behavior, Disable ➔ Verify graceful bypass).
