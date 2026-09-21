# Enterprise Hybrid (B2B + B2C) Barcode Scanning & Dual-View Pricing Plan

**Document Version:** 1.0.0  
**Target System:** B2B Viking ERP  
**Location:** `docs/10_barcode_and_label_printing/b2b_b2c_dual_view_barcode_scan_and_pricing_plan.md`  
**Status:** Approved Architectural Blueprint  

---

## 1. Executive Summary & Business Architecture

B2B Viking operates a hybrid distribution network consisting of:
1. **The Main Company (Headquarters / Supplier):** Sells wholesale goods to affiliated outlets, franchise stores, and registered B2B wholesale buyers.
2. **Retail Outlets & Stores:** Sell goods directly to end consumers, tourists, and shoppers at specified retail/outlet prices.

### The Challenge
Every physical product carries **one single 1D GS1 Barcode** (e.g. `10002145-1933-31E5`). Two completely different user personas will scan this exact same barcode:
- **Persona A: End Consumer (B2C Guest):** Must instantly see the verified authentic product details and the **Customer / Outlet Price (`185 kr.`)**. Wholesale prices must remain **100% confidential and hidden**.
- **Persona B: Registered Wholesale Client (B2B Outlet):** When authenticated, must see the **Wholesale Price (`129 kr.`)**, Minimum Order Quantity (MOQ), tiered volume pricing, and instant re-order actions.

This document outlines the complete architectural roadmap to implement **Dual-View Role-Based Barcode Resolution** without requiring two separate barcodes on the product.

---

## 2. Database Price Mapping Reference

In B2B Viking ERP, the product pricing structure is mapped as follows:

| Conceptual Price | Database Column | Product 1930 Example (`Square Bag 40*45`) | Visibility Rules |
| :--- | :--- | :--- | :--- |
| **Outlet / Customer Price** | `products.price` | `kr. 185.00` | **Public & Unauthenticated (B2C)**. Printed on product stickers. |
| **Wholesale / Supply Price** | `products.outlet_price` | `kr. 129.00` | **Restricted (B2B Only)**. Visible only to authenticated wholesale accounts. |
| **Purchase / Cost Price** | `products.purchase_price` | `kr. 60.00` | **Restricted (Admin & Procurement Only)**. |

---

## 3. Core Architectural Components

```
                       [ Single 1D GS1 Barcode on Product ]
                                      |
                 +--------------------+--------------------+
                 |                                         |
     [ Hardware Laser Gun ]                       [ Smartphone Camera ]
         (POS / Warehouse)                           (B2C or B2B User)
                 |                                         |
     Direct Keypress Input                        Google Search / Scan URL:
     Into ERP / Cart (<0.01s)                 https://b2bviking.com/scan/{barcode}
                                                           |
                                            +--------------+--------------+
                                            |                             |
                                    [ Guest / B2C ]              [ Authenticated B2B ]
                                            |                             |
                                  - Verified Authentic          - Wholesale Price: 129 kr.
                                  - Customer Price: 185 kr.     - Bulk MOQ & Cart Action
                                  - Wholesale Hidden            - B2B Quick Reorder
                                  - Apply for B2B Account
```

---

## 4. Implementation Specification

### 4.1. Public Scan Controller (`BarcodeScanController`)
The public scan endpoint (`GET /scan/{barcode}`) will inspect the caller's session and authentication context:

```php
public function resolve(string $barcode)
{
    // 1. Resolve Product or Handling Unit by GS1 Barcode
    $product = Product::with(['category', 'brand', 'variants'])->where('barcode', $barcode)->first();
    
    if (!$product) {
        $variant = ProductVariant::with(['product.category', 'product.brand'])->where('barcode', $barcode)->first();
        if ($variant) {
            return $this->renderVariantScan($variant);
        }
        return $this->renderHandlingUnitOrNotFound($barcode);
    }

    // 2. Check Authentication Role
    $user = auth()->user();
    $isB2BClient = $user && ($user->is_wholesale_approved || $user->hasRole('b2b_client'));

    if ($isB2BClient) {
        return view('frontend.products.scan_card_b2b', compact('product', 'user'));
    }

    return view('frontend.products.scan_card_guest', compact('product'));
}
```

