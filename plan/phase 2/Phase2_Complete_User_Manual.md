# B2B Viking ERP — Phase 2 Complete User Manual
**Procurement, International Import (LC), Landed Cost Engine & Accounts Payable**

---

# 🇧🇩 বাংলা সংস্করণ (Bangla Version)

## 📌 সূচিপত্র ও সম্পূর্ণ কাজের সময়ক্রম (Execution Workflow Sequence)
বিজিবি ভাইকিং ইআরপি (B2B Viking ERP)-এর Phase 2 প্রকিউরমেন্ট ও ইম্পোর্ট মোডিউলে কাজ করার জন্য নিচের ৪টি ধারাবাহিক পদক্ষেপে (Step 1 থেকে Step 4) কাজ পরিচালনা করতে হবে:

- **Step 1: RFQ, Vendor Quotations & Comparison Statement (CS)** — চাহিদা অনুযায়ী দরপত্র আহ্বান, ভেন্ডর কোটেশন জমা ও সর্বনিম্ন দরদাতা (L1 Bidder) নির্বাচন।
- **Step 2: Purchase Order (PO), Proforma Invoice (PI) & LC Register** — পারচেজ অর্ডার জারি, স্প্লিট পিও জেনারেশন, পিআই আপলোড ও এলসি ডিউটি হিসাব।
- **Step 3: Shipment Logistics, Stock-in-Transit (SIT), GRN QC & Landed Cost Engine** — শিপমেন্ট ট্র্যাকিং, কাস্টমস ক্লিয়ারেন্স, গুদামে মাল গ্রহণ (GRN QC Inspection), ট্রু ল্যান্ডেড কস্ট হিসাব এবং ভেন্ডর রিটার্ন ও ডেবিট নোট।
- **Step 4: Procurement Financials, Vendor Bills, Payment Vouchers, Supplier Ledger & Purchase Reports** — 3-Way Matching ইনভয়েস, পেমেন্ট ভাউচার, ডেবিট নোট নিস্পত্তি, সাপ্লায়ার লেজার, এপি এজিং ও রিপোর্টস।

---

## 🔹 Step 1: RFQ, Vendor Quotations & Comparison Statement (CS)

### ১.১ Request for Quotation (RFQ) ইস্যু করা
যখন কোনো পণ্য কেনার প্রয়োজন হবে, তখন সিস্টেমে রিকোয়েস্ট তৈরি করে ভেন্ডরদের কাছে কোটেশনের জন্য রিকোয়েস্ট পাঠাতে হবে।

**কীভাবে RFQ তৈরি করবেন?**
1. বাম পাশের নেভিগেশন মেনু থেকে **Procurement > RFQs** সেকশনে যান।
2. **Create New RFQ** বাটনে ক্লিক করুন।
3. ক্রয়ের জন্য নির্ধারিত প্রোডাক্ট এবং তার পরিমাণ (Quantity) নির্বাচন করুন।
4. যেসব ভেন্ডর বা সাপ্লায়ারদের কাছে দরপত্র পাঠাতে চান, তাদের সিলেক্ট করুন।
5. **Save / Send** বাটনে ক্লিক করুন। সিস্টেম স্বয়ংক্রিয়ভাবে একটি পিডিএফ RFQ ডকুমেন্ট তৈরি করে ভেন্ডরদের ইমেইলে পাঠিয়ে দেবে।

---

### ১.২ Vendor Quotation এন্ট্রি দেওয়া
ভেন্ডররা ইমেইলে দর জানালে তা সিস্টেমে এন্ট্রি করতে হবে।

**কীভাবে ভেন্ডরের দাম সিস্টেমে জমা দেবেন?**
1. **Procurement > RFQs** পেজে গিয়ে নির্দিষ্ট RFQ-টির ডিটেইলসে যান।
2. ভেন্ডরের নামের পাশে **Add Quotation** বাটনে ক্লিক করুন।
3. ভেন্ডরের প্রদত্ত একক দর (Unit Price), ডেলিভারি সময় এবং পেমেন্ট শর্তাবলি ইনপুট দিয়ে সেভ করুন।

