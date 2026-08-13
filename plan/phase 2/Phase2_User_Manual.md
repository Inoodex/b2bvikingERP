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

## Step 4: Procurement Financials, Vendor Bills, Payment Vouchers, Supplier Ledger & Reports

### ৪.১ Vendor Bills / Invoices (3-Way Matching & Debit Note Auto-Settlement)
ওয়্যারহাউজে রিসিভ হওয়া GRN ডাটাকে অফিশিয়াল **Vendor Bill / Invoice (`BILL-YYYYMMDD-XXXX`)**-এ রূপান্তর করা এবং পূর্বে ইস্যু করা Debit Note অটো-কাটাকাটি করা।

**কীভাবে Vendor Bill তৈরি করবেন?**
1. বাম পাশের মেনু থেকে **Procurement > Vendor Bills**-এ গিয়ে **Create Vendor Bill** সিলেক্ট করুন, অথবা যেকোনো রিসিভ হওয়া GRN পেজ থেকে **Issue Vendor Bill** বাটনে ক্লিক করুন।
2. **Purchase Order** এবং **Goods Receipt (GRN)** নির্বাচন করুন।
3. সিস্টেম স্বয়ংক্রিয়ভাবে গুদামে গ্রহণ করা মালের পরিমাণ (Accepted Qty) এবং ল্যান্ডেড কস্ট গুণ করে ইনভয়েসের মোট টাকা হিসাব করবে।
4. **ডেবিট নোট অটো-কাটাকাটি**: উক্ত ভেন্ডরের পূর্বে কোনো অনুমোদিত Debit Note থাকলে তা ফর্মে দেখতে পাবেন এবং `Auto-apply Debit Notes as Credit Adjustment` চেকবক্সে টিক দেওয়া থাকবে। সাবমিট দিলে মোট বিল থেকে ডেবিট নোটের টাকা স্বয়ংক্রিয়ভাবে মাইনাস হয়ে নেট বাকি টাকা হিসাব হবে।
5. **Save & Issue Vendor Bill** বাটনে ক্লিক করলে সিলিকন সিল সহ ইউনিক ইনভয়েস জেনারেট হবে।

---

### ৪.২ Multi-Currency Payment Vouchers (`PAY-YYYYMMDD-XXXX`)
সাপ্লায়ারকে আংশিক (Partial), পূর্ণাঙ্গ (Full) বা অগ্রিম (Advance) টাকা ক্যাশ, ব্যাংক ট্রান্সফার, চেক বা এলসি মার্জিন মারফতে পরিশোধ করা এবং অফিশিয়াল ভাউচার স্লিপ প্রিন্ট করা।

**কীভাবে Payment Voucher এন্ট্রি দিবেন এবং PDF প্রিন্ট করবেন?**
1. **Procurement > Purchase Payments** পেজে গিয়ে **Record Payment Voucher** বাটনে ক্লিক করুন।
2. **Vendor Name** এবং পরিশোধ করার উপযোগী **Vendor Bill** (অথবা অগ্রিম পেমেন্টের জন্য PO) নির্বাচন করুন।
3. **Payment Date**, **Amount** এবং **Payment Method** (Bank Transfer, Cash, Cheque, LC Settlement) বেছে নিন।
4. বৈদেশিক মুদ্রায় পেমেন্ট দিলে (যেমন USD বা RMB) **Currency** এবং **Exchange Rate** দিয়ে দিন, সিস্টেম অটোমেটিক ERP Base Currency (`kr.`) তে কনভার্ট করে নিবে।
5. ব্যাংকের জমা স্লিপ বা রসিদের ফাইল আপলোড করতে পারেন।
6. **Process Payment** এ ক্লিক করলে ইউনিক ভাউচার নম্বর `PAY-YYYYMMDD-XXXX` তৈরি হবে এবং বিলের বাকি বকেয়া অটোমেটিক কমে যাবে।
7. **Print PDF Voucher** বাটনে ক্লিক করে অফিশিয়াল পেমেন্ট ভাউচার পিডিএফ প্রিন্ট করুন।

---

