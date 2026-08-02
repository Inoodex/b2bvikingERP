# B2B Viking ERP - User Manual (Phase 2)
**Purchase, Import (LC) & Quotation Management**

---

## 1. Introduction (পরিচিতি)
B2B Viking ERP-এর Phase 2-এর ম্যানুয়ালে আপনাকে স্বাগতম! এই ফেজে প্রকিউরমেন্ট (Procurement) বা কেনাকাটার সম্পূর্ণ প্রসেসটি ৪টি ধাপে কভার করা হয়েছে। নিচে **Step 1 (RFQ & CS)** এবং **Step 2 (Purchase Order, PI & LC Tracking)**-এর কাজগুলো কীভাবে সিস্টেমে করতে হবে, তা ধাপে ধাপে বর্ণনা করা হলো।

---

## Step 1: RFQ, Quotations & Comparison Statement

### ১.১ Request for Quotation - RFQ (কোটেশনের জন্য রিকোয়েস্ট)
যখন আপনার কোনো প্রোডাক্ট কেনার প্রয়োজন হবে, তখন আপনি বিভিন্ন ভেন্ডরকে একটি RFQ (Request for Quotation) পাঠাবেন।

**কীভাবে নতুন RFQ তৈরি করবেন?**
1. বাম পাশের মেন্যু থেকে **Procurement / Purchases** > **RFQs**-এ ক্লিক করুন।
2. **Create New RFQ** বাটনে ক্লিক করুন।
3. আপনার প্রয়োজনীয় আইটেমগুলো (Products) এবং তার পরিমাণ (Quantity) ফর্মে অ্যাড করুন।
4. কোন কোন ভেন্ডর বা সাপ্লায়ারকে এই রিকোয়েস্টটি পাঠাতে চান, তাদের সিলেক্ট করুন।
5. **Save / Send** বাটনে ক্লিক করুন। 
*(সিস্টেম স্বয়ংক্রিয়ভাবে একটি সুন্দর PDF তৈরি করে ভেন্ডরদের ইমেইলে পাঠিয়ে দেবে)।*

---

### ১.২ Vendor Quotation (ভেন্ডর কোটেশন সাবমিট করা)
ভেন্ডররা যখন ইমেইল পাবে, তখন তারা ইমেইলেই তাদের দাম (Price) রিপ্লাই করে জানাবে এবং আপনাকে তা সিস্টেমে এন্ট্রি করতে হবে।

**কীভাবে ভেন্ডরের দাম সিস্টেমে জমা দেবেন?**
1. ভেন্ডর তার ইমেইলে RFQ-এর একটি **PDF** পাবে।
2. ভেন্ডর তাদের দাম (Price) এবং টার্মসগুলো ইমেইলের মাধ্যমে আপনাকে রিপ্লাই করে জানাবে।
3. আপনি ইমেইলটি পাওয়ার পর, অ্যাডমিন প্যানেলে **RFQs** পেজে গিয়ে ওই নির্দিষ্ট RFQ-টিতে ক্লিক করবেন।
4. সেখানে নির্দিষ্ট ভেন্ডরের নামের পাশে **Add Quotation**-এ ক্লিক করে, ভেন্ডরের পাঠানো দামগুলো সিস্টেমে এন্ট্রি করে সেভ করবেন।

---

### ১.৩ Comparison Statement - CS (কোটেশন তুলনা করা)
একাধিক ভেন্ডর যখন তাদের দাম জমা দেবে, তখন কার দাম সবচেয়ে কম, তা তুলনা করার জন্য CS বা Comparison Statement তৈরি করতে হয়। 