---

### ১.৩ Comparison Statement (CS) তৈরি ও অনুমোদন
একাধিক ভেন্ডরের দর তুলনা করে সর্বনিম্ন দরদাতা নির্বাচন করার জন্য CS তৈরি করতে হয়।

**কীভাবে CS তৈরি ও অনুমোদন করবেন?**
1. RFQ Details পেজ থেকে **Generate CS** বাটনে ক্লিক করুন।
2. সিস্টেম পাশাপাশি সকল ভেন্ডরের দর সাজিয়ে একটি ম্যাট্রিক্স টেবিল দেখাবে।
3. বৈচিত্র্যময় মুদ্রা থাকলে সিস্টেম স্বয়ংক্রিয়ভাবে বেস কারেন্সিতে কনভার্ট করে সর্বনিম্ন দরদাতাকে (L1 Bidder) সবুজ রঙে হাইলাইট করবে।
4. **Submit CS for Approval** বাটনে ক্লিক করে অনুমোদনের জন্য পাঠান। কর্তৃপক্ষ অনুমোদন দিলে তা PO তৈরির জন্য প্রস্তুত হবে।

---

## 🔹 Step 2: Purchase Order (PO), Proforma Invoice (PI) & LC Register

### ২.১ স্বয়ংক্রিয় Purchase Order (PO) তৈরি ও স্প্লিটিং
CS অনুমোদিত হওয়ার পর বিজয়ী ভেন্ডরদের জন্য অফিশিয়াল PO ইস্যু করতে হয়।

**কীভাবে PO তৈরি ও স্প্লিট করবেন?**
1. অনুমোদিত CS পেজ থেকে **+ Generate PO** বাটনে ক্লিক করুন।
2. একাধিক ভেন্ডর বিভিন্ন আইটেমে বিজয়ী হলে সিস্টেম স্বয়ংক্রিয়ভাবে আলাদা **Split POs (`PO-00001`, `PO-00002`...)** তৈরি করবে।
3. **নিরাপত্তা সেফগার্ড (Concurrency Locking):** একাধিক ইউজার একই সাথে PO তৈরি করতে গেলে ডাটাবেস লেভেলে `lockForUpdate()` সেফগার্ড ডুপ্লিকেট PO হওয়া থেকে রক্ষা করবে।
4. **Procurement > Purchase Orders (PO)** পেজে গিয়ে **Preview PDF**, **Download PDF** অথবা **Send PO Email to Supplier** বাটনে ক্লিক করে সরাসরি ভেন্ডরকে ইমেইল পাঠাতে পারবেন।

---

### ২.২ Proforma Invoice (PI) ডকুমেন্ট সংযোজন
ভেন্ডরের পাঠানো প্রোফরমা ইনভয়েস সিস্টেমে আপলোড করতে হয়।

**কীভাবে PI ফাইল আপলোড করবেন?**
1. নির্দিষ্ট PO Details পেজে যান।
2. **Proforma Invoice (PI)** কার্ডের নিচে PI নম্বর, তারিখ ও মোট টাকার পরিমাণ দিন।
3. ভেন্ডরের পাঠানো ফাইলটি (PDF/Image) আপলোড করে **Upload & Attach PI** ক্লিক করুন। PO মাইলস্টোন স্বয়ংক্রিয়ভাবে **`pi_attached`** হবে।

---

### ২.৩ International Letter of Credit (LC) & Expenses Breakdown
বৈদেশিক ইম্পোর্টের ক্ষেত্রে এলসি রেজিস্টার করা এবং ১৩টি আমদানিকৃত খরচ ট্র্যাকিং করা।

