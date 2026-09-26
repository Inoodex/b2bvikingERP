# 🧠 Skill: Enterprise Product Barcode Generation, Label Customizer & Print Engine
**Category:** `10_barcode_and_label_printing`  
**Standard:** Enterprise Execution Playbook  

---

## 1. Native Vector SVG Barcode Generation Engine (Offline & Fast)

To guarantee zero dependencies and zero network round-trips, the system utilizes a high-performance, native Code-128 (subset B) SVG barcode encoder.

### 1.1 Code-128 Encoder Specification
- Encodes standard alphanumeric strings (ASCII 32 to 126).
- Computes weighted modulo-103 checksum and appends standard Stop pattern.
- Generates inline vector SVG with `<rect>` elements.
- Clean vector rendering at 203 DPI and 300 DPI without pixelation or antialiasing blur.

---

## 2. 100% Center-Aligned CSS Specifications

Both thermal rolls and A4 sheets enforce strict horizontal center alignment to eliminate printer edge clipping:

```css
.sticker-centered-layout {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    width: 100%;
}

.stk-company {
    font-size: 6.5pt;
    text-transform: uppercase;
    text-align: center;
    width: 100%;
}

.stk-title {
    font-size: 6.5pt;
    text-align: center;
    width: 100%;
}

.stk-barcode-box {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 2px auto;
}

.stk-barcode-box svg {
    margin: 0 auto;
    display: block;
}

.stk-code {
    font-size: 6.5pt;
    font-family: monospace;
    font-weight: 800;
    text-align: center;
    width: 100%;
}

.stk-footer-centered {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    text-align: center;
}
```

---

## 3. Physical Formats & Paged Media

### 3.1 Thermal Continuous Rolls
- **50mm × 30mm**: `@page { size: 50mm 30mm; margin: 0; }`
- **38mm × 25mm**: `@page { size: 38mm 25mm; margin: 0; }`

### 3.2 Standard A4 Laser Sheets
- **3 × 8 Grid (24 Labels/Page - Avery 7160)**: Label size `63.5mm × 33.9mm`
- **2 × 7 Grid (14 Labels/Page - Large Format)**: Label size `99.1mm × 38.1mm`