**কীভাবে CS তৈরি এবং অ্যাপ্রুভ করবেন?**
1. অ্যাডমিন প্যানেলে **RFQs** পেজে গিয়ে নির্দিষ্ট RFQ-টির ডিটেইলসে যান।
2. সেখানে **Generate CS** বা **View CS** বাটনে ক্লিক করুন।
3. আপনি একটি ম্যাট্রিক্স (টেবিল) দেখতে পাবেন, যেখানে সব ভেন্ডরের দেওয়া দাম পাশাপাশি সাজানো থাকবে।
4. সিস্টেম স্বয়ংক্রিয়ভাবে কারেন্সি কনভার্ট করে সর্বনিম্ন দাম (L1 Bidder) সবুজ রঙে হাইলাইট করবে!
5. **Submit CS for Approval** বাটনে ক্লিক করে অনুমোদনের জন্য পাঠান。

---

## Step 2: Purchase Orders (PO), Proforma Invoices (PI) & LC Register (Import Tracking)

### ২.১ স্বয়ংক্রিয় Purchase Order (PO) জেনারেশন
Comparison Statement (CS) অ্যাপ্রুভ হওয়ার পর বিজয়ী ভেন্ডরদের জন্য PO জেনারেট করতে হয়।

### ৩.১ Shipment Tracking & Stock-in-Transit (SIT)
শিপিং ভেসেল, কনটেইনার নম্বর, বিল অফ লেডিং (BL/AWB) ডকুমেন্ট আপলোড, ডিপার্চার (ETD), অ্যারাইভাল (ETA) ট্র্যাকিং এবং এন্টারপ্রাইজ লজিস্টিকস সিকিউরিটি লক।

**কীভাবে শিপমেন্ট রেজিস্টার, আপডেট ও ম্যানেজ করবেন?**
1. বামপাশের মেনু থেকে **Procurement > Shipments & SIT** অথবা যেকোনো অনুমোদিত PO পেজ থেকে **Register Shipment** বাটনে ক্লিক করুন।
2. **Vessel Name / Flight No**, **Container No**, **BL/AWB Number**, **Port of Loading**, **Port of Discharge**, **ETD**, এবং **ETA** ঘরগুলো পূরণ করুন।
3. অফিসিয়াল Bill of Lading (BL) অথবা Packing List ফাইল (PDF/JPG/PNG) আপলোড করুন।
4. **Register Shipment** বাটনে ক্লিক করুন। সিস্টেমের PO মাইলস্টোন স্বয়ংক্রিয়ভাবে **`shipped`** স্ট্যাটাসে আপডেট হবে।
5. **স্ট্যাটাস আপডেট**: শিপমেন্টের অগ্রগতির সাথে সাথে মাইলস্টোন স্ট্যাটাস **`in_transit`** ➔ **`arrived`** ➔ **`cleared`** (Customs Cleared) হিসেবে আপডেট করুন।
6. **লজিস্টিকস Edit ও Cancel লক (Enterprise Security Rules)**:
   - **Edit Logistics**: শিপমেন্ট লিস্ট ড্যাশবোর্ড অথবা Details পেজ থেকে নীল রঙের **Edit Logistics** বাটনে ক্লিক করে তথ্য সংশোধন করতে পারবেন।
   - **Customs / GRN Lock**: গুদামে মাল রিসিভ (GRN) সম্পন্ন হয়ে গেলে শিপমেন্টের স্ট্যাটাস পরিবর্তন এবং Edit বাটন স্বয়ংক্রিয়ভাবে লক হয়ে যাবে।
   - **Cancelled Status Permanent Lock**: শিপমেন্ট ভুলবশত তৈরি হলে **`Cancelled`** সিলেক্ট করা যাবে, তবে একবার ক্যানসেল করা হলে স্ট্যাটাস চিরতরে লক হয়ে যাবে এবং PO মাইলস্টোন আগের অবস্থায় রোলব্যাক হবে।

---

### ৩.২ Goods Received Note (GRN), Quality Control (QC) & Partial Receiving
ওয়্যারহাউসে মাল রিসিভ করা, পন্যের গুণগত মান (QC) যাচাই করা, গৃহীত (Accepted) ও প্রত্যাখ্যাত (Rejected) পরিমাণ রেকর্ড করা এবং স্বয়ংক্রিয়ভাবে ইনভেন্টরি স্টক আপডেট করা।