**কীভাবে LC রেজিস্টার ও অ্যামেন্ডমেন্ট করবেন?**
1. PO Details পেজের **Register LC** ফর্মে এলসি নম্বর, ইস্যুয়িং ব্যাংক, এলসি অ্যামাউন্ট, মার্জিন % এবং মেয়াদের তারিখ লিখে সাবমিট দিন।
2. **Procurement > LC Register** পেজে সকল এলসির তালিকা এবং **১৩টি আমদানিকৃত খরচ (CD, RD, SD, VAT, AIT, AT, Margin, Insurance, Freight, C&F Agent Fees ইত্যাদি)** দেখতে পারবেন।
3. এলসির মেয়াদ বা অ্যামাউন্ট বাড়লে **Record LC Amendment** বাটনে ক্লিক করে অ্যামেন্ডমেন্ট সংরক্ষণ করুন।

---

## 🔹 Step 3: Shipment Logistics, Stock-in-Transit (SIT), GRN QC & Landed Cost Engine

### ৩.১ Shipment Tracking & Stock-in-Transit (SIT)
শিপিং ভেসেল, কনটেইনার নম্বর, বিল অফ লেডিং (BL/AWB) ডকুমেন্ট আপলোড এবং অ্যারাইভাল ট্র্যাকিং।

**কীভাবে শিপমেন্ট ট্র্যাকিং ম্যানেজ করবেন?**
1. **Procurement > Shipments & SIT** অথবা PO পেজ থেকে **Register Shipment** বাটনে ক্লিক করুন।
2. Vessel/Flight No, Container No, BL/AWB No, Port of Loading, Port of Discharge, ETD, ETA এবং BL/Packing List ডকুমেন্ট আপলোড করুন।
3. শিপমেন্টের অগ্রগতির সাথে সাথে মাইলস্টোন স্ট্যাটাস **`shipped`** ➔ **`in_transit`** ➔ **`arrived`** ➔ **`cleared`** (Customs Cleared) হিসেবে আপডেট করুন।

---

### ৩.২ Goods Received Note (GRN), Quality Control (QC) & Validation Guards
গুদামে মাল গ্রহণ করা, গুণগত মান যাচাই করা এবং ইনভেন্টরি স্টক আপডেট করা।

**কীভাবে GRN তৈরি ও QC পরিচালনা করবেন?**
1. **Strict Clearance Guard:** আমদানিকৃত মালের ক্ষেত্রে শিপমেন্ট স্ট্যাটাস **`Customs Cleared`** না হওয়া পর্যন্ত GRN রিসিভ বাটন লক থাকবে।
2. **Over-Receipt Validation Guard:** অবশিষ্ট মালের পরিমাণের বেশি মাল রিসিভ করা (`accepted_qty > remaining_qty`) সম্পূর্ণ নিষিদ্ধ ও ব্লকড।
3. **Procurement > Goods Receipts (GRN)** পেজে গিয়ে **Receive Goods (Create GRN)** ক্লিক করুন। গন্তব্য **Outlet / Warehouse** সিলেক্ট করুন।
4. প্রতিটি মালের বিপরীতে **Accepted Qty** এবং রিজেক্ট হলে **Rejected Qty** সহ **Rejection Reason** লিখে **Submit GRN & Update Inventory** দিন।
5. সিস্টেমের স্টক Ledgers-এ Landed Unit Cost সহ মাল স্বয়ংক্রিয়ভাবে যুক্ত হবে এবং **Stream / Print Official GRN PDF** জেনারেট হবে।

---

### ৩.৩ Weighted Average Landed Cost Allocation Matrix
১৩টি আমদানিকৃত খরচকে মালের মূল্যের ওজনের অনুপাত অনুযায়ী বন্টন করে পন্যের প্রকৃত কেনা দাম হিসাব করা।