### ৪.৩ Enterprise Debit Note Settlements (3 Modes)
QC রিজেকশনের ফলে সৃষ্ট Debit Note (`DN-YYYYMMDD-XXXX`) ৩টি ফ্লেক্সিবল উপায়ে সেটেল বা নিস্পত্তি করার পদ্ধতি:
- **Mode A: Bill Credit Deduction** (নতুন ইনভয়েস/বিলের টাকা থেকে কেটে রাখা - অটোমেটিক সচল)।
- **Mode B: Product Replacement Swap** (ভেন্ডর রিজেক্ট মালের বদলে ভালো মাল পাঠালে গুদামে রিসিভ করা)।
- **Mode C: Direct Money Refund Voucher** (ভেন্ডর সরাসরি ব্যাংকে/ক্যাশে টাকা ফেরত দিলে রিফান্ড ভাউচার তৈরি করা)।

**কীভাবে Product Swap বা Money Refund সেটেল করবেন?**
1. **Procurement > Vendor Returns** পেজে গিয়ে কাঙ্ক্ষিত Debit Note ডিটেইলস পেজটি ওপেন করুন।
2. **Product Replacement (মাল বদলে ভালো মাল নেওয়া)**:
   - **Settle via Product Replacement (Swap)** বাটনে ক্লিক করুন।
   - ভেন্ডরের পাঠানো একই প্রোডাক্ট অথবা বিকল্প অন্য কোনো ভালো প্রোডাক্ট (Substitute Item) সিলেক্ট করুন।
   - মালের পরিমাণ ও রিসিভ ডেট দিয়ে **Receive Stock & Settle Debit Note** সাবমিট দিলে গুদামের ইনভেন্টরি স্টক স্বয়ংক্রিয়ভাবে বৃদ্ধি পাবে এবং ডেবিট নোট নিস্পত্তি হবে।
3. **Direct Money Refund (ব্যাংকে সরাসরি টাকা ফেরত নেওয়া)**:
   - **Settle via Direct Money Refund** বাটনে ক্লিক করুন।
   - ভেন্ডরের ফেরত দেওয়া টাকার পরিমাণ, তারিখ, পেমেন্ট মেথড ও ব্যাংকের রেফারেন্স নম্বর দিন।
   - **Record Deposit & Settle Debit Note** সাবমিট দিলে রিফান্ড ভাউচার `RCN-YYYYMMDD-XXXX` তৈরি হবে এবং ক্যাশ/ব্যাংক ব্যালেন্স বৃদ্ধি পাবে।

---

### ৪.৪ Supplier Ledger, AP Aging Statement & Confirmation Letter
সাপ্লায়ারের রানিং লেজার স্টেটমেন্ট, Accounts Payable (AP) Aging বিশ্লেষণ (0-30, 31-60, 61-90, 90+ দিন) এবং অফিশিয়াল বকেয়া স্বীকৃতি লেটার প্রিন্ট করা।

**কীভাবে Supplier Ledger ও Aging Report দেখবেন?**
1. **Procurement > Supplier Ledger** (অথবা **Accounts > Vendor Ledger**) পেজে প্রবেশ করুন।
2. ড্যাশবোর্ডে মোট দেনা (Total Bills), মোট পরিশোধ (Total Payments), ডেবিট নোট এডজাস্টমেন্ট এবং নেট বাকি বকেয়া (Running Balance) দেখতে পাবেন।
3. যেকোনো সাপ্লায়ারের **View Statement** এ ক্লিক করে নির্দিষ্ট তারিখের মধ্যকার লেনদেনের সময়ক্রম দেখুন।
4. **AP Aging Analysis** ট্যাবে ক্লিক করে 0-30 দিন, 31-60 দিন, 61-90 দিন এবং 90+ দিনের পুরোনো ওভারডিউ বকেয়া বাকির বিশ্লেষণ দেখুন।
5. **Export PDF Statement** বাটনে ক্লিক করে সাপ্লায়ারের বকেয়া স্বীকৃতির অফিশিয়াল কনফার্মেশন লেটার ডাউনলোড বা প্রিন্ট করুন।

---