**কীভাবে GRN তৈরি করবেন এবং QC ইন্সপেকশন পরিচালনা করবেন?**
1. **এন্টারপ্রাইজ কাস্টমস ক্লিয়ারেন্স ফিল্টার (Strict Clearance Guard)**: 
   - **Foreign Purchase (আমদানি)**: শিপমেন্টের স্ট্যাটাস **`Customs Cleared`** না হওয়া পর্যন্ত সিস্টেমে **Receive Goods (GRN)** বাটন লক থাকবে এবং GRN ফর্মে ড্রপডাউনে আসবে না।
   - **Local Purchase (স্থানীয় কেনাকাটা)**: PO অনুমোদিত হলেই সরাসরি GRN তৈরি করা যাবে।
2. বামপাশের মেনু থেকে **Procurement > Goods Receipts (GRN)** পেজে গিয়ে **Receive Goods (Create GRN)** বাটনে ক্লিক করুন, অথবা কাস্টমস ক্লিয়ার হওয়া শিপমেন্ট/PO পেজ থেকে সরাসরি **Receive Goods (GRN)** ক্লিক করুন।
3. গন্তব্য **Outlet / Warehouse** নির্বাচন করুন এবং প্রয়োজনে রিসিভিং নোট (যেমন গেট পাস বা ট্রাক নম্বর) লিখুন।
4. সিস্টেম স্বয়ংক্রিয়ভাবে মালের তালিকা, **Ordered Qty** এবং **Remaining Qty** (পূর্বে কতটুকু মাল রিসিভ হয়েছে তার অবশিষ্ট) দেখাবে।
5. প্রতিটি আইটেমের বিপরীতে **Accepted Qty** (সবুজ হাইলাইট) এবং **Rejected Qty** (লাল হাইলাইট) বসান। মাল রিজেক্ট হলে উপযুক্ত **Rejection Reason** (যেমন "Damaged in Transit", "Defective Batch") লিখুন।
6. **Submit GRN & Update Inventory** বাটনে ক্লিক করুন (সিস্টেম Toastr নোটিফিকেশনের মাধ্যমে কনফার্ম করবে)।
7. **স্বয়ংক্রিয় স্টক আপডেট**: QC স্ট্যাটাস **Passed** বা **Partial** হলে, Accepted পরিমাণটি সাথে সাথে উক্ত আউটলেটের `inventory_stocks` এবং `stock_ledgers`-এ Landed Unit Cost সহ যুক্ত হয়ে যাবে।
8. **আংশিক মাল রিসিভ (Partial Receiving)**: কোনো PO-এর মালের কিছু অংশ আসলে (যেমন ১,০০০ পিসের জায়গায় ৬০০ পিস) PO স্ট্যাটাস **`goods_partial`** এ যাবে। পরবর্তীতে বাকি ৪০০ পিস রিসিভ হলে স্ট্যাটাস অটোমেটিক **`goods_received`** হয়ে সম্পূর্ণ হবে।
9. **GRN PDF Slip**: GRN details পেজ থেকে **Stream / Print Official GRN PDF** বাটনে ক্লিক করে অফিশিয়াল রিসিভিং ডকুমেন্ট প্রিন্ট বা ভিউ করতে পারবেন।

---

### ৩.৩ Weighted Average Landed Cost Allocation Matrix
১৩টি ইম্পোর্ট ডিউটি ও লজিস্টিকস খরচকে (Customs Duty, VAT, Freight, Insurance, C&F Agent Fees ইত্যাদি) মালের মূল্যের ওজনের ভিত্তিতে সমানুপাতিকভাবে বন্টন করে প্রতিটি পন্যের সঠিক **True Landed Unit Cost** হিসাব করা।