**কীভাবে Landed Cost Matrix দেখবেন?**
1. PO বা GRN Details পেজ থেকে **View Landed Cost Matrix** বাটনে ক্লিক করুন।
2. সেখানে মূল কেনা দাম (PO Base Unit Cost), আনুপাতিক ডিউটি খরচ (Allocated LC Overhead) এবং চূড়ান্ত কেনা দাম (**True Landed Unit Cost**) এর পরিষ্কার ব্রেকডাউন দেখতে পাবেন।

---

### ৩.৪ Vendor Returns & Debit Notes
QC-তে রিজেক্ট হওয়া মালামাল ভেন্ডরকে ফেরত দিয়ে ডেবিট নোট ইস্যু করা।

**কীভাবে Vendor Return ও Debit Note তৈরি করবেন?**
1. QC স্ট্যাটাস Partial বা Failed হওয়া GRN পেজ থেকে **Process Vendor Return (Debit Note)** ক্লিক করুন।
2. সিস্টেম ডাইনামিক `total_claim_amount` হিসাব করে **Vendor Return Record (`RET-XXXX`)** এবং অফিশিয়াল **Debit Note (`DN-XXXX`)** তৈরি করবে।
3. **Procurement > Vendor Returns** পেজে সকল ডেবিট নোটের তালিকা দেখতে পাবেন।

---

## 🔹 Step 4: Procurement Financials, Vendor Bills, Payment Vouchers, Supplier Ledger & Reports

### ৪.১ Vendor Bills / Invoices (3-Way Matching)
গুদামে গ্রহণ করা মালের ভিত্তিতে অফিশিয়াল বিল তৈরি করা এবং ডেবিট নোট কাটাকাটি করা।

**কীভাবে Vendor Bill তৈরি করবেন?**
1. **Procurement > Vendor Bills** পেজে গিয়ে **Create Vendor Bill** সিলেক্ট করুন।
2. Purchase Order ও GRN নির্বাচন করলে কাস্টমস ল্যান্ডেড কস্ট অনুযায়ী বিল তৈরি হবে।
3. ভেন্ডরের কোনো বকেয়া Debit Note থাকলে তা বিলের মোট টাকা থেকে স্বয়ংক্রিয়ভাবে ক্রেডিট এডজাস্টমেন্ট হিসেবে মাইনাস হবে।

---

### ৪.২ Multi-Currency Payment Vouchers (`PAY-XXXX`)
সাপ্লায়ারকে ক্যাশ, ব্যাংক ট্রান্সফার, চেক বা এলসি মার্জিন মারফতে টাকা পরিশোধ করা।

**কীভাবে Payment Voucher এন্ট্রি দেবেন?**
1. **Procurement > Purchase Payments** পেজে গিয়ে **Record Payment Voucher** ক্লিক করুন।
2. Vendor Name, Bill No, Payment Date, Amount এবং Payment Method নির্বাচন করুন। বৈচিত্র্যময় মুদ্রা হলে এক্সচেঞ্জ রেট দিলে স্বয়ংক্রিয়ভাবে বেস কারেন্সিতে কনভার্ট হবে।
3. **Process Payment** দিলে অফিশিয়াল পেমেন্ট ভাউচার জেনারেট হবে এবং **Print PDF Voucher** নেওয়া যাবে।

---

### ৪.৩ Enterprise Debit Note Settlements (3 Modes)
ডেবিট নোট ৩টি উপায়ে নিস্পত্তি করার পদ্ধতি:
- **Mode A (Bill Credit Deduction):** নতুন ইনভয়েসের টাকা থেকে কেটে রাখা (অটোমেটিক)।
- **Mode B (Product Replacement Swap):** ভেন্ডর রিজেক্ট মালের বদল নতুন ভালো মাল পাঠালে `Procurement > Vendor Returns`-এ গিয়ে **Settle via Product Replacement** দিয়ে নতুন মাল গুদামে রিসিভ করা।
- **Mode C (Direct Money Refund):** ভেন্ডর ব্যাংকে টাকা ফেরত দিলে **Settle via Direct Money Refund** দিয়ে রিফান্ড ভাউচার `RCN-XXXX` তৈরি করে ব্যালেন্স জমা করা।