### 4.2. Guest / B2C Card View (`scan_card_guest.blade.php`)
- **No Login Required:** Accessible directly via smartphone scan or Google Search click.
- **Displayed Information:**
  - Product Title & High-Resolution Image.
  - Authentic GS1 GPC Verified Badge (`GS1: 10002145 - Souvenirs / Novelty`).
  - Retail Price: **`kr. 185.00`** (`products.price`).
  - Available Sizes / Colors.
  - Store / Outlet Availability.
- **Security Safeguards:**
  - Wholesale fields (`outlet_price`), purchase costs, and raw inventory numbers are completely stripped from HTML and JSON.
- **Conversion Hook:**
  - *"Are you a business or retailer? [Apply for a B2B Wholesale Account] to unlock wholesale catalog pricing."*

### 4.3. Authenticated B2B View (`scan_card_b2b.blade.php`)
- **Displayed Information:**
  - Wholesale Unit Price: **`kr. 129.00`** (`products.outlet_price`).
  - Suggested Retail Price (MSRP): **`kr. 185.00`** (`products.price`).
  - Real-time Warehouse Inventory Stock for replenishment.
  - Minimum Order Quantity (MOQ) & Case Pack Multiples.
  - **1-Click "Add Case to B2B Order"** button.

---

## 5. Google Search & SEO Indexing Strategy

To ensure that when an end customer scans the 1D barcode with Google Lens or a standard smartphone camera, Google Search immediately presents the product at the top of the search results:

### 5.1. Schema.org JSON-LD Metadata
In the public product page and scan verification page:
```html
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "Square Bag 40*45",
  "image": "https://b2bviking.com/storage/products/square-bag.jpg",
  "description": "Premium authentic souvenir bag.",
  "sku": "BAG-4045",
  "gtin": "10002145-1933-31E5",
  "brand": {
    "@type": "Brand",
    "name": "Copenhagen Tourist Point"
  },
  "offers": {
    "@type": "Offer",
    "url": "https://b2bviking.com/scan/10002145-1933-31E5",
    "priceCurrency": "DKK",
    "price": "185.00",
    "availability": "https://schema.org/InStock",
    "seller": {
      "@type": "Organization",
      "name": "B2B Viking"
    }
  }
}
</script>
```

### 5.2. Automated XML Sitemap Generator
- An automated cron/command (`php artisan sitemap:generate-barcodes`) will generate `public/sitemap-products.xml`.
- Each URL will include the canonical product link and barcode identifier.
- Submitted to Google Search Console upon production deployment.

---

## 6. Logistics & Packaging Handling Units (Cartons & Pallets)

For master cartons and shipping pallets during stock transfer or freight:
1. **Shipping Label (4×6 inch):** Contains the 1D GS1 Barcode (`CTN-10001363-2026-0001`) and a 2D QR Code targeting `https://b2bviking.com/scan/CTN-10001363-2026-0001`.
2. **Delivery Driver / Receiving Staff Experience:**
   - Scanning the label with any smartphone camera instantly opens the **Carton Packing Manifest Card**.
   - Displays: Total Units (e.g. 120 pcs), child box breakdown, dispatch origin, and destination outlet.

---

## 7. Execution Checklist & Milestones

- [x] **Milestone 1:** Pure 1D Barcode label studio with GS1 GPC category integration.
- [x] **Milestone 2:** Packaging Units workbench supporting empty master cartons and arbitrary child nesting.
- [x] **Milestone 3:** Price mapping verification (`price = 185 kr.` for retail, `outlet_price = 129 kr.` for wholesale).
- [ ] **Milestone 4 (Future Phase):** Dual-View Blade template split (`scan_card_guest` vs `scan_card_b2b`).
- [ ] **Milestone 5 (Future Phase):** Dynamic Schema.org JSON-LD injection on public scan routes.
- [ ] **Milestone 6 (Future Phase):** XML Sitemap generation for Google Search Console indexing upon production launch.

---
*Prepared by: Antigravity AI Engineering Team on behalf of B2B Viking ERP.*