**কীভাবে Landed Cost Matrix দেখবেন?**
1. যেকোনো PO বা GRN Details পেজ থেকে **View Landed Cost Matrix** বাটনে ক্লিক করুন।
2. এই ম্যাট্রিক্সে প্রতিটি SKU-এর সম্পূর্ণ হিসাব পরিষ্কার সফট-কার্ডস ও প্রফেশনাল থিমে দেখতে পাবেন:
   - **PO Base Unit Cost**: মূল কেনা দাম (যেমন: `¥ 8.50 / unit`)।
   - **Allocated LC Overhead**: মালের অনুপাত অনুযায়ী প্রতি ইউনিটে যুক্ত হওয়া অতিরিক্ত ডিউটি ও ফ্রেইট খরচ (যেমন: `+ ¥ 5.65 / unit`) এবং মোট ওভারহেড।
   - **True Landed Unit Cost**: পন্যের চূড়ান্ত প্রকৃত কেনা দাম (যেমন: `¥ 14.15 / unit`) ($\text{Base Cost} + \text{Allocated Overhead} / \text{Accepted Qty}$)।
3. নতুন কোনো LC খরচ যুক্ত বা আপডেট হলে **Recalculate Matrix** বাটনে ক্লিক করে ইনভেন্টরি ভ্যালুয়েশন সিঙ্ক করে নিতে পারবেন।

**কীভাবে LC রেজিস্টার ও খরচ ইনপুট দেবেন?**
1. নির্দিষ্ট PO Details পেজের **Register LC** ফর্মে এলসি নম্বর, ইস্যুয়িং ব্যাংক, এলসি অ্যামাউন্ট, মার্জিন % এবং মেয়াদের তারিখ লিখুন।
2. **Register LC** বাটনে ক্লিক করার সাথে সাথে LC রেজিস্টার তৈরি হয়ে যাবে এবং স্ট্যাটাস **`lc_opened`** হবে।
3. **Procurement > LC Register** মেন্যুতে ক্লিক করে সকল এলসির তালিকা, মার্জিন % এবং **১৩টি আমদানিকৃত খরচ (CD, RD, SD, VAT, AIT, AT, Margin, Insurance, Freight, C&F Agent Cost)** দেখতে পারবেন।
4. এলসির মেয়াদ বাড়লে **Record LC Amendment** বাটনে ক্লিক করে অ্যামেন্ডমেন্ট হিস্ট্রি সংরক্ষণ করতে পারবেন।

---

## Step 3: International Shipment Logistics, Stock-in-Transit (SIT), GRN QC & Landed Cost Engine

### ৩.১ Shipment Tracking & Stock-in-Transit (SIT)
শিপিং ভেসেল, কনটেইনার নম্বর, বিল অফ লেডিং (BL/AWB) ডকুমেন্ট আপলোড, ডিপার্চার (ETD) এবং অ্যারাইভাল (ETA) ট্র্যাকিং।

**কীভাবে শিপমেন্ট রেজিস্টার ও আপডেট করবেন?**
1. বামপাশের মেনু থেকে **Procurement > Shipments & SIT** অথবা যেকোনো অনুমোদিত PO পেজ থেকে **Register Shipment** বাটনে ক্লিক করুন।
2. **Vessel Name / Flight No**, **Container No**, **BL/AWB Number**, **Port of Loading**, **Port of Discharge**, **ETD**, এবং **ETA** ঘরগুলো পূরণ করুন।
3. অফিসিয়াল Bill of Lading (BL) অথবা Packing List ফাইল (PDF/JPG/PNG) আপলোড করুন।
4. **Register Shipment** বাটনে ক্লিক করুন। সিস্টেমের PO মাইলস্টোন স্বয়ংক্রিয়ভাবে **`shipped`** স্ট্যাটাসে আপডেট হবে।
5. শিপমেন্টের অগ্রগতির সাথে সাথে মাইলস্টোন স্ট্যাটাস **`in_transit`** ➔ **`arrived`** ➔ **`cleared`** (Customs Cleared) হিসেবে আপডেট করুন।

---