---

### ৪.৪ Supplier Ledger, AP Aging Statement & Reports
সাপ্লায়ারের রানিং হিসাব ও পারচেজ রিপোর্ট দেখা।

**কীভাবে Supplier Ledger ও Reports দেখবেন?**
1. **Procurement > Supplier Ledger** (অথবা **Accounts > Vendor Ledger**) পেজে গিয়ে ভেন্ডরের রানিং ব্যালেন্স দেখুন, **View Statement** থেকে লেনদেন সময়ক্রম এবং **AP Aging Analysis** (0-30, 31-60, 61-90, 90+ দিন) দেখুন। **Export PDF Statement** দিয়ে বকেয়া স্বীকৃতি পত্র প্রিন্ট করুন।
2. **Reports > Procurement Reports** সেকশনে গিয়ে ১৩টি পারচেজ রিপোর্ট (Supplier-wise, Item-wise, Total Value, Purchase vs Last Year, PR Status, PO Status ইত্যাদি) সার্ভার-সাইড ডাটাটেবিল ফিল্টারিং, পেজিনেশন ও এক্সপোর্ট বাটন সহ ব্যবহার করুন।

---
---

# 🇬🇧 English Version

## 📌 Master Workflow Sequence
Follow these 4 logical sequential steps to manage the Procurement, Import Costing, and Accounts Payable processes in B2B Viking ERP:

- **Step 1: RFQ, Vendor Quotations & Comparison Statement (CS)** — Requisitioning, vendor bidding, and L1 bidder selection.
- **Step 2: Purchase Order (PO), Proforma Invoice (PI) & LC Register** — PO generation, split POs, PI attachment, and import duty tracking.
- **Step 3: Shipment Logistics, Stock-in-Transit (SIT), GRN QC & Landed Cost Engine** — Vessel logistics, customs clearance, warehouse GRN QC, Landed Cost Matrix, and Debit Notes.
- **Step 4: Procurement Financials, Vendor Bills, Payment Vouchers, Supplier Ledger & Purchase Reports** — 3-Way Matching, Payment Vouchers, Debit Note settlements, Supplier Ledger, AP Aging, and Reports.

---

## 🔹 Step 1: RFQ, Vendor Quotations & Comparison Statement (CS)

### 1.1 Request for Quotation (RFQ)
Create RFQs from approved requisitions to request vendor pricing.
1. Go to **Procurement > RFQs** from the left menu.
2. Click **Create New RFQ**, select required products/quantities, select target suppliers, and click **Save / Send** to automatically email PDF RFQs.

### 1.2 Vendor Quotation Entry
1. Open the specific RFQ details under **Procurement > RFQs**.
2. Click **Add Quotation** next to the vendor name, input unit prices and terms, and save.

### 1.3 Comparison Statement (CS) Matrix
1. Click **Generate CS** from the RFQ details page to view a side-by-side vendor comparison matrix.
2. The system converts currencies to Base Currency and highlights the lowest L1 bidder in green.
3. Click **Submit CS for Approval** for executive authorization.

---

## 🔹 Step 2: Purchase Order (PO), Proforma Invoice (PI) & LC Register

### 2.1 Automatic Purchase Order (PO) & Split PO Generation
1. Click **+ Generate PO** on an approved CS. If multiple vendors win different items, the system creates separate **Split POs (`PO-00001`, `PO-00002`...)**.
2. Database-level `lockForUpdate()` prevents race conditions and duplicate PO creation.
3. Access **Procurement > Purchase Orders (PO)** to **Preview PDF**, **Download PDF**, or **Send PO Email to Supplier**.

### 2.2 Proforma Invoice (PI) Attachment
1. Open PO Details, fill in PI Number, Date, and Amount under the **Proforma Invoice (PI)** card.
2. Upload the PI file (PDF/Image) and click **Upload & Attach PI** to update milestone to **`pi_attached`**.

