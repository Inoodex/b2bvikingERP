# 🏷️ Spec: Enterprise Product Barcode & Label Printing Studio
**Module:** `10_barcode_and_label_printing`  
**Standard:** SAP S/4HANA EWM & Odoo 17 `product.label.layout` Enterprise Architecture  
**Status:** Approved Technical Specification  
**Target System:** Copenhagen Tourist Point (b2bviking.com) — B2B Viking ERP  

---

## 1. 📌 Executive Summary & Business Objective

In high-volume retail, souvenir, and wholesale apparel operations, product price tagging and inventory identification must be instantaneous and reliable.

This module delivers a centralized, 100% offline **Enterprise Product Barcode & Label Printing Studio** integrated into the **Inventory & WMS** ecosystem:
1. **Search & Queue Workflow:** Admin searches products via keyword, SKU, or barcode scanner, or filters by category, and stages them in a clean **Print Queue Table**.
2. **Dynamic Copy Management:** Set print copies manually with a numeric stepper (`- [ 1 ] +`) or sync all items to active on-hand inventory stock with a single click.
3. **100% Center-Aligned Vector Labels:** Crisp, scalable vector SVG barcodes (Code-128 Subset B) with zero edge-drift and 100% centered alignment for Zebra/thermal rolls and A4 laser sheets.
4. **Separation of Concerns:** Warehouse bin and shelf markers are dedicated to the WMS Warehouse Bin module (`/admin/warehouse-bins/{id}/print-barcode`), leaving this workspace 100% focused on **Retail Merchandise and Apparel**.
5. **Zero-Dependency Vector Output:** 100% pure PHP inline vector SVG rendering. Zero external API calls (`tec-it.com` eliminated), zero network latency, and pin-sharp scannability at 203 DPI and 300 DPI.

---

## 2. 🏛️ System Topology & User Experience

```
┌────────────────────────────────────────────────────────────────────────┐
│           ENTERPRISE PRODUCT BARCODE LABELS ARCHITECTURE               │
├────────────────────────────────────────────────────────────────────────┤
│ 1. SEARCH & QUEUE WORKSPACE (Left Panel)                               │
│    ├── Fast Search Input (Product Name, SKU, Barcode Scanner Input)    │
│    ├── Category Filter Dropdown (All Categories, Viking Apparel, etc.) │
│    ├── Instant Search Results Dropdown List                            │
│    ├── Selected Items Queue Table:                                     │
│    │   ├── Item Name & SKU Chip                                        │
│    │   ├── On-Hand Stock Qty Badge                                     │
│    │   ├── Print Copies Spinner (- [ 1 ] +)                            │
│    │   └── Remove Action / Clear All                                   │
│    ├── Batch Actions: "Set All to On-Hand Stock Qty"                   │
│    ├── Physical Presets (50x30mm, 38x25mm, A4 3x8, A4 2x7)             │
│    └── Content Toggles (Show Price, Title, SKU, Brand, Barcode Digits) │
├────────────────────────────────────────────────────────────────────────┤
│ 2. LIVE STICKY WYSIWYG PREVIEW STUDIO (Right Panel)                    │
│    ├── Instant Centered Sticker Preview Canvas (Updates in real-time)  │
│    ├── Total Staged Sticker Counter Badge                              │
│    └── Primary Action: "Print Barcode Labels Now"                      │
├────────────────────────────────────────────────────────────────────────┤
│ 3. PRINT ENGINE & MEDIA SPECIFICATIONS                                 │
│    ├── 100% Centered CSS Flexbox Alignment                            │
│    ├── Native Browser Print Window (@media print)                      │
│    └── Code-128 Subset B Pure PHP Vector SVG Generator                │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 3. 📐 Physical Label Formations & Print Presets

| Preset Key | Formation Name | Physical Dimensions | Hardware Target | Content Composition |
|:---|:---|:---|:---|:---|
| `thermal_50x30` | **Retail Apparel & Souvenir Tag** | `50mm × 30mm` | 2" Thermal Roll Printer (Zebra, TSC, Xprinter) | Centered Store Name, Title, Code-128 SVG, Barcode Digits, SKU & Price in DKK (`kr.`) |
| `thermal_38x25` | **Compact Jewelry / Price Tag** | `38mm × 25mm` | Small Roll Thermal Printer | Centered Title, Compact Code-128 SVG, Barcode Digits, Price in DKK |
| `a4_3x8_sheet`  | **A4 Sheet 24-Up (Avery 7160)** | Standard A4 (3 × 8 Grid) | Office Laser / Inkjet Printer | 24 centered labels/sheet, 63.5mm × 33.9mm per label |
| `a4_2x7_sheet`  | **A4 Sheet 14-Up (Large Grid)** | Standard A4 (2 × 7 Grid) | Office Laser / Inkjet Printer | 14 large centered labels/sheet, 99.1mm × 38.1mm per label |

---

## 4. ⚙️ Architectural Layering & Patterns

### 4.1 Thin Controller Pattern
- `BarcodeLabelController`: Acts strictly as an HTTP request orchestrator (~70-80 lines). Delegating 100% of domain logic to `BarcodeLabelService`.
- Form Requests: `BarcodeSearchRequest`, `BarcodeLabelPreviewRequest`, `BarcodeLabelPrintRequest` handle all input validation.

### 4.2 Offline Native Barcode Generator
- `NativeBarcodeGenerator`: Pure PHP implementation of Code-128 Subset B. Encodes ASCII 32–126 with start, stop, and modulo-103 checksum characters, returning pure vector SVG XML.

### 4.3 Service Layer
- `BarcodeLabelService`: Resolves presets, performs fast product searches, ensures deterministic barcodes exist on products (`PRD-{cat}-{id}-{hash}`), and parses label configurations into view models.