### ৩.২ Goods Received Note (GRN), Quality Control (QC) & Partial Receiving
ওয়্যারহাউসে মাল রিসিভ করা, পন্যের গুণগত মান (QC) যাচাই করা, গৃহীত (Accepted) ও প্রত্যাখ্যাত (Rejected) পরিমাণ রেকর্ড করা এবং স্বয়ংক্রিয়ভাবে ইনভেন্টরি স্টক আপডেট করা।

**কীভাবে GRN তৈরি করবেন এবং QC ইন্সপেকশন পরিচালনা করবেন?**
1. **এন্টারপ্রাইজ কাস্টমস ক্লিয়ারেন্স ফিল্টার (Strict Clearance Guard)**: 
   - **Foreign Purchase (আমদানি)**: শিপমেন্টের স্ট্যাটাস **Customs Cleared** না হওয়া পর্যন্ত সিস্টেমে **Receive Goods (GRN)** বাটন লক থাকবে এবং GRN ফর্মে ড্রপডাউনে আসবে না।
   - **Local Purchase (স্থানীয় কেনাকাটা)**: PO অনুমোদিত হলেই সরাসরি GRN তৈরি করা যাবে।
2. বামপাশের মেনু থেকে **Procurement > Goods Receipts (GRN)** পেজে গিয়ে **Receive Goods (Create GRN)** বাটনে ক্লিক করুন, অথবা কাস্টমস ক্লিয়ার হওয়া শিপমেন্ট/PO পেজ থেকে সরাসরি **Receive Goods (GRN)** ক্লিক করুন।
2. গন্তব্য **Outlet / Warehouse** নির্বাচন করুন এবং প্রয়োজনে রিসিভিং নোট (যেমন গেট পাস বা ট্রাক নম্বর) লিখুন।
3. সিস্টেম স্বয়ংক্রিয়ভাবে মালের তালিকা, **Ordered Qty** এবং **Remaining Qty** (পূর্বে কতটুকু মাল রিসিভ হয়েছে তার অবশিষ্ট) দেখাবে।
4. প্রতিটি আইটেমের বিপরীতে **Accepted Qty** এবং **Rejected Qty** বসান। মাল রিজেক্ট হলে উপযুক্ত **Rejection Reason** (যেমন "Damaged in Transit", "Defective Batch") লিখুন।
5. **Submit GRN & Update Inventory** বাটনে ক্লিক করুন।
6. **স্বয়ংক্রিয় স্টক আপডেট**: QC স্ট্যাটাস **Passed** বা **Partial** হলে, Accepted পরিমাণটি সাথে সাথে উক্ত আউটলেটের `inventory_stocks` এবং `stock_ledgers`-এ Landed Unit Cost সহ যুক্ত হয়ে যাবে।
7. **আংশিক মাল রিসিভ (Partial Receiving) ও ভেন্ডর রিপ্লেসমেন্ট (Replacement Delivery)**:
   - কোনো PO-এর মালের কিছু অংশ আসলে (যেমন ১,০০০ পিসের জায়গায় ৬০০ পিস) PO স্ট্যাটাস **goods_partial** এ থাকবে। পরবর্তীতে বাকি ৪০০ পিস রিসিভ হলে স্ট্যাটাস অটোমেটিক **goods_received** হয়ে সম্পূর্ণ হবে।
   - **ভেন্ডর মাল রিপ্লেস করলে (Vendor Replacement Flow)**: QC-তে কোনো মাল রিজেক্ট হলে সিস্টেমে Remaining Qty অপশন থাকবে। ভেন্ডর পরবর্তীতে নতুন ভালো মাল পাঠালে আপনি একই PO-এর আন্ডারে ২য় আরেকটি GRN বানিয়ে নতুন মাল স্টকে তুলতে পারবেন।
8. **GRN PDF Slip**: GRN details পেজ থেকে **Stream / Print Official GRN PDF** বাটনে ক্লিক করে অফিশিয়াল রিসিভিং ডকুমেন্ট প্রিন্ট বা ভিউ করতে পারবেন।

---