### 2.3 International Letter of Credit (LC) & Expenses Breakdown
1. Complete **Register LC** on the PO details view.
2. Access **Procurement > LC Register** to track active LCs and 13 import duty elements (CD, RD, SD, VAT, AIT, AT, Freight, C&F fees). Record validity changes via **Record LC Amendment**.

---

## 🔹 Step 3: Shipment Logistics, Stock-in-Transit (SIT), GRN QC & Landed Cost Engine

### 3.1 Shipment Tracking & Stock-in-Transit (SIT)
1. Navigate to **Procurement > Shipments & SIT**, click **Register Shipment**, enter Vessel/Container/BL details, upload BL files, and update milestones from **`shipped`** ➔ **`in_transit`** ➔ **`arrived`** ➔ **`cleared`** (Customs Cleared).

### 3.2 Goods Received Note (GRN), Quality Control (QC) & Validation Guards
1. **Strict Clearance Guard:** Foreign import GRNs remain locked until shipment status reaches **Customs Cleared**.
2. **Over-Receipt Validation Guard:** Receiving quantities exceeding remaining balances (`accepted_qty > remaining_qty`) is blocked.
3. Go to **Procurement > Goods Receipts (GRN)**, click **Receive Goods (Create GRN)**, select Outlet, enter Accepted vs. Rejected quantities with Rejection Reasons, and submit.
4. Stock ledgers update automatically with Landed Unit Costs, and **Stream / Print Official GRN PDF** becomes available.

### 3.3 Weighted Average Landed Cost Allocation Matrix
1. Click **View Landed Cost Matrix** on any PO/GRN page to view the exact line-weight allocation of 13 import overheads into the final **True Landed Unit Cost** ($\text{Base Cost} + \text{Allocated Overhead} / \text{Accepted Qty}$).

### 3.4 Vendor Returns & Debit Notes
1. Click **Process Vendor Return (Debit Note)** on rejected GRN views.
2. The system calculates dynamic claim totals and generates Vendor Return `RET-XXXX` and Debit Note `DN-XXXX`. View all returns under **Procurement > Vendor Returns**.

---

## 🔹 Step 4: Procurement Financials, Vendor Bills, Payment Vouchers, Supplier Ledger & Reports

### 4.1 Vendor Bills / Invoices (3-Way Matching)
1. Go to **Procurement > Vendor Bills**, click **Create Vendor Bill**, select PO & GRN to populate line items with landed costs, and auto-deduct outstanding Debit Notes.

### 4.2 Multi-Currency Payment Vouchers (`PAY-XXXX`)
1. Go to **Procurement > Purchase Payments**, click **Record Payment Voucher**, select Vendor & Bill, enter payment method (Cash, Bank, Cheque, LC), convert foreign currencies, process payment, and print PDF vouchers.

### 4.3 Enterprise Debit Note Settlement Modes (3 Modes)
- **Mode A (Bill Deduction):** Auto-deducted during bill creation.
- **Mode B (Product Replacement Swap):** Open Debit Note details, click **Settle via Product Replacement**, and receive replacement stock into warehouse inventory.
- **Mode C (Direct Money Refund):** Click **Settle via Direct Money Refund**, enter bank refund details, and issue Refund Voucher `RCN-XXXX`.

### 4.4 Supplier Ledger, AP Aging Statement & Purchase Reports
1. Go to **Procurement > Supplier Ledger** for running balances, transaction history, AP Aging (0-30, 31-60, 61-90, 90+ days), and printable confirmation letters (**Export PDF Statement**).
2. Go to **Reports > Procurement Reports** to access 13 enterprise reports with server-side DataTables filtering, pagination, and export tools (Excel, CSV, PDF, Print).

---

*Document Version: 1.0 (Phase 2 Complete User Manual)*  
*Generated for B2B Viking ERP System Control*