### ৪.৫ Purchase Reporting Engine (13 Reports)
প্রকিউরমেন্টের ১৩টি ক্লায়েন্ট রিপোর্ট ফিল্টারিং, সার্চ ও প্যাজিনেশন সহ দেখতে পাবেন।

**কীভাবে পারচেজ রিপোর্ট দেখবেন?**
1. নেভিগেশন মেনু থেকে **Procurement > Purchase Reports** সেকশনে যান।
2. ১৩টি রিপোর্টের মধ্যে আপনার প্রয়োজনীয় রিপোর্ট নির্বাচন করুন:
   - **2.23 Supplier-wise Purchase Report**: সাপ্লায়ার ভিত্তিক মোট ক্রয়ের পরিমাণ।
   - **2.24 & 2.26 Item-wise Purchase Report**: প্রোডাক্ট ভিত্তিক ক্রয়ের বিবরণ ও মোট পরিমাণ।
   - **2.25 Total Purchase Value (Periodic)**: মাসভিত্তিক বা নির্দিষ্ট পিরিয়ডের ক্রয়ের খরচ।
   - **2.27 Purchase Value vs Last Year**: গত বছরের সাথে এ বছরের ক্রয়ের তুলনামূলক পারফরম্যান্স।
   - **2.28 - 2.30 PR Status Reports**: কতটি PR রিসিভ হয়েছে, কতটি পেন্ডিং এবং কতটি আইটেম পেন্ডিং।
   - **2.31 - 2.32 PO Status Reports**: কতটি PO ইস্যু হয়েছে এবং কতটি আইটেম কেনা সম্পন্ন হয়েছে।
   - **2.33 PDF Print Format Validation**: SR, PR, CS, PO ও ভাউচারের পিডিএফ ফরম্যাট যাচাই।
   - **2.35 LC Register Dashboard**: এলসি ব্যাংক মার্জিন, মেয়াদের তারিখ ও এলসি খরচের হিসাব।

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

---

### 1.2 Vendor Quotation
When vendors receive the RFQ email, they will reply with their prices, and you will enter them into the system.

**How Do Vendors Submit Prices?**
1. The vendor receives a **PDF** of the RFQ via email.
2. The vendor will reply to your email with their offered prices and terms.
3. Once you receive the email, go to the **RFQs** page in the admin panel and open that specific RFQ.
4. Click on **Add Quotation** next to the specific vendor's name, manually enter the prices they provided, and save it.

---

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

---

### 2.2 Proforma Invoice (PI) Document Attachment
After receiving the PO, the vendor will issue an official Proforma Invoice (PI), which must be uploaded to the system.

**How to Attach a PI File?**
1. Open the specific PO Details page.
2. Under the **Proforma Invoice (PI)** card, fill in the PI Number, Issue Date, and Total Amount.
3. Upload the PI file (PDF/Image) provided by the supplier and click **Upload & Attach PI**.
4. The PO milestone status will update to **`pi_attached`**.

---

### 2.3 International Letter of Credit (LC) & Expenses Breakdown
Register Letters of Credit (LC) for foreign imports and track 13 itemized import duty costs.

**How to Register LC & Enter Expenses?**
1. On the PO Details page, fill in the **Register LC** form with LC Number, Issuing Bank, Amount, Margin %, and Expiry Date.
2. Click **Register LC** to transition the PO milestone to **`lc_opened`**.
3. Click **Procurement > LC Register** from the left sidebar to access active LCs, margin utilization, and **13 itemized import expense breakdowns (CD, RD, SD, VAT, AIT, AT, Margin, Insurance, Freight, C&F Agent Fees)**.
4. Record LC validity extensions or amount changes via the **Record LC Amendment** modal to maintain complete audit logs.

---

## Step 3: International Shipment Logistics, Stock-in-Transit (SIT), GRN QC & Landed Cost Engine

### 3.1 Shipment Tracking & Stock-in-Transit (SIT)
Track shipping vessels, container numbers, Bill of Lading (BL/AWB) documents, departure (ETD), and arrival (ETA) milestones.