### ৩.৩ Weighted Average Landed Cost Allocation Matrix
১৩টি ইম্পোর্ট ডিউটি ও লজিস্টিকস খরচকে (Customs Duty, VAT, Freight, Insurance, C&F Agent Fees ইত্যাদি) মালের মূল্যের ওজনের ভিত্তিতে সমানুপাতিকভাবে বন্টন করে প্রতিটি পন্যের সঠিক **True Landed Unit Cost** হিসাব করা।

**কীভাবে Landed Cost Matrix দেখবেন?**
1. যেকোনো PO বা GRN Details পেজ থেকে **View Landed Cost Matrix** বাটনে ক্লিক করুন।
2. এই ম্যাট্রিক্সে প্রতিটি SKU-এর সম্পূর্ণ হিসাব দেখতে পাবেন:
   - **PO Base Unit Cost**: মূল কেনা দাম।
   - **Allocated LC Overhead**: মালের অনুপাত অনুযায়ী প্রতি ইউনিটে যুক্ত হওয়া অতিরিক্ত ডিউটি ও ফ্রেইট খরচ।
   - **True Landed Unit Cost**: পন্যের চূড়ান্ত প্রকৃত কেনা দাম ($\text{Base Cost} + \text{Allocated Overhead} / \text{Accepted Qty}$)।
3. নতুন কোনো LC খরচ যুক্ত বা আপডেট হলে **Recalculate Matrix** বাটনে ক্লিক করে ইনভентরি ভ্যালুয়েশন সিঙ্ক করে নিতে পারবেন।

---

### ৩.৪ Vendor Returns & Debit Notes
QC-তে প্রত্যাখ্যাত (Rejected) মালামাল সাপ্লায়ারের কাছে ফেরত পাঠানো এবং সাপ্লায়ার অ্যাকাউন্টের বিরুদ্ধে অফিশিয়াল ডেবিট নোট (Debit Note) ইস্যু করা।

**কীভাবে Vendor Return ও Debit Note তৈরি করবেন?**
1. QC স্ট্যাটাস **Partial** বা **Failed** হওয়া যেকোনো GRN Details পেজ ওপেন করুন।
2. **Process Vendor Return (Debit Note)** বাটনে ক্লিক করুন।
3. সিস্টেম রিজেক্ট হওয়া মালের পরিমাণ এবং PO দর স্বয়ংক্রিয়ভাবে ফর্মে বসিয়ে দেবে।
4. ফেরত পাঠানোর সাধারণ কারণ লিখে **Issue Debit Note & Submit Return** বাটনে ক্লিক করুন।
5. সিস্টেম সাথে সাথে একটি অফিশিয়াল **Vendor Return Record (`RET-YYYYMMDD-XXXX`)** এবং **Debit Note (`DN-YYYYMMDD-XXXX`)** তৈরি করবে।
6. **Procurement > Vendor Returns** মেনুতে গিয়ে সকল রিটার্ন ও ডেবিট নোটের তালিকা এবং ব্যালেন্স দেখতে পাবেন।

---

# English Version

## 1. Introduction
Welcome to the Phase 2 User Manual of B2B Viking ERP! This phase covers the complete Procurement process in 4 distinct Steps. Below, you will find the step-by-step guide for **Step 1 (RFQ & CS)**, **Step 2 (Purchase Orders, PI & LC Tracking)**, and **Step 3 (Shipments, SIT, GRN QC & Landed Cost Engine)**.

---

## Step 1: RFQ, Quotations & Comparison Statement

### 1.1 Request for Quotation (RFQ)
When you need to purchase products, you will send an RFQ to various vendors.

**How to Create a New RFQ?**
1. Click on **Procurement / Purchases** > **RFQs** from the left menu.
2. Click the **Create New RFQ** button.
3. Add the required items (Products) and their quantities to the form.
4. Select the vendors/suppliers you want to send this request to.
5. Click **Save / Send**. 
*(The system will automatically generate a PDF and email it to the selected vendors).*

### 1.2 Vendor Quotation
When vendors receive the RFQ email, they will reply with their prices, and you will enter them into the system.

**How Do Vendors Submit Prices?**
1. The vendor receives a **PDF** of the RFQ via email.
2. The vendor will reply to your email with their offered prices and terms.
3. Once you receive the email, go to the **RFQs** page in the admin panel and open that specific RFQ.
4. Click on **Add Quotation** next to the specific vendor's name, manually enter the prices they provided, and save it.

### 1.3 Comparison Statement (CS)
When multiple vendors submit their prices, a Comparison Statement (CS) is generated to compare and identify the lowest bidder.

**How to Generate and Approve a CS?**
1. Go to the **RFQs** page in the admin panel and open the specific RFQ details.
2. Click the **Generate CS** or **View CS** button.
3. You will see a matrix (table) displaying all submitted vendor prices side-by-side.
4. The system automatically converts currencies into the base currency and highlights the lowest price (L1 Bidder)!
5. Click **Submit CS for Approval**.

---

## Step 2: Purchase Orders (PO), Proforma Invoices (PI) & LC Register (Import Tracking)

### 2.1 Automatic Purchase Order (PO) Generation
Once a Comparison Statement (CS) is approved, Purchase Orders can be generated for the winning vendors.

**How to Generate & Manage POs?**
1. Open the approved RFQ or CS details page and click **+ Generate PO**.
2. If multiple vendors win different items, the system automatically creates separate **Split POs (`PO-00001`, `PO-00002`...)**.
3. Navigate to **Procurement > Purchase Orders (PO)** from the left sidebar to view all POs and milestone trackers.
4. From the PO table or details page, click **Preview PDF** (to view inline in a new tab) or **Live Preview** and **Download PDF**. You can also click **Send PO Email to Supplier** to email the PO document directly to the vendor.

### 2.2 Proforma Invoice (PI) Document Attachment
After receiving the PO, the vendor will issue an official Proforma Invoice (PI), which must be uploaded to the system.

**How to Attach a PI File?**
1. Open the specific PO Details page.
2. Under the **Proforma Invoice (PI)** card, fill in the PI Number, Issue Date, and Total Amount.
3. Upload the PI file (PDF/Image) provided by the supplier and click **Upload & Attach PI**.
4. The PO milestone status will update to **`pi_attached`**.

### 2.3 International Letter of Credit (LC) & Expenses Breakdown
Register Letters of Credit (LC) for foreign imports and track 13 itemized import duty costs.

**How to Register LC & Enter Expenses?**
1. On the PO Details page, fill in the **Register LC** form with LC Number, Issuing Bank, Amount, Margin %, and Expiry Date.
2. Click **Register LC** to transition the PO milestone to **`lc_opened`**.
3. Click **Procurement > LC Register** from the left sidebar to access active LCs, margin utilization, and **13 itemized import expense breakdowns (CD, RD, SD, VAT, AIT, AT, Margin, Insurance, Freight, C&F Agent Fees)**.
4. Record LC validity extensions or amount changes via the **Record LC Amendment** modal to maintain complete audit logs.

---



### 3.1 Shipment Tracking & Stock-in-Transit (SIT)
Track shipping vessels, container numbers, Bill of Lading (BL/AWB) documents, departure (ETD), and arrival (ETA) milestones.

**How to Register & Update a Shipment?**
1. Go to **Procurement > Shipments & SIT** from the left sidebar or click **Register Shipment** from an approved PO.
2. Fill in the **Vessel Name / Flight No**, **Container No**, **BL/AWB Number**, **Port of Loading**, **Port of Discharge**, **ETD**, and **ETA**.
3. Upload the official Bill of Lading (BL) or Packing List document file (PDF/JPG/PNG).
4. Click **Register Shipment**. The PO milestone will automatically update to **`shipped`**.
5. As the shipment progresses, update its status milestone from **`in_transit`** ➔ **`arrived`** ➔ **`cleared`** (Customs Cleared).

---