**How to Register & Update a Shipment?**
1. Go to **Procurement > Shipments & SIT** from the left sidebar or click **Register Shipment** from an approved PO.
2. Fill in the **Vessel Name / Flight No**, **Container No**, **BL/AWB Number**, **Port of Loading**, **Port of Discharge**, **ETD**, and **ETA**.
3. Upload the official Bill of Lading (BL) or Packing List document file (PDF/JPG/PNG).
4. Click **Register Shipment**. The PO milestone will automatically update to **`shipped`**.
5. As the shipment progresses, update its status milestone from **`in_transit`** ➔ **`arrived`** ➔ **`cleared`** (Customs Cleared).
6. **Logistics Lock Rules**: Editing is enabled via **Edit Logistics**, but locked once GRN is received.

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
## Step 4: Procurement Financials, Vendor Bills, Payment Vouchers, Supplier Ledger & Reports

### 4.1 Vendor Bills / Invoices (3-Way Matching & Debit Note Settlement)
Convert GRN data into formal **Vendor Bills / Invoices (`BILL-YYYYMMDD-XXXX`)** and auto-deduct outstanding Debit Notes.

**How to Create a Vendor Bill?**
1. Go to **Procurement > Vendor Bills** and click **Create Vendor Bill** (or click **Issue Vendor Bill** directly from GRN details).
2. Select Purchase Order & GRN. Line items auto-populate from accepted quantities × unit landed cost.
3. Outstanding Debit Notes are automatically deducted as credit adjustments from the bill total.
4. Click **Save & Issue Vendor Bill** to generate invoice `BILL-YYYYMMDD-XXXX`.

---

### 4.2 Multi-Currency Payment Vouchers (`PAY-YYYYMMDD-XXXX`)
Record partial, full, or advance supplier payments across cash, bank transfer, cheque, or LC settlements with real-time currency conversion.

**How to Record Payment Vouchers?**
1. Go to **Procurement > Purchase Payments** and click **Record Payment Voucher**.
2. Select Vendor & Bill, enter Payment Date, Amount, and Payment Method.
3. For foreign currency payments, enter Currency & Exchange Rate to convert to Base Currency (`kr.`).
4. Upload receipt file if available and click **Process Payment** to generate Voucher `PAY-YYYYMMDD-XXXX`.
5. Click **Print PDF Voucher** to stream official payment voucher slips.

---

### 4.3 Enterprise Debit Note Settlements (3 Modes)
Settle Debit Notes (`DN-YYYYMMDD-XXXX`) via 3 flexible enterprise paths:
- **Mode A: Bill Credit Deduction**: Auto-deducted from future Vendor Bills.
- **Mode B: Product Replacement Receive (Swap)**: Open Debit Note details, click **Settle via Product Replacement**, select replacement product (same SKU or substitute product), enter quantity & submit. Warehouse stock is updated automatically.
- **Mode C: Direct Money Refund Voucher**: Click **Settle via Direct Money Refund**, enter refund amount, date, bank reference & submit. Creates Refund Voucher `RCN-YYYYMMDD-XXXX` and credits bank account balance.

---

### 4.4 Supplier Ledger, AP Aging Statement & Confirmation Letter
Real-time supplier ledger, running statement of account, Accounts Payable (AP) aging analysis (0-30, 31-60, 61-90, 90+ days), and printable audit confirmation letters.

**How to View Supplier Ledger & AP Aging?**
1. Go to **Procurement > Supplier Ledger** to view total payables, total paid, and net running balance.
2. Click **View Statement** for any vendor to see chronological transactions.
3. Click **AP Aging Analysis** to inspect overdue payables in **0-30**, **31-60**, **61-90**, and **90+ days** buckets.
4. Click **Export PDF Statement** to download/print the official Supplier Outstanding Acknowledgement PDF letter.

---

### 4.5 Purchase Reporting Engine (13 Reports)
Access 13 enterprise purchase reports under **Procurement > Purchase Reports** (Supplier-wise, Item-wise, Periodic Total Value, Value vs Last Year, PR Status, PO Status, LC Register Dashboard, etc.) with date range filters, pagination, and Base Currency (`kr.`) formatting.

---


*Document Version: 4.0 (Phase 2 Complete — Step 1, Step 2, Step 3 & Step 4)*  
*Generated for B2B Viking ERP Client*