### 3.2 Goods Received Note (GRN), Quality Control (QC) & Partial Receiving
Receive physical goods at the warehouse, inspect item quality, record accepted vs. rejected quantities, and automatically update inventory stock ledgers.

**How to Create a GRN & Conduct QC Inspection?**
1. **Enterprise Customs Clearance Filter (Strict Clearance Guard)**: 
   - **Foreign Purchase**: Goods receiving is locked until the shipment status reaches **Customs Cleared**.
   - **Local Purchase**: GRN can be initiated directly upon PO approval.
2. Go to **Procurement > Goods Receipts (GRN)** and click **Receive Goods (Create GRN)**, or click **Receive Goods (GRN)** directly from a Customs Cleared Shipment/PO.
2. Select the **Destination Outlet / Warehouse** and enter optional receiving notes (e.g. Gate Pass / Truck No).
3. The system automatically displays line items with **Ordered Qty** and **Remaining Qty** (ordered quantity minus items received in previous shipments).
4. Enter the **Accepted Qty** and **Rejected Qty** for each line item. If items are rejected, enter a clear **Rejection Reason** (e.g. "Damaged in Transit", "Defective Batch").
5. Click **Submit GRN & Update Inventory**.
6. **Automatic Stock Update**: If QC status is **Passed** or **Partial**, accepted quantities are immediately added to the selected outlet's `inventory_stocks` and recorded in `stock_ledgers` with the calculated Landed Unit Cost.
7. **Partial Receiving & Vendor Replacement Delivery**:
   - If only a portion of the order arrives (e.g., 600 out of 1,000 pcs), the PO milestone updates to **goods_partial**. When the remaining 400 pcs arrive in a subsequent GRN, the status updates to **goods_received**.
   - **Vendor Replacement Goods**: If QC items are rejected and the vendor later sends fresh replacement goods, the system automatically calculates Remaining Qty to allow receiving replacement items in a 2nd GRN under the same PO.
8. **GRN PDF Slip**: Click **Stream / Print Official GRN PDF** from the GRN details view to view or print the official receiving document rendered via background caching.

---

### 3.3 Weighted Average Landed Cost Allocation Matrix
Automatically allocate total import overheads (13 duty elements such as Customs Duty, VAT, Freight, Insurance, C&F fees) across purchased items based on their line value weight, calculating exact **True Landed Unit Cost** per SKU.

**How to View the Landed Cost Allocation Matrix?**
1. From any PO or GRN details page, click **View Landed Cost Matrix** or navigate to **`admin/landed-cost/{purchase_id}`**.
2. The matrix displays a complete breakdown:
   - **PO Base Unit Cost**: Original purchase price per item.
   - **Allocated LC Overhead**: Exact proportional share of duty & freight per unit based on line value weight ratio.
   - **True Landed Unit Cost**: Final unit cost ($\text{Base Cost} + \text{Allocated Duty Overhead} / \text{Accepted Qty}$).
3. Click **Recalculate Matrix** anytime new LC expenses are added or updated to synchronize inventory stock valuation.

---

### 3.4 Vendor Returns & Debit Notes
Process rejected QC items, return defective goods to the supplier, and automatically issue formal Debit Notes against Accounts Payable.

**How to Create a Vendor Return & Debit Note?**
1. Open any GRN details view that has a QC status of **Partial** or **Failed**.
2. Click **Process Vendor Return (Debit Note)**.
3. The system pre-fills rejected item quantities and PO unit prices.
4. Enter a general return reason and click **Issue Debit Note & Submit Return**.
5. The system generates a formal **Vendor Return Record (`RET-YYYYMMDD-XXXX`)** and an official **Debit Note (`DN-YYYYMMDD-XXXX`)**.
6. Go to **Procurement > Vendor Returns** to view all active returns, debit note amounts, and status trackers.

---
*Document Version: 3.0 (Phase 2, Step 1, Step 2 & Step 3 Complete)*  
*Generated for B2B Viking ERP Client*
