# 📘 Phase 3 Enterprise Sales Module User & Manual Testing Guide

এই ডকুমেন্টটিতে **B2B Viking ERP**-এর Sales Module (Phase 3)-এর প্রতিটি ফিচারের **ব্যবসায়িক উদ্দেশ্য (Business Purpose)**, **কখন ও কেন ব্যবহার করবেন (Real-World Use Cases)** এবং **ধাপে ধাপে ম্যানুয়াল টেস্ট করার নিয়ম** সম্পূর্ণ বিস্তারিতভাবে একটি একক মাস্টার ফাইলে উপস্থাপন করা হলো।

---

## 📑 বিষয়সূচি (Table of Contents)
1. **Step 3.2 — অটো ডকুমেন্ট সিক্যুয়েন্স ডায়নামিক কনফিগারেশন**
2. **Step 3.3 — সেলস কোটেশন তৈরি, DomPDF ডাউনলোড, অডিট লক এবং ১-ক্লিক সেলস অর্ডারে রূপান্তর**
3. **Step 3.4 (Part A) — ইউজার ক্রেডিট লিমিট ও কাস্টমার সেগমেন্টেশন**
4. **Step 3.4 (Part B) — কাস্টমার ডায়নামিক প্রাইসলিস্ট ও লাইভ অটো-প্রাইসিং**
5. **Step 3.5 (Part A) — প্রোমোশনাল কুপন কোড জেনারেটর ও ভ্যালিডেশন**
6. **Step 3.5 (Part B) — গিফট কার্ড ইস্যু, লেজার ট্রানজেকশন ও ব্যালেন্স অ্যাডজাস্টমেন্ট**
7. **Step 3.6 — সেলস অর্ডার ম্যানেজমেন্ট, কাস্টমার ক্রেডিট লিমিট ভ্যালিডেশন ও ক্রেডিট হোল্ড রিলিজ**
8. **Step 3.7 — সেলস অর্ডার পলিমরফিক এপ্রুভাল ওয়ার্কফ্লো**
9. **Step 3.8 — ডেলিভারি অর্ডার (চালান), কমার্শিয়াল প্যাকিং স্লিপ ও পার্শিয়াল শিপমেন্ট ইঞ্জিন**
10. **Step 3.9 — কমার্শিয়াল B2B সেলস ইনভয়েসিং ও ফিনান্সিয়াল পোস্টিং ইঞ্জিন**
11. **Step 3.10 — কাস্টমার পেমেন্ট কালেকশন, পেড রিসিপ্ট ভাউচার ও ইনভয়েস অ্যাডজাস্টমেন্ট**
12. **Step 3.11 — কাস্টমার সেলস রিটার্ন (RMA), ইনভেন্টরি অটো-রিস্টক, ৩-মোড ক্রেডিট নোট ও ক্রেডিট লিমিট রিলিজ**

---

## 1️⃣ Step 3.2: অটো ডকুমেন্ট সিক্যুয়েন্স কনফিগারেশন

### 💡 কেন ও কখন ব্যবহার করবেন? (Business Purpose)
আন্তর্জাতিক ও ইউরোপীয় হিসাববিজ্ঞান আইন এবং অডিট স্ট্যান্ডার্ড অনুযায়ী, প্রতিটি ইনভয়েস, সেলস অর্ডার বা কোটেশনের একটি সুনির্দিষ্ট ও ধারাবাহিক সিরিয়াল নম্বর (যেমন: `SQ-202608-0001`) থাকা বাধ্যতামূলক।  
- **কখন লাগবে:** নতুন অর্থবছর বা কোম্পানির প্রিফিক্স আপডেট করার সময় অ্যাডমিন সহজেই কোটেশন (`SQ-`), সেলস অর্ডার (`SO-`), ইনভয়েস (`INV-`), ডেলিভারি অর্ডার (`DO-`) এবং ক্রেডিট নোটের (`CN-`) রানিং সিক্যুয়েন্স ডায়নামিকভাবে কাস্টমাইজ করতে পারবেন।

### 👣 টেস্ট করার ধাপসমূহ:
1. অ্যাডমিন প্যানেলে লগইন করে নেভিগেশন বার থেকে **Document Sequences** পেজে যান:  
   👉 `http://b2bvikingerp.test/admin/document-sequences`
2. **লিস্ট চেক করুন:** সিস্টেমে ৫টি প্রধান মডিউলের জন্য অ্যাক্টিভ সিক্যুয়েন্স লিস্ট দেখতে পাবেন:
   - `Sales Quotation` (ডিফল্ট প্রিফিক্স: `SQ-`)
   - `Sales Order` (ডিফল্ট প্রিফিক্স: `SO-`)
   - `Sales Invoice` (ডিফল্ট প্রিফিক্স: `INV-`)
   - `Delivery Order` (ডিফল্ট প্রিফিক্স: `DO-`)
   - `Credit Note` (ডিফল্ট প্রিফিক্স: `CN-`)
3. **এডিট পরীক্ষা:** যেকোনো সিক্যুয়েন্সের পাশে **Edit** বাটনে ক্লিক করে প্রিফিক্স বা প্যাডিং পরিবর্তন করে সেভ করুন।
4. **ফলাফল:** পরবর্তী যেকোনো নতুন কোটেশন তৈরি করলে আপনার সেট করা নতুন ফরম্যাটে নম্বর জেনারেট হবে!

---

## 2️⃣ Step 3.3: সেলস কোটেশন, PDF, অডিট লক এবং ১-ক্লিক SO কনভার্সন

### 💡 কেন ও কখন ব্যবহার করবেন? (Business Purpose)
B2B পাইকারি ব্যবসায় ক্লায়েন্টরা অর্ডার দেওয়ার আগে খরচের আনুমানিক হিসাব বা প্রাইজ কোটেশন দেখতে চান।  
- **সেলস কোটেশন:** সেলস এক্সিকিউটিভরা কাস্টমারের জন্য ট্যাক্স ও ডিসকাউন্টসহ একটি আনুমানিক প্রাইজ অফার (Quotation) তৈরি করে PDF আকারে পাঠায়।
- **১-ক্লিক সেলস অর্ডার কনভার্সন:** কাস্টমার কোটেশনটি গ্রহণ (Accept) করলে এক ক্লিকে সেটিকে অফিসিয়াল সেলস অর্ডারে (Sales Order) রূপান্তর করা হয়। এতে সময় বাঁচে এবং অডিট ট্রেইল বজায় থাকে।
- **অডিট লক:** একবার কনভার্ট হয়ে গেলে কোটেশনটি লক হয়ে যায়, যাতে পরে কেউ জালিয়াতি করে দাম পরিবর্তন করতে না পারে।

### 👣 টেস্ট করার ধাপসমূহ:

#### ক. সেলস কোটেশন তৈরি (Create Quotation):
1. নেভিগেশন বার থেকে **Orders ➔ Sales Quotations ➔ Create** পেজে যান:  
   👉 `http://b2bvikingerp.test/admin/sales-quotations/create`
2. **কাস্টমার সিলেক্ট করুন:** যেকোনো কাস্টমার চয়ন করুন।
3. **মেয়াদ দিন:** `Valid Until` তারিখ নির্বাচন করুন।
4. **প্রোডাক্ট যোগ করুন:** **+ Add Item** বাটনে ক্লিক করে একাধিক প্রোডাক্ট সিলেক্ট করুন, পরিমাণ (Quantity) ও একক মূল্য (Unit Price) বসান।
5. **ট্যাক্স ও ডিসকাউন্ট পরীক্ষা:** `Tax` ড্রপডাউন (যেমন: Moms 25%) এবং `Overall Discount` ফিল্ডে ভ্যালু দিন। সাবটোটাল, ট্যাক্স ও গ্র্যান্ড টোটাল লাইভ আপডেট হবে!
6. **Save Quotation** চাপুন। কোটেশনটি সফলভাবে `Draft` স্ট্যাটাসে তৈরি হবে।

#### খ. DomPDF ডাউনলোড পরীক্ষা (PDF Export):
1. কোটেশন ডিটেইলস পেজে থাকা **Download PDF** বাটনে ক্লিক করুন:  
   👉 `http://b2bvikingerp.test/admin/sales-quotations/{id}/pdf`
2. **ফলাফল:** ব্রাউজারে সুন্দর স্টাইলিশ PDF ফাইল স্ট্রিম হবে (কোনো মেমোরি বা 500 ক্র্যাশ ছাড়াই)।

#### গ. এন্টারপ্রাইজ অডিট ড্রাফট লক রুল টেস্ট (Strict Audit Rules):
1. কোটেশনটি `Draft` অবস্থায় থাকলে টেবিল ও ডিটেইলস পেজে **Edit Quote** বাটন দৃশ্যমান থাকবে।
2. কোটেশনটি একবার কনভার্ট হয়ে গেলে Edit বাটন **স্বয়ংক্রিয়ভাবে গায়েব** হয়ে যাবে।

#### ঘ. ১-ক্লিক সেলস অর্ডার কনভার্সন (SweetAlert Modal):
1. কোটেশন ডিটেইলস পেজে থাকা সবুজ **Convert to Sales Order (SO)** বাটনে ক্লিক করুন।
2. **SweetAlert মোডাল ভেসে উঠবে:**  
   - Title: `Convert to Sales Order?`
   - Prompt: `Are you sure you want to convert Quotation SQ-XXXXXX into an official Sales Order (SO)?`
3. **Yes, Convert to SO!** চাপুন। কোটেশনটি মুহূর্তের মধ্যে `SO-202608-XXXX` সেলস অর্ডারে রূপান্তর হয়ে যাবে!

---

## 3️⃣ Step 3.4 (Part A): ইউজার ক্রেডিট লিমিট ও কাস্টমার সেগমেন্টেশন

### 💡 কেন ও কখন ব্যবহার করবেন? (Business Purpose)
প্রতিটি কাস্টমার বা ডিস্ট্রিবিউটরের ব্যবসায়িক ক্যাটাগরি এবং ঝুঁকির মাত্রা (Financial Risk) ভিন্ন হয়।
- **Customer Segment (`Retail`, `Wholesale`, `B2B VIP`, `Distributor`):** কাস্টমারদের ক্যাটাগরি অনুযায়ী গ্রুপ করা হয়, যাতে প্রাইসলিস্ট ও ছাড়ের নিয়ম স্বয়ংক্রিয়ভাবে প্রযোজ্য হয়।
- **Credit Limit ($ / kr.):** কোনো B2B কাস্টমারকে সর্বোচ্চ কত টাকার বাকিতে পণ্য দেওয়া যাবে (যেমন: ৫০,০০০ টাকা) তার নিরাপত্তা সীমানা নির্ধারণ করা হয়। ফলে কাস্টমার লিমিট পার করে ঋণ নিতে পারে না।

### 👣 টেস্ট করার ধাপসমূহ:
1. **User Management ➔ Users** পেজে যান:  
   👉 `http://b2bvikingerp.test/admin/users`
2. **ডাটাটেবিল কলাম চেক করুন:** লিস্টে প্রতিটি ইউজারের পাশে **Segment** (রঙিন ব্যাজ) এবং **Credit Limit** (`kr. XX,XXX.XX`) দেখতে পাবেন।
3. **ইউজার এডিট/ক্রিয়েট:** যেকোনো ইউজার এডিট করে:
   - **Customer Segment / Tier:** সিলেক্ট করুন `Wholesale Customer`
   - **Credit Limit:** বসান `50000`
4. **Update User** চাপুন। ডাটাটেবিলে ইউজারের পাশে নীল রঙের `WHOLESALE` ব্যাজ এবং `kr. 50,000.00` ক্রেডিট লিমিট আপডেট হবে!

---

## 4️⃣ Step 3.4 (Part B): ডায়নামিক কাস্টমার প্রাইসলিস্ট ও লাইভ অটো-প্রাইসিং

### 💡 কেন ও কখন ব্যবহার করবেন? (Business Purpose)
পণ্যমূল্য বারবার পরিবর্তন না করে নির্দিষ্ট খদ্দেরদের জন্য চুক্তিভিত্তিক পাইকারি বা বিশেষ দাম অফার করা।
- **কেন লাগবে:** খুচরা ক্রেতা কিনবে ২৫০ টাকায়, পাইকারি ক্রেতা কিনবে ১৫০ টাকায়, আর চুক্তিভিত্তিক ভিআইপি ডিস্ট্রিবিউটর পাবে ১২০ টাকায়। 
- **সুবিধা:** অ্যাডমিন একটি নির্দিষ্ট মেয়াদের (যেমন: ৩ মাসের) জন্য পাইকারি প্রাইসলিস্ট বানিয়ে রাখলে, সেলস ম্যান কোটেশন তৈরি করার সময় কাস্টমার সিলেক্ট করলেই সিস্টেমে **স্বয়ংক্রিয়ভাবে পাইকারি বিশেষ দাম বসবে**! হাত দিয়ে বসানোর দরকার হবে না।

### 👣 টেস্ট করার ধাপসমূহ:

#### ক. পাইকারি (Wholesale) প্রাইসলিস্ট তৈরি:
1. **Orders ➔ Customer Pricelists** পেজে যান:  
   👉 `http://b2bvikingerp.test/admin/pricelists`
2. **Create Pricelist** বাটনে ক্লিক করুন:  
   👉 `http://b2bvikingerp.test/admin/pricelists/create`
3. **ফরম পূরণ করুন:**
   - `Pricelist Name`: `2026 Wholesale Tier Offer`
   - `Target Customer Segment`: `Wholesale Customer`
   - `Status`: `Active`
4. **প্রাইস গ্রিডে দাম বসান:**
   - ড্রপডাউন থেকে যেকোনো প্রোডাক্ট (যেমন: `Magnet-DS 1058`) সিলেক্ট করুন। প্রোডাক্টের সাধারণ MRP (যেমন: `35.00` kr.) ফিল্ডে চলে আসবে।
   - **Tier Special Price** ঘরে পাইকারি দামটি বসান (যেমন: `25.00` kr.)।
5. **Save Pricelist** চাপুন।

#### খ. সেলস কোটেশনে লাইভ অটো-প্রাইসিং টেস্ট (Live Magic Test ✨):
1. **Sales Quotations ➔ Create** পেজে যান:  
   👉 `http://b2bvikingerp.test/admin/sales-quotations/create`
2. **Customer** ড্রপডাউন থেকে পূর্বে প্রস্তুত করা **Wholesale Customer** ইউজারটি সিলেক্ট করুন।
3. প্রোডাক্ট ড্রপডাউন থেকে প্রাইসলিস্টের প্রোডাক্টটি (`Magnet-DS 1058`) সিলেক্ট করুন।
4. **ফলাফল:** সাধারণ দাম `35.00` kr.-এর বদলে সিস্টেমে স্বয়ংক্রিয়ভাবে আপনার সেট করা পাইকারি বিশেষ দাম **`25.00` kr. অটো-লোড হয়ে যাবে!**

---

## 5️⃣ Step 3.5 (Part A): প্রোমোশনাল কুপন কোড

### 💡 কেন ও কখন ব্যবহার করবেন? (Business Purpose)
মার্কেটিং ক্যাম্পেইন পরিচালনা ও বড় বিক্রয় উৎসব বা পার্টনারশিপ অফার চালানোর জন্য।
- **কেন লাগবে:** যেমন বৈশাখী অফার বা নতুন কাস্টমারদের জন্য `WELCOME2026` কুপন তৈরি করা।
- **নিয়ন্ত্রণ:** অ্যাডমিন নির্দিষ্ট কুপন কোড কতবার ব্যবহার করা যাবে (যেমন: প্রথম ১০০ জন) এবং কত তারিখ পর্যন্ত মেয়াদী থাকবে তা নিয়ন্ত্রণ করতে পারবেন।

### 👣 টেস্ট করার ধাপসমূহ:
1. নেভিগেশন বার থেকে **Orders ➔ Promo Coupons** পেজে যান:  
   👉 `http://b2bvikingerp.test/admin/coupons`
2. **Create Coupon Code** বাটনে ক্লিক করুন:  
   👉 `http://b2bvikingerp.test/admin/coupons/create`
3. **ফরম পূরণ করুন:**
   - **Coupon Code:** কাস্টম কোড লিখুন (যেমন: `WELCOME2026`) অথবা **Auto Generate** বাটনে চাপ দিন (যেমন: `VIKING-9X2K8L`)।
   - **Linked Discount Rule:** যেকোনো ডিসকাউন্ট রুল সিলেক্ট করুন (যেমন: `10% Campaign Discount`)।
   - **Usage Limit:** বসান `50` (অর্থাৎ ৫০ বারের বেশি ব্যবহার করা যাবে না)।
   - **Expiration Date:** ভবিষ্যতের যেকোনো তারিখ নির্বাচন করুন।
4. **Save Coupon Code** চাপুন। কুপনটি Yajra ডাটাটেবিলে রেডি হয়ে যাবে!

---

## 6️⃣ Step 3.5 (Part B): গিফট কার্ড ইস্যু ও ট্রানজেকশন লেজার

### 💡 কেন ও কখন ব্যবহার করবেন? (Business Purpose)
কাস্টমার লয়াল্টি, প্রি-পেইড ভাউচার অফার এবং কাস্টমার রিফান্ড সামলানোর জন্য।
- **কেন লাগবে:** কাস্টমারদের উপহার হিসেবে ১৬ ডিজিটের প্রি-পেইড কার্ড (যেমন: ১,০০০ টাকার Gift Card) ইস্যু করা।
- **ট্রানজেকশন অডিট লেজার:** কাস্টমার যখনই উপহারের টাকা ব্যবহার করে পণ্য কিনবে, সিস্টেম অটোমেটিক কার্ডের অবশিষ্টাংশ হিসাব রাখবে এবং কোন অর্ডারে কত টাকা কেনাকাটা করা হয়েছে তার সম্পূর্ণ স্বচ্ছ ট্রানজেকশন লেজার রেজিস্টার তৈরি করবে।

### 👣 টেস্ট করার ধাপসমূহ:

#### ক. গিফট কার্ড ইস্যু (Issue Card):
1. নেভিগেশন বার from **Orders ➔ Gift Cards Engine** পেজে যান:  
   👉 `http://b2bvikingerp.test/admin/gift-cards`
2. **Issue Gift Card** বাটনে ক্লিক করুন:  
   👉 `http://b2bvikingerp.test/admin/gift-cards/create`
3. **ফরম পূরণ করুন:**
   - **Gift Card Number:** অটোমেটিক ইউনিক ১৬ ডিজিটের কার্ড কোড (যেমন: `GC-5206-3923-8251`) চলে আসবে। প্রয়োজনে **Regenerate** বাটনে চাপুন।
   - **Initial Value:** বসান `1000` (১,০০০ kr.)।
4. **Issue Gift Card** চাপুন। 

#### খ. কার্ডের ট্রানজেকশন লেজার ও ব্যালেন্স অ্যাডজাস্টমেন্ট টেস্ট:
1. গিফট কার্ড লিস্টের পাশে নীল চোখের আইকন (**View Ledger**) বাটনে ক্লিক করুন:  
   👉 `http://b2bvikingerp.test/admin/gift-cards/{id}`
2. **কার্ড সামারি দেখুন:** কার্ডের প্রাথমিক ভ্যালু `kr. 1,000.00` এবং বর্তমান ব্যালেন্স `kr. 1,000.00` দেখতে পাবেন।
3. **ট্রানজেকশন টেবিলে দেখুন:** `ISSUED` টাইপে ১টি ট্রানজেকশন লগ তৈরি হয়েছে।
4. **ব্যালেন্স অ্যাডজাস্টমেন্ট পরীক্ষা:**  
   - **Adjust Balance** বক্সে `+200` বা `-150` লিখে রিলিজ/রেফারেন্স দিয়ে **Apply Adjustment** চাপুন।
   - **ফলাফল:** মুহূর্তের মধ্যে সমাপনী ব্যালেন্স আপডেট হবে এবং নতুন `ADJUSTED` ট্রানজেকশন লগ যুক্ত হবে!

---

## 7️⃣ Step 3.6: সেলস অর্ডার ম্যানেজমেন্ট, কাস্টমার ক্রেডিট লিমিট ভ্যালিডেশন ও ক্রেডিট হোল্ড রিলিজ

### 💡 কেন ও কখন ব্যবহার করবেন? (Business Purpose)
SAP S/4HANA & Odoo 17 এন্টারপ্রাইজ স্ট্যান্ডার্ডে বি২বি সেলস অর্ডারে গ্রাহকের ঋণের ঝুঁকি (Credit Risk Management) স্বয়ংক্রিয়ভাবে নিয়ন্ত্রণ করা।
- **কেন লাগবে:** কোনো খদ্দেরের ক্রেডিট লিমিট যদি ১০,০০০ টাকা হয় এবং তার পূর্বের বকেয়া ৭,০০০ টাকা থাকে, তবে সে নতুন ৫,০০০ টাকার অর্ডার দিলে মোট এক্সপোজার হবে ১২,০০০ টাকা (যা লিমিটের চেয়ে বেশি)।
- **স্বয়ংক্রিয় ক্রেডিট হোল্ড (Credit Hold):** সিস্টেম স্বয়ংক্রিয়ভাবে অর্ডারটিকে **`Credit Hold`** করে রিফাইনারি/শিপমেন্ট ব্লক করে দেবে।
- **অ্যাডমিন ওভাররাইড (Credit Hold Release):** অর্থ ব্যবস্থাপক বা অ্যাডমিন চাইলে নোট লিখে ১-ক্লিকে অননুমোদিত হোল্ড রিলিজ (Release Credit Hold) করে অর্ডারটি প্রসেস করতে পারবেন।

### 👣 টেস্ট করার ধাপসমূহ:

#### ক. লাইভ ক্রেডিট এক্সপোজার উইজেট পরীক্ষা (Sales Order Creation):
1. **Orders ➔ Sales Orders (SO) ➔ Create** পেজে যান:  
   👉 `http://b2bvikingerp.test/admin/sales-orders/create`
2. **Customer select করুন:** সিলেক্ট করুন আপনার প্রস্তুত করা **Wholesale Customer** (যার ক্রেডিট লিমিট দেওয়া আছে, যেমন: `10000` kr.)।
3. **ডানপাশের Credit Exposure Widget চেক করুন:**
   - কাস্টমারের approved limit `kr. 10,000.00` এবং বর্তমান বকেয়া ও অবশিষ্ট ক্রেডিট ডিসপ্লে হবে।
4. **অর্ডার প্রোডাক্ট যোগ করুন:** এমন পরিমাণ প্রোডাক্ট সিলেক্ট করুন যার মোট মূল্য কাস্টমারের অবশিষ্ট ক্রেডিট লিমিট পার করে ফেলে (যেমন: `kr. 12,000.00`)।
5. **Save & Process Order** চাপুন।

#### খ. ক্রেডিট হোল্ড স্ট্যাটাস ও রিলিজ টেস্ট (Credit Hold Release Workflow):
1. **ফলাফল:** অর্ডারটি সফলভাবে সংরক্ষিত হবে কিন্তু স্ট্যাটাস হবে লাল রঙের **`CREDIT HOLD`**!
2. **অর্ডার ডিটেইলস পেজে লাল সতর্কবার্তা ভাসবে:**  
   `Order Flagged Under Credit Hold: This order exceeds customer approved credit limit exposure.`
3. **Release Credit Hold বাটনে চাপ দিন:**  
   - মোডাল ভেসে উঠবে: **Authorize Credit Hold Release**।
   - নোট লিখুন: `Approved by Finance Manager on Verbal Guarantee`
   - **Authorize Release** বাটনে চাপুন।
4. **ফলাফল:** অর্ডারের স্ট্যাটাস মুহূর্তের মধ্যে সবুজ **`APPROVED`** স্ট্যাটাসে রূপান্তর হবে!

---




---

## 8️⃣ Step 3.7: সেলস অর্ডার পলিমরফিক এপ্রুভাল ওয়ার্কফ্লো

### 💡 কেন ও কখন ব্যবহার করবেন? (Business Purpose)
আন্তর্জাতিক এন্টারপ্রাইজ লেভেলে (SAP S/4HANA SD Order Approval / Odoo 17 Sales Approval) বড় অঙ্কের বা ক্রেডিট লিমিট অতিক্রম করা সেলস অর্ডার ম্যানেজারিয়াল লেভেল থেকে অনুমোদন (Approval) বা রিজেক্ট (Reject) করার জন্য পলিমরফিক এপ্রুভাল ইঞ্জিন ব্যবহার করা হয়।
- **পলিমরফিক এপ্রুভাল ইঞ্জিন (`App\Models\Approval`):** সেলস অর্ডার রিলেশনশিপে ডায়নামিক মাল্টি-লেভেল এপ্রুভাল স্টেপ ট্র্যাক করা।
- **রোল-বেসড সিকিউরিটি ও স্টেপর UI (`ApprovalService.php`):** সেলস অর্ডার ডিটেইলস পেজে এপ্রুভাল প্রোগ্রেস বার, অনুমোদনকারী রোলস এবং ১-ক্লিক **Approve Order** বা **Reject Order** বাটন পরিবেশন করা।
- **স্বয়ংক্রিয় স্ট্যাটাস পরিবর্তন:** সকল লেভেলের এপ্রুভাল সম্পন্ন হলে অর্ডারের স্ট্যাটাস `Pending Approval` থেকে স্বয়ংক্রিয়ভাবে `Approved` হয়ে যাওয়া।

### 👣 টেস্ট করার ধাপসমূহ:

#### ক. সেলস অর্ডার এপ্রুভালে সাবমিট করা (Submit Sales Order for Approval):
1. ড্রাফট সেলস অর্ডার পেজে (`http://b2bvikingerp.test/admin/orders/{id}`) যান।
2. হেডার কার্ডে থাকা **Submit for Approval** বাটনে ক্লিক করুন।
3. **ফলাফল:**  
   - অর্ডারের স্ট্যাটাস হলুদ **`PENDING APPROVAL`** হয়ে যাবে।
   - পেজে ডায়নামিক এপ্রুভাল স্টেপার (Approval Progress Stepper) দেখা যাবে।

#### খ. সেলস অর্ডার এপ্রুভ বা রিজেক্ট পরীক্ষা (Approve / Reject Order Step):
1. অনুমোদিত ম্যানেজার ইউজার হিসেবে লগইন করে সেলস অর্ডার পেজে (`http://b2bvikingerp.test/admin/orders/{id}`) যান।
2. স্টেপারের ডানপাশে থাকা **Approve Order** বাটনে চাপ দিন।
3. **ফলাফল:**  
   - স্ক্রিনের উপরে সবুজ **Toastr** পপ-আপ ভেসে উঠবে।
   - অর্ডারের স্ট্যাটাস সবুজ **`APPROVED`** হয়ে যাবে এবং পরবর্তী ডেলিভারি বা ইনভয়েসিং স্টেপ আনলক হবে!

---

## 9️⃣ Step 3.8: ডেলিভারি অর্ডার (চালান), কমার্শিয়াল প্যাকিং স্লিপ ও পার্শিয়াল শিপমেন্ট ইঞ্জিন

### 💡 কেন ও কখন ব্যবহার করবেন? (Business Purpose)
আন্তর্জাতিক এন্টারপ্রাইজ লেভেলে (SAP S/4HANA VL01N / Odoo 17 / Zoho Inventory) অনুমোদিত সেলস অর্ডারের বিপরীতে কমার্শিয়াল ডেলিভারি চালান (`DO-202608-XXXX`) তৈরি ও মালামাল শিপমেন্ট সম্পন্ন করা।
- **স্বয়ংক্রিয় চালান সিকোয়েন্স (`DO-YYYYMM-XXXX`):** `OrderNumberService`-এর মাধ্যমে অফিশিয়াল চালান নম্বর জেনারেট হওয়া।
- **পার্শিয়াল শিপমেন্ট ও ব্যাক-অর্ডার (Partial Dispatch):** ১০০টি অর্ডারের মধ্যে আজ ৫০টি মাল চালান দিলে সিস্টেম বাকি ৫০টি স্বয়ংক্রিয়ভাবে `Back-Order` হিসেবে ধরে রাখবে।
- **লজিস্টিকস ও কুরিয়ার AWB ট্র্যাকিং:** শিপিং ক্যারিয়ার (DHL, PostNord, DSV, FedEx, Local Truck) এবং AWB ট্র্যাকিং নম্বর সংরক্ষণ করা।
- **ওয়্যারহাউজ স্টক মাইনাস ও লেজার ট্র্যাকিং:** চালান ডিসপ্যাচ (`Dispatched`) হলে স্বয়ংক্রিয়ভাবে `InventoryStock` থেকে পণ্য বিয়োগ হওয়া এবং `StockLedger`-এ `OUT` এন্ট্রি পড়া।
- **DomPDF কমার্শিয়াল প্যাকিং স্লিপ PDF:** ১-ক্লিকে ব্রাউজারে প্রিন্টযোগ্য প্যাকিং স্লিপ PDF জেনারেট হওয়া।

### 👣 টেস্ট করার ধাপসমূহ:

#### ক. ১-ক্লিকে বা সরাসরি চালান তৈরি (Create Delivery Order):
1. **উপায় ১ (অর্ডার পেজ থেকে):**  
   যেকোনো অনুমোদিত অর্ডার পেজে (`http://b2bvikingerp.test/admin/orders/{id}`) যান এবং কার্ডের উপরে থাকা 🚚 **Create Delivery Order** বাটনে ক্লিক করুন।
2. **উপায় ২ (ডেলিভারি অর্ডার পেজ থেকে):**  
   👉 `http://b2bvikingerp.test/admin/delivery-orders/create` পেজে যান এবং সেন্টার্ড সার্চ বক্সে অর্ডার সিলেক্ট করুন।
3. **চালানের তথ্য প্রদান:**  
   - **Carrier:** DHL, PostNord, DSV বা Local Truck বেছে নিন।
   - **AWB Tracking:** কুরিয়ার বা গাড়ির ট্র্যাকিং নম্বর লিখুন (যেমন: `AWB-982341823`)।
   - **Dispatch Qty:** প্রতিটি আইটেমের জন্য আজ কত পিস চালান দিচ্ছেন তা বসান (যেমন: `50`)।
4. **Create Delivery Order (Challan)** চাপুন।

#### খ. চালান এপ্রুভাল ও স্টক মাইনাস পরীক্ষা (Dispatch & Ship Order):
1. তৈরি হওয়া চালানের ডিটেইলস পেজে (`http://b2bvikingerp.test/admin/delivery-orders/{id}`) যান।
2. **Dispatch & Ship Order** বাটনে ক্লিক করুন।
3. **SweetAlert পপ-আপ:** *"Dispatch & Ship Delivery Order #DO-XXXX?"* কনফার্ম করুন।
4. **ফলাফল:**  
   - স্ক্রিনের উপরে সবুজ **Toastr** পপ-আপ ভেসে উঠবে।
   - চালানের স্ট্যাটাস সবুজ **`Dispatched & Shipped`** হয়ে যাবে।
   - গুদামের স্টক স্বয়ংক্রিয়ভাবে বিয়োগ (`InventoryStock` minus) হবে এবং `StockLedger`-এ `OUT` এন্ট্রি পড়বে!
   - মূল সেলস অর্ডারের শিপমেন্ট স্ট্যাটাস `Partially Delivered` বা `Fully Delivered`-এ রূপান্তর হবে।

#### গ. DomPDF প্যাকিং স্লিপ প্রিন্ট পরীক্ষা (Download Packing Slip PDF):
1. চালান ডিটেইলস পেজে থাকা **Packing Slip PDF** বাটনে চাপ দিন:  
   👉 `http://b2bvikingerp.test/admin/delivery-orders/{id}/pdf`
2. **ফলাফল:** ব্রাউজারে আন্তর্জাতিক মানসম্পন্ন অফিশিয়াল `Commercial Packing Slip / Challan` PDF স্ট্রিম হবে!

---

## 🔟 Step 3.9: কমার্শিয়াল B2B সেলস ইনভয়েসিং ও ফিনান্সিয়াল পোস্টিং ইঞ্জিন

### 💡 কেন ও কখন ব্যবহার করবেন? (Business Purpose)
আন্তর্জাতিক এন্টারপ্রাইজ লেভেলে (SAP S/4HANA Billing Doc VF01 / Odoo 17 Customer Invoices) চালানে মালামাল শিপমেন্টের পর কাস্টমারের হিসাব বিভাগে অফিশিয়ালি টাকা দাবি করার জন্য কমার্শিয়াল সেলস ইনভয়েস (`INV-202608-XXXX`) তৈরি ও ফিনান্সিয়াল অ্যাকাউন্টিং সিঙ্ক করা হয়।
- **স্বয়ংক্রিয় ইনভয়েস সিকোয়েন্স (`INV-YYYYMM-XXXX`):** `OrderNumberService`-এর মাধ্যমে অফিশিয়াল ইনভয়েস নম্বর জেনারেট হওয়া।
- **১-ক্লিক ইনভয়েস জেনারেটর (From DO or SO):** চালান পেজে বা সেলস অর্ডার পেজে থাকা 🧾 **Generate Invoice** বাটনে চাপ দিলে ১-ক্লিকে মালামালের তালিকা, রিয়েল-টাইম ভ্যাট ট্যাক্স ও দাম প্রিলোড হয়ে ইনভয়েস ফর্ম তৈরি হওয়া।
- **জেনারেল লেজার পোস্টিং (Double-Entry Accounting):** ইনভয়েস `Draft` থেকে `Posted` করা হলে স্বয়ংক্রিয়ভাবে খাতা আপডেট হওয়া (`Accounts Receivable` Dr, `Sales Revenue` Cr, `VAT Payable` Cr)।
- **DomPDF কমার্শিয়াল B2B সেলস ইনভয়েস PDF:** সেলার/বায়ার ভ্যাট ট্যাক্স নম্বর, পেমেন্ট ডিউ ডেট (Net 30 Days), এবং ব্যাংকের IBAN/SWIFT ওয়্যার ট্রান্সফার ডিটেইলস সহ প্রিন্টযোগ্য অফিশিয়াল PDF।

### 👣 টেস্ট করার ধাপসমূহ:

#### ক. ১-ক্লিকে ইনভয়েস তৈরি (Create Commercial Sales Invoice):
১. **উপায় ১ (ডেলিভারি চালান পেজ থেকে):**  
   যেকোনো ডিসপ্যাচড চালান পেজে (`http://b2bvikingerp.test/admin/delivery-orders/{id}`) যান এবং উপরে থাকা 🧾 **Generate Invoice** বাটনে ক্লিক করুন।
২. **উপায় ২ (সেলস ইনভয়েস মডিউল থেকে):**  
   👉 `http://b2bvikingerp.test/admin/sales-invoices/create` পেজে যান এবং ড্রপডাউন থেকে ডিসপ্যাচড চালান বেছে নিন।
৩. **বিলিং তথ্য পর্যালোচনা:**  
   - ইনভয়েস তারিখ, পেমেন্ট ডিউ ডেট (Net 30 Days) এবং নোটস চেক করুন।
   - সাবটোটাল, কাস্টমারের ডেটাবেজে থাকা ভ্যাট ট্যাক্স এবং সর্বমোট টাকা পারফেক্টলি হিসাব করবে।
৪. **Generate Commercial Sales Invoice** বাটনে চাপ দিন।

#### খ. ফিনান্সিয়াল পোস্টিং ও লক পরীক্ষা (Post & Accounting Journal Entry):
১. তৈরি হওয়া ইনভয়েস ডিটেইলস পেজে (`http://b2bvikingerp.test/admin/sales-invoices/{id}`) যান।
২. ডানপাশের সাইডবারে থাকা **Post & Journal Entry** বাটনে চাপ দিন।
৩. **SweetAlert পপ-আপ:** *"Post Commercial Invoice?"* কনফার্ম করুন।
৪. **ফলাফল:**  
   - ইনভয়েসের স্ট্যাটাস **`POSTED`** হয়ে যাবে।
   - সাইডবারে **`Posted & Accounting Locked`** ব্যাজ শো করবে (অডিটের সুরক্ষার্থে স্থায়ীভাবে টাকার হিসাব লক হয়ে যাবে)।
   - জেনারেল লেজারে (General Ledger) কাস্টমারের বকেয়া খাতা ও বিক্রি আয় স্বয়ংক্রিয়ভাবে পোস্টিং সম্পন্ন হবে!

#### গ. DomPDF কমার্শিয়াল ইনভয়েস PDF প্রিন্ট পরীক্ষা (PDF Commercial Invoice):
১. ইনভয়েস পেজে থাকা **PDF Commercial Invoice** বাটনে চাপ দিন:  
   👉 `http://b2bvikingerp.test/admin/sales-invoices/{id}/pdf`
২. **ফলাফল:** ব্রাউজারে আন্তর্জাতিক মানসম্পন্ন অফিশিয়াল `Commercial B2B Sales Invoice` PDF স্ট্রিম হবে, যেখানে ব্যাংক ওয়্যার ট্রান্সফার IBAN তথ্য থাকবে!

---

## 1️⃣1️⃣ Step 3.10: কাস্টমার পেমেন্ট কালেকশন, পেড রিসিপ্ট ভাউচার ও ইনভয়েস অ্যাডজাস্টমেন্ট

### 💡 কেন ও কখন ব্যবহার করবেন? (Business Purpose)
আন্তর্জাতিক এন্টারপ্রাইজ লেভেলে (SAP S/4HANA F-28 Customer Payments / Odoo 17 Customer Payments) কাস্টমারদের কাছ থেকে পাওনা টাকা নগদ, ব্যাংক ট্রানজিফশন, চেক বা কার্ডের মাধ্যমে সংগ্রহ করা এবং অনাদায়ী ইনভয়েসের পাওনা মিটিয়ে ফেলা (`due_amount` জিরো করা)।
- **অফিশিয়াল রিসিপ্ট নম্বর সিকোয়েন্স (`REC-YYYYMM-XXXX`):** `OrderNumberService`-এর মাধ্যমে ডায়নামিক রিসিপ্ট নম্বর জেনারেট হওয়া।
- **১-ক্লিক পেমেন্ট ভাউচার জেনারেটর:** ইনভয়েস ডিটেইলস পেজে থাকা 💳 **Record Customer Payment** বাটনে চাপ দিলে কাস্টমার ও পাওনা টাকা স্বয়ংক্রিয়ভাবে প্রিলোড হয়ে পেমেন্ট ফর্ম ওপেন হওয়া।
- **স্বয়ংক্রিয় ইনভয়েস নকডাউন (Auto Due Deduction):** পেমেন্ট পোস্ট হওয়া মাত্রই ইনভয়েসের `paid_amount` প্লাস হবে এবং `due_amount` স্বয়ংক্রিয়ভাবে কমে জিরো (`0.00`) হয়ে যাবে।
- **জেনারেল লেজার ডাবল-এন্ট্রি পোস্টিং (Double-Entry Accounting):** পেমেন্ট সেভ হওয়ার সাথে সাথে ব্যাংকিং খাতা আপডেট হবে (`Cash/Bank Account` Dr, `Accounts Receivable` Cr)।
- **কাস্টমার ক্রেডিট লিমিট অটো-রিলিজ:** পেমেন্ট রিসিভ হওয়ার ফলে কাস্টমারের ব্যবহারের বাকি থাকা **Credit Limit** স্বয়ংক্রিয়ভাবে মুক্ত (Restored) হয়ে যাবে।
- **DomPDF কমার্শিয়াল পেমেন্ট রিসিপ্ট PDF:** ১-ক্লিকে আন্তর্জাতিক মানসম্পন্ন অফিশিয়াল Payment Receipt Voucher PDF জেনারেট ও ডাউনলোড করা।

### 👣 টেস্ট করার ধাপসমূহ:

#### ক. ১-ক্লিকে বা সরাসরি পেমেন্ট এন্ট্রি তৈরি (Record Payment Receipt):
১. **উপায় ১ (ইনভয়েস পেজ থেকে):**  
   যেকোনো অনাদায়ী ইনভয়েস পেজে (`http://b2bvikingerp.test/admin/sales-invoices/{id}`) যান এবং সাইডবারে থাকা 💳 **Record Customer Payment** বাটনে ক্লিক করুন।
২. **উপায় ২ (পেমেন্ট মডিউল থেকে):**  
   👉 `http://b2bvikingerp.test/admin/customer-payments/create` পেজে যান এবং কাস্টমার বা ইনভয়েস ড্রপডাউন বেছে নিন।
৩. **পেমেন্ট মেথড ও তথ্য নির্বাচন:**  
   - **Payment Method:** Bank Transfer, Cash, Cheque বা Card বেছে নিন।
   - **Reference / Cheque No:** ব্যাংকের ট্রানজেকশন বা চেক নম্বর লিখুন (যেমন: `TRF-981245`)।
   - **Amount Received:** জমা হওয়া টাকার পরিমাণ চেক করুন।
৪. **Post Payment Receipt** চাপুন।

#### খ. ইনভয়েস বকেয়া শূন্য ও পোস্টিং পরীক্ষা (Invoice Knockdown & GL Sync):
১. পেমেন্ট ভাউচার পেজে (`http://b2bvikingerp.test/admin/customer-payments/{id}`) রিডাইরেক্ট হবে এবং স্ক্রিনের উপরে **Toastr** নোটিফিকেশন আসবে।
২. ইনভয়েস ডিটেইলস পেজে গিয়ে রিফ্রেশ দিন:  
   - ইনভয়েসের `Due Amount` স্বয়ংক্রিয়ভাবে **`kr. 0.00`** হয়ে যাবে!
   - কাস্টমারের অ্যাকাউন্টে অব্যবহৃত **Credit Limit Exposure** পুনরায় বৃদ্ধি পাবে।
   - জেনারেল লেজারে (General Ledger) ক্যাশ/ব্যাংক ডেবিট ও রিসিভেবল ক্রেডিট পোস্টিং নিশ্চিত হবে!

#### গ. DomPDF পেমেন্ট রিসিপ্ট ভাউচার PDF প্রিন্ট পরীক্ষা (Payment Receipt PDF):
১. পেমেন্ট পেজে থাকা **Print / Download PDF** বাটনে চাপ দিন:  
   👉 `http://b2bvikingerp.test/admin/customer-payments/{id}/pdf`
২. **ফলাফল:** ব্রাউজারে আন্তর্জাতিক অফিশিয়াল `Payment Receipt Voucher` PDF স্ট্রিম হবে, যা কাস্টমারকে ইমেইল বা প্রিন্ট করে প্রদান করা যাবে!

---

## 1️⃣2️⃣ Step 3.11: কাস্টমার সেলস রিটার্ন (RMA), ইনভেন্টরি অটো-রিস্টক, ৩-মোড ক্রেডিট নোট ও ক্রেডিট লিমিট রিলিজ

### 💡 কেন ও কখন ব্যবহার করবেন? (Business Purpose)
আন্তর্জাতিক এন্টারপ্রাইজ লেভেলে ক্ষতিগ্রস্ত বা ভুল পণ্য ফেরত (Customer Returns / RMA) নেওয়া এবং ফাইনান্স বিভাগের জন্য ক্রেডিট নোট (Credit Note) ইস্যু করা।
- **RMA (Return Merchandise Authorization):** আসল সেলস অর্ডারের বিপরীতে কত পিস মালামাল ফেরত নেওয়া হচ্ছে তার সঠিক হিসাব রাখা।
- **SAP/Odoo ৪টি ওয়্যারহাউজ স্টক অ্যাকশন (Warehouse Stock Action):**
  1. 📦 `Restock to Salable Inventory`: বিক্রয়যোগ্য ভালো স্টক পুনরায় গুদামে প্লাস করা (`StockLedger` + `InventoryStock`)।
  2. 🗑️ `Scrap / Write-Off`: ট্রানজিটে ভাঙা/নষ্ট মালামাল স্ক্র্যাপ করা (গুদামের স্টকে ভুল প্লাস হবে না)।
  3. 🔁 `Return to Vendor (RTV)`: ফ্যাক্টরি/সাপ্লাইয়ার ডিফেক্ট হিসেবে আলাদা করা।
  4. 🔬 `Quarantine (Inspection)`: কোয়ালিটি ল্যাব টেস্টের জন্য রাখা।
- **ক্রেডিট লিমিট এক্সপোজার রিলিজ:** রিটার্নের ফলে কাস্টমারের বকেয়া কমে যাওয়া এবং তার অব্যবহৃত Credit Limit পুনরায় মুক্ত (Restored) হয়ে যাওয়া।
- **৩টি ফাইনান্সিয়াল সেটেলমেন্ট মোড (Credit Note Settlement):**
  1. `Mode A: Invoice Offset`: স্রেফ বকেয়া অনাদায়ী ইনভয়েস (`due_amount > 0`) থেকে স্মাৰ্ট ডাইনামিক লিমিটে টাকা বিয়োগ করা।
  2. `Mode B: Product Replacement`: কাস্টমারকে নতুন ভালো প্রোডাক্ট ডাইরেক্ট ইস্যু করা।
  3. `Mode C: Direct Cash / Bank Refund`: কাস্টমারকে সরাসরি ক্যাশ/ব্যাংক রিফান্ড বা রিফান্ড ভাউচার দেওয়া।
- **DomPDF Credit Note Export:** কাস্টমার ও অডিটের জন্য অফিশিয়াল ক্রেডিট নোট PDF জেনারেট করা।

### 👣 টেস্ট করার ধাপসমূহ:

#### ক. RMA রিটার্ন রিকোয়েস্ট তৈরি (Create Customer Return):
1. নেভিগেশন বার থেকে **Orders ➔ Customer Returns (RMA) ➔ Create Customer Return** পেজে যান:  
   👉 `http://b2bvikingerp.test/admin/sales-returns/create`
2. **মাঝখানে সেন্টার্ড সিলেক্ট সার্চ বক্সে (Centered Select Order):**  
   **Select Commercial Order** ড্রপডাউন থেকে যেকোনো একটি সম্পন্ন হওয়া সেলস অর্ডার (যেমন `#DS-11` বা `#SO-202608-XXXX`) সিলেক্ট করুন।
3. **অর্ডার আইটেম টেবিল লোড হবে:**  
   - **Ordered Qty:** অর্ডারের কেনা সংখ্যা দেখাবে।  
   - **Returned Qty:** পূর্বে ফেরত দেওয়া সংখ্যা দেখাবে।  
   - **Unit Price:** একক মূল্য দেখাবে।
4. **Return Qty** বক্সে সংখ্যা বসান (যেমন: `2`)।
5. **Warehouse Stock Action** ড্রপডাউন থেকে সিদ্ধান্ত নিন:
   - মালামাল ভালো থাকলে: 📦 **Restock to Inventory** বেছে নিন।
   - ট্রানজিটে ভাঙা/নষ্ট থাকলে: 🗑️ **Scrap (Damaged in Transit)** বেছে নিন।
6. **Return Reason** ঘরে কারণ লিখুন (যেমন: `Damaged in Transit`) এবং **Submit Return Request** চাপুন।

#### খ. রিটার্ন এপ্রুভাল ও অটো-রিস্টক টেস্ট (Approve Return & Physical Restock):
1. **Orders ➔ Customer Returns (RMA)** লিস্ট পেজ থেকে আপনার তৈরি হওয়া রিটার্নটির পাশে **View** (চোখের আইকন) বাটনে ক্লিক করুন:  
   👉 `http://b2bvikingerp.test/admin/sales-returns/{id}`
2. **Approve & Issue Credit Note** বাটনে ক্লিক করুন।
3. **এন্টারপ্রাইজ SweetAlert পপ-আপ:** সিস্টেম মালামালের অবস্থা বুঝে ডায়নামিক সুইট-অ্যালার্ট দেখাবে:
   - ভালো মাল হলে: *"This will physically restock inventory and issue Credit Note."*
   - ড্যামেজড মাল হলে: *"Return contains DAMAGED items (Scrap). Official Accounts Credit Note will be issued WITHOUT inventory restock."*
4. **Yes, Approve & Issue Credit Note!** বাটনে চাপ দিন।
5. **ফলাফল:**  
   - স্ক্রিনের উপরে সবুজ রঙের Pure **Toastr** পপ-আপ মেসেজ ভেসে উঠবে!
   - রিটার্নটি সবুজ **`Approved`** স্ট্যাটাসে রূপান্তর হবে।  
   - সিদ্ধান্ত অনুযায়ী স্টক প্লাস হবে (ভালো মাল হলে) অথবা স্ক্র্যাপে যাবে (ড্যামেজড হলে)।
   - ফাইনান্সিয়াল লেজারে স্বয়ংক্রিয়ভাবে একটি নতুন **`CN-XXXX`** ক্রেডিট নোট জেনারেট হয়ে যাবে!

#### গ. ক্রেডিট নোট সেটেলমেন্ট ও কাস্টমার ক্রেডিট লিমিট রিলিজ টেস্ট (Settle Credit Note):
1. নেভিগেশন বার থেকে **Orders ➔ Credit Notes** পেজে যান:  
   👉 `http://b2bvikingerp.test/admin/credit-notes`
2. আপনার তৈরি হওয়া ক্রেডিট নোটটির পাশে **View / Settle** বাটনে ক্লিক করুন।
3. **Settle Credit Note** বাটনে চাপ দিন:
   - **Target Unpaid Order** ড্রপডাউনে স্রেফ **বকেয়া টাকা থাকা অর্ডারসমূহ (`due_amount > 0`)** দেখাবে।
   - **Amount to Settle** বক্সে স্বয়ংক্রিয়ভাবে ক্রেডিট নোটের অবশিষ্টাংশ এবং অর্ডারের বকেয়া পাওনার মধ্যে ছোট পরিমাণটি (`Math.min`) ডায়নামিকভাবে লিমিট সেট হয়ে যাবে!
4. **Apply Settlement** চাপুন।
5. **ফলাফল:** কাস্টমারের বকেয়া দায় কমে যাবে এবং কাস্টমারের **অব্যবহৃত Credit Limit Exposure আবার স্বয়ংক্রিয়ভাবে রিলিজ (Free)** হয়ে যাবে!

#### ঘ. DomPDF ক্রেডিট নোট ডাউনলোড পরীক্ষা (Download Credit Note PDF):
1. ক্রেডিট নোট ডিটেইলস পেজে থাকা **PDF Export** বাটনে চাপ দিন:  
   👉 `http://b2bvikingerp.test/admin/credit-notes/{id}/pdf`
2. **ফলাফল:** ব্রাউজারে আন্তর্জাতিক মানসম্পন্ন অফিশিয়াল `Credit Note Document` ডাউনলোড/ভিউ হবে!

---


---

### 🔹 Step 3.12: সেলস অ্যানালিটিক্স, এজিং রিপোর্ট ও ড্যাশবোর্ড ম্যানুয়াল টেস্ট নির্দেশিকা (Enterprise Sales Reports & AR Aging Dashboard Testing Guide)

#### ক. কাস্টমার পোর্টফোলিও AR Aging Receivables রিপোর্ট পরীক্ষা (Customer AR Aging Report Test):
১. ব্রাউজারে কাস্টমার এজিং রিপোর্ট পেজে যান:  
   👉 `http://127.0.0.1:8000/admin/reports/ar-aging`
২. **Executive Metric Cards পরিদর্শন:**  
   - 🟣 **Total Dues:** কাস্টমারদের মোট অনাদায়ী বকেয়া পাওনার পরিমাণ দেখাবে।
   - 🟢 **Current (0-30 Days):** ৩০ দিনের মধ্যে দেওয়া নতুন বকেয়া টাকার পরিমাণ।
   - 🟡 **31 - 60 Days:** ১ মাস পার হওয়া বকেয়া টাকা।
   - 🔵 **61 - 90 Days:** ২ মাস পার হওয়া বকেয়া টাকা।
   - 🔴 **90+ Days (Critical):** ৩ মাসের বেশি আটকে থাকা অতি-ঝুঁকিপূর্ণ বকেয়া টাকা!
৩. **বিটুবি কাস্টমার ফিল্টারিং পরীক্ষা (Filter by B2B Customer):**  
   - **Filter by B2B Customer** ড্রপডাউন থেকে যেকোনো নির্দিষ্ট কাস্টমার সিলেক্ট করে **Apply Filter** চাপুন।  
   - **ফলাফল:** পেজ রিফ্রেশ হয়ে স্রেফ ওই কাস্টমারের এজিং ব্যালেন্স ও রিয়াল-টাইম ইনভয়েস সামারি প্রদর্শন করবে।
৪. **DataTables ইনস্ট্যান্ট সার্চ ফিল্টারিং (Live Search):**  
   - টেবিলে থাকা **Filter Customer Aging** সার্চ বক্সে কাস্টমারের নাম বা ফোন নম্বর লিখুন।  
   - **ফলাফল:** পেজ লোড ছাড়া সাথে সাথে টেবিলে ফিল্টারিং হয়ে যাবে!

#### খ. প্রিন্টযোগ্য অফিশিয়াল AR Aging PDF রিপোর্ট ডাউনলোড পরীক্ষা (Export AR Aging PDF):
১. পেজের উপরে ডানপাশে থাকা **Export PDF Report** বাটনে চাপ দিন:  
   👉 `http://127.0.0.1:8000/admin/reports/ar-aging/pdf`
২. **ফলাফল:** ব্রাউজারে নতুন ট্যাবে আন্তর্জাতিক মানের অফিশিয়াল প্রিন্টযোগ্য `Customer AR Aging PDF Report` ওপেন হবে।

#### গ. সেলস রিপ্রেজেন্টেটিভ ও অ্যাকাউন্ট ম্যানেজার পারফরম্যান্স রিপোর্ট পরীক্ষা (Salesperson Performance Test):
১. ব্রাউজারে সেলস রিপ্রেজেন্টেটিভ পারফরম্যান্স পেজে যান:  
   👉 `http://127.0.0.1:8000/admin/reports/salesperson-performance`
২. **পারফরম্যান্স মেট্রিক্স পরিদর্শন:**  
   - **Total Orders:** অ্যাকাউন্ট ম্যানেজারের মোট অর্ডার সংখ্যা।
   - **Gross Sales Revenue:** মোট বিক্রির পরিমাণ।
   - **Collected Revenue:** কাস্টমার থেকে আদায়কৃত টাকা।
   - **Outstanding Dues:** বকেয়া পাওনা টাকা।
   - **Avg Order Value (AOV):** অর্ডারের গড় মূল্য।
৩. **DataTables কলাম সর্টিং ও পেজিনেশন:**  
   - টেবিলে **Gross Sales Revenue** কলাম হেডারে ক্লিক করে সর্টিং চেক করুন।
   - **Filter Sales Rep** বক্সে যেকোনো সেলস অফিসারের নাম লিখে ইনস্ট্যান্ট সার্চ দিন!

---

💡 **উপসংহার:** এই ম্যানুয়াল অনুসরণ করে সেলস কোটেশন, অর্ডার, ডেলিভারি চালান, সেলস ইনভয়েস, কাস্টমার পেমেন্ট রিসিপ্ট ভাউচার, ক্রেডিট নোট এবং AR Aging Reports এর ১০০% ফিচার ম্যানুয়ালি টেস্ট করা যাবে।

---
---

# 📘 Phase 3 Enterprise Sales Module User & Manual Testing Guide (English Version)

This document provides a comprehensive, step-by-step user manual and manual testing guide for every feature in the **B2B Viking ERP** Sales Module (Phase 3). It details the **Business Purpose**, **Real-World Use Cases**, and **Step-by-Step Manual Testing Instructions** in a single master guide.

---

## 📑 Table of Contents (English)
1. **Step 3.2 — Dynamic Document Sequence Auto-Configuration**
2. **Step 3.3 — Sales Quotation, DomPDF Export, Audit Lock & 1-Click Sales Order Conversion**
3. **Step 3.4 (Part A) — User Credit Limit & Customer Segmentation Tiers**
4. **Step 3.4 (Part B) — Dynamic Customer Pricelists & Live Auto-Pricing Engine**
5. **Step 3.5 (Part A) — Promotional Coupon Code Generator & Usage Validation**
6. **Step 3.5 (Part B) — Gift Card Issuance, Transaction Ledger & Balance Adjustment**
7. **Step 3.6 — Sales Order Management, Credit Limit Validation & Credit Hold Release**
8. **Step 3.7 — Sales Order Polymorphic Approval Workflow**
9. **Step 3.8 — Delivery Order (Challan), Commercial Packing Slip & Partial Shipment Engine**
10. **Step 3.9 — Commercial B2B Sales Invoicing & Financial General Ledger Posting Engine**
11. **Step 3.10 — Customer Payment Collection, Payment Receipt Voucher & Invoice Due Knockdown**
12. **Step 3.11 — Customer Sales Returns (RMA), Inventory Auto-Restock, 3-Mode Credit Notes & Credit Limit Restoration**
13. **Step 3.12 — Enterprise Sales Analytics, Customer AR Aging Dashboard & Salesperson Performance Reports**

---

## 1️⃣ Step 3.2: Dynamic Document Sequence Configuration

### 💡 Why & When to Use? (Business Purpose)
Under international accounting standards (EU & Danish GAAP) and audit compliance rules, every commercial quotation, sales order, delivery challan, or invoice must maintain a distinct, chronological, and tamper-evident sequential number (e.g., `SQ-202608-0001`).
- **When to use:** At the start of a fiscal year, company rebranding, or system initialization, administrators can dynamically customize the sequence prefixes, date formats, and zero-padding for Quotations (`SQ-`), Sales Orders (`SO-`), Invoices (`INV-`), Delivery Orders (`DO-`), and Credit Notes (`CN-`).

### 👣 Step-by-Step Testing Instructions:
1. Log in to the Admin Panel and navigate to **Document Sequences**:  
   👉 `http://b2bvikingerp.test/admin/document-sequences`
2. **Inspect Sequence List:** You will find active sequences for 5 core commercial entities:
   - `Sales Quotation` (Default Prefix: `SQ-`)
   - `Sales Order` (Default Prefix: `SO-`)
   - `Sales Invoice` (Default Prefix: `INV-`)
   - `Delivery Order` (Default Prefix: `DO-`)
   - `Credit Note` (Default Prefix: `CN-`)
3. **Test Edit Sequence:** Click the **Edit** button next to any sequence, change the prefix or padding length, and save.
4. **Verification:** Creating any new document will immediately generate numbers following your updated sequence rules.

---

## 2️⃣ Step 3.3: Sales Quotation, DomPDF Export, Audit Lock & 1-Click SO Conversion

### 💡 Why & When to Use? (Business Purpose)
In B2B wholesale trade, commercial buyers require an estimated price offer and formal proforma proposal before issuing purchase commitments.
- **Sales Quotation:** Sales reps generate formal price quotations with line-item discounts, currency specifications, and VAT breakdown, and deliver them as clean DomPDF documents.
- **1-Click Sales Order Conversion:** When the customer accepts the quotation, the user converts the quotation into a confirmed Sales Order (`SO-XXXXXX`) with a single click, eliminating manual re-entry errors and preserving a clear audit trail.
- **Enterprise Audit Lock:** Once converted, the quotation is permanently locked from modification to prevent retroactive tampering.

### 👣 Step-by-Step Testing Instructions:

#### A. Create Sales Quotation:
1. Go to **Orders ➔ Sales Quotations ➔ Create**:  
   👉 `http://b2bvikingerp.test/admin/sales-quotations/create`
2. **Select Customer:** Pick any B2B wholesale customer.
3. **Set Validity:** Pick a `Valid Until` date.
4. **Add Items:** Click **+ Add Item**, select products, specify quantities, and confirm unit prices.
5. **Tax & Discount Verification:** Select a `Tax` rule (e.g., Moms 25%) and enter an overall discount if applicable. Subtotal, Tax Amount, and Grand Total update in real-time.
6. Click **Save Quotation**. The quotation is saved in `Draft` status.

#### B. Test DomPDF Export:
1. Click **Download PDF** on the quotation details page:  
   👉 `http://b2bvikingerp.test/admin/sales-quotations/{id}/pdf`
2. **Result:** A clean, professional, branded PDF streams directly in your browser with zero memory leaks.

#### C. Test Audit Draft Lock:
1. While in `Draft` status, the **Edit Quote** button is active.
2. Once converted to a Sales Order, the Edit button is **automatically hidden and locked**.

#### D. 1-Click Sales Order Conversion (SweetAlert Modal):
1. Click the green **Convert to Sales Order (SO)** button.
2. **SweetAlert Confirmation Appears:**  
   - Title: `Convert to Sales Order?`
   - Prompt: `Are you sure you want to convert Quotation SQ-XXXXXX into an official Sales Order (SO)?`
3. Click **Yes, Convert to SO!**. The quotation instantly converts into a verified Sales Order (`SO-202608-XXXX`).

---

## 3️⃣ Step 3.4 (Part A): User Credit Limit & Customer Segmentation Tiers

### 💡 Why & When to Use? (Business Purpose)
Every B2B wholesale customer possesses a distinct risk profile, payment track record, and commercial volume tier.
- **Customer Segmentation (`Retail`, `Wholesale`, `B2B VIP`, `Distributor`):** Classifies buyers into structured commercial groups to automatically apply tier-specific pricelists and volume pricing.
- **Credit Limit ($ / kr.):** Sets the maximum allowable outstanding balance for credit purchases (e.g., 50,000 kr.), safeguarding the business against bad debts and overexposure.

### 👣 Step-by-Step Testing Instructions:
1. Navigate to **User Management ➔ Users**:  
   👉 `http://b2bvikingerp.test/admin/users`
2. **Inspect DataTable:** Review the **Segment / Entity** badge and **Credit Limit** (`kr. XX,XXX.XX`) columns for each user.
3. **Edit / Create User:**
   - Set **Customer Segment / Tier:** `Wholesale Customer`
   - Set **Credit Limit:** `50000`
4. Click **Update User**. The DataTable displays the blue `WHOLESALE` badge and updated `kr. 50,000.00` credit limit.

---

## 4️⃣ Step 3.4 (Part B): Dynamic Customer Pricelists & Live Auto-Pricing Engine

### 💡 Why & When to Use? (Business Purpose)
Eliminates manual price negotiation per order by binding contracted wholesale rates to specific customer tiers.
- **Why it matters:** Retail buyers purchase at 250 kr., Wholesale buyers at 150 kr., and contracted VIP Distributors at 120 kr.
- **Live Automation:** When a sales representative creates a quotation or order and selects a customer, the system **automatically injects the contracted tier special price**, bypassing standard retail MRPs.

### 👣 Step-by-Step Testing Instructions:

#### A. Create a Wholesale Pricelist:
1. Navigate to **Orders ➔ Customer Pricelists**:  
   👉 `http://b2bvikingerp.test/admin/pricelists`
2. Click **Create Pricelist**:  
   👉 `http://b2bvikingerp.test/admin/pricelists/create`
3. **Fill Form:**
   - `Pricelist Name`: `2026 Wholesale Tier Offer`
   - `Target Customer Segment`: `Wholesale Customer`
   - `Status`: `Active`
4. **Define Tier Pricing:**
   - Select a product (e.g., `Magnet-DS 1058`). The standard MRP (e.g., `35.00` kr.) populates.
   - Enter the **Tier Special Price** (e.g., `25.00` kr.).
5. Click **Save Pricelist**.

#### B. Live Auto-Pricing Magic Test:
1. Go to **Sales Quotations ➔ Create**:  
   👉 `http://b2bvikingerp.test/admin/sales-quotations/create`
2. In the **Customer** dropdown, pick your **Wholesale Customer**.
3. In the product row, select `Magnet-DS 1058`.
4. **Result:** Instead of the regular `35.00` kr., the system **automatically loads the tier contracted price of `25.00` kr.!**

---

## 5️⃣ Step 3.5 (Part A): Promotional Coupon Codes

### 💡 Why & When to Use? (Business Purpose)
Used to execute marketing campaigns, seasonal holiday discounts, or partner promotions.
- **Why it matters:** Create custom coupon codes like `WELCOME2026` or auto-generate alphanumeric codes with usage caps (e.g., first 50 orders) and expiration date validation.

### 👣 Step-by-Step Testing Instructions:
1. Go to **Orders ➔ Promo Coupons**:  
   👉 `http://b2bvikingerp.test/admin/coupons`
2. Click **Create Coupon Code**:  
   👉 `http://b2bvikingerp.test/admin/coupons/create`
3. **Fill Form:**
   - **Coupon Code:** Enter a custom code (e.g., `WELCOME2026`) or click **Auto Generate** (e.g., `VIKING-9X2K8L`).
   - **Linked Discount Rule:** Select an active rule (e.g., `10% Campaign Discount`).
   - **Usage Limit:** Set `50` (caps maximum redemptions).
   - **Expiration Date:** Pick a future date.
4. Click **Save Coupon Code**. The coupon displays in the DataTable.

---

## 6️⃣ Step 3.5 (Part B): Gift Card Issuance & Transaction Ledger

### 💡 Why & When to Use? (Business Purpose)
Used for corporate gifts, store loyalty credits, and refund vouchers.
- **Why it matters:** Issues 16-digit unique prepaid gift cards (e.g., 1,000 kr.).
- **Audit Ledger:** Every partial redemption automatically tracks the remaining balance and logs transparent debit/credit ledger entries tied to specific sales orders.

### 👣 Step-by-Step Testing Instructions:

#### A. Issue Gift Card:
1. Navigate to **Orders ➔ Gift Cards Engine**:  
   👉 `http://b2bvikingerp.test/admin/gift-cards`
2. Click **Issue Gift Card**:  
   👉 `http://b2bvikingerp.test/admin/gift-cards/create`
3. **Fill Form:**
   - **Gift Card Number:** Auto-generated 16-digit code (e.g., `GC-5206-3923-8251`).
   - **Initial Value:** Enter `1000` (1,000 kr.).
4. Click **Issue Gift Card**.

#### B. Audit Ledger & Balance Adjustment Test:
1. Click the **View Ledger** (eye icon) button next to any card:  
   👉 `http://b2bvikingerp.test/admin/gift-cards/{id}`
2. **Review Summary:** Initial balance `kr. 1,000.00` and current balance `kr. 1,000.00` are displayed.
3. **Transaction Table:** An `ISSUED` transaction record is present.
4. **Test Adjustment:** Enter `+200` or `-150` in the **Adjust Balance** box, enter a reason, and click **Apply Adjustment**. Balance updates immediately with a recorded `ADJUSTED` ledger log.

---

## 7️⃣ Step 3.6: Sales Order Management, Credit Validation & Credit Hold Release

### 💡 Why & When to Use? (Business Purpose)
Following SAP S/4HANA & Odoo 17 enterprise credit risk management standards, this engine automates financial risk controls on sales orders.
- **Automated Credit Exposure Calculation:** Total Exposure = (Existing Outstanding Dues + Current Order Grand Total).
- **Automated Credit Hold:** If the order exceeds the customer's approved credit limit, the system automatically flags the order as **`Credit Hold`** and halts shipment.
- **Authorized Manager Override:** A designated finance manager or administrator can review the hold and execute an authorized 1-click **Release Credit Hold** with an audit comment.

### 👣 Step-by-Step Testing Instructions:

#### A. Test Live Credit Exposure Widget (Order Creation):
1. Go to **Orders ➔ Sales Orders (SO) ➔ Create**:  
   👉 `http://b2bvikingerp.test/admin/sales-orders/create`
2. **Select Customer:** Pick a Wholesale Customer with an established credit limit (e.g., `10000` kr.).
3. **Check Credit Exposure Widget:** The widget shows the Approved Limit, Current Dues, and Available Credit Balance.
4. **Trigger Credit Hold:** Add items such that the grand total exceeds available credit (e.g., `kr. 12,000.00`).
5. Click **Save & Process Order**.

#### B. Verify Credit Hold & Authorized Release:
1. **Result:** Order saves in **`CREDIT HOLD`** status (red badge).
2. **Warning Banner:** The order details page displays:  
   `Order Flagged Under Credit Hold: This order exceeds customer approved credit limit exposure.`
3. **Click Release Credit Hold:**  
   - Modal opens: **Authorize Credit Hold Release**.
   - Enter note: `Approved by Finance Manager on Verbal Guarantee`.
   - Click **Authorize Release**.
4. **Result:** Order status transforms immediately to green **`APPROVED`**!

---

## 8️⃣ Step 3.7: Sales Order Polymorphic Approval Workflow

### 💡 Why & When to Use? (Business Purpose)
Complies with enterprise governance rules (SAP SD Order Approval / Odoo Sales Approval) requiring high-value or restricted orders to undergo hierarchical managerial approval before commitment.
- **Polymorphic Approval Engine (`App\Models\Approval`):** Tracks dynamic multi-tier approval steps.
- **Role-Based Step UI (`ApprovalService.php`):** Renders dynamic approval steppers, authorized approver roles, and 1-click **Approve Order** or **Reject Order** actions.
- **Automated Transition:** Once all required approval tiers are completed, status transitions from `Pending Approval` to `Approved`.

### 👣 Step-by-Step Testing Instructions:

#### A. Submit Order for Approval:
1. On a Draft Sales Order page (`http://b2bvikingerp.test/admin/orders/{id}`), click **Submit for Approval**.
2. **Result:** Status changes to yellow **`PENDING APPROVAL`** and the dynamic approval progress stepper renders.

#### B. Approve / Reject Order Step:
1. Log in as an authorized Manager and open the order details page.
2. Click **Approve Order** on the active approval step.
3. **Result:** A success Toastr notification fires, the order status transitions to green **`APPROVED`**, and downstream delivery order actions unlock.

---

## 9️⃣ Step 3.8: Delivery Order (Challan), Packing Slip & Partial Shipment Engine

### 💡 Why & When to Use? (Business Purpose)
Complies with warehouse fulfillment standards (SAP S/4HANA VL01N / Odoo 17 Delivery Orders) by issuing commercial delivery challans (`DO-202608-XXXX`) against approved sales orders.
- **Sequential Challan Numbers (`DO-YYYYMM-XXXX`):** Auto-generated via `OrderNumberService`.
- **Partial Dispatch & Back-Order Tracking:** If 50 units of a 100-unit order are dispatched today, the remaining 50 units are automatically maintained as an active `Back-Order`.
- **Logistics & AWB Carrier Tracking:** Records shipping carrier (DHL, PostNord, DSV, FedEx, Local Truck) and AWB tracking numbers.
- **Inventory Stock Deduction:** Dispatching a delivery order immediately reduces physical stock (`InventoryStock`) and logs an `OUT` transaction in `StockLedger`.
- **DomPDF Commercial Packing Slip:** 1-click generation of printable commercial packing slips.

### 👣 Step-by-Step Testing Instructions:

#### A. Create Delivery Order:
1. **Method 1 (From Order):** Open an approved order (`http://b2bvikingerp.test/admin/orders/{id}`) and click 🚚 **Create Delivery Order**.
2. **Method 2 (From DO Module):** Open `http://b2bvikingerp.test/admin/delivery-orders/create` and select the order.
3. **Fulfillment Details:**
   - **Carrier:** Select DHL, PostNord, DSV, or Local Truck.
   - **AWB Tracking:** Enter tracking reference (e.g., `AWB-982341823`).
   - **Dispatch Qty:** Specify units dispatched in this shipment (e.g., `50`).
4. Click **Create Delivery Order (Challan)**.

#### B. Dispatch & Warehouse Stock Deduction Test:
1. Open the created Delivery Order (`http://b2bvikingerp.test/admin/delivery-orders/{id}`).
2. Click **Dispatch & Ship Order**.
3. **SweetAlert Confirmation:** Confirm *"Dispatch & Ship Delivery Order #DO-XXXX?"*.
4. **Result:**  
   - Challan status changes to green **`Dispatched & Shipped`**.
   - Warehouse stock is deducted (`InventoryStock` reduced) with an `OUT` ledger entry in `StockLedger`.
   - The originating Sales Order shipment status updates to `Partially Delivered` or `Fully Delivered`.

#### C. DomPDF Packing Slip PDF Export:
1. Click **Packing Slip PDF** on the delivery order page:  
   👉 `http://b2bvikingerp.test/admin/delivery-orders/{id}/pdf`
2. **Result:** Streams a clean, branded Commercial Packing Slip / Delivery Challan PDF with full item details.

---

## 🔟 Step 3.9: Commercial B2B Sales Invoicing & Financial Posting Engine

### 💡 Why & When to Use? (Business Purpose)
Following international billing standards (SAP VF01 Billing / Odoo Invoicing), commercial sales invoices (`INV-202608-XXXX`) formalize receivables upon shipment and synchronize with the General Ledger.
- **Sequential Invoice Numbers (`INV-YYYYMM-XXXX`):** Auto-generated via `OrderNumberService`.
- **1-Click Generation (From DO or SO):** Preloads line items, quantities, VAT tax calculations, and payment terms (Net 30 Days).
- **Double-Entry General Ledger Posting:** Posting an invoice debits `Accounts Receivable` and credits `Sales Revenue` and `VAT Payable`.
- **DomPDF Commercial B2B Invoice PDF:** Printable official invoice featuring seller/buyer VAT IDs, payment terms, and bank wire transfer IBAN/SWIFT details.

### 👣 Step-by-Step Testing Instructions:

#### A. Create Commercial Sales Invoice:
1. **Method 1:** Open a dispatched delivery order and click 🧾 **Generate Invoice**.
2. **Method 2:** Go to `http://b2bvikingerp.test/admin/sales-invoices/create` and select the dispatched challan.
3. **Review Billing Details:** Verify invoice date, payment due date (Net 30 Days), line items, VAT amounts, and grand total.
4. Click **Generate Commercial Sales Invoice**.

#### B. Financial Posting & Audit Lock:
1. Open the invoice details page (`http://b2bvikingerp.test/admin/sales-invoices/{id}`).
2. In the right sidebar, click **Post & Journal Entry**.
3. Confirm the SweetAlert prompt.
4. **Result:** Status updates to **`POSTED`**, the accounting lock is permanently engaged, and financial journal entries are posted to the General Ledger.

#### C. Export Commercial Invoice PDF:
1. Click **PDF Commercial Invoice**:  
   👉 `http://b2bvikingerp.test/admin/sales-invoices/{id}/pdf`
2. **Result:** Streams a compliant B2B Commercial Sales Invoice PDF complete with company IBAN and VAT tax credentials.

---

## 1️⃣1️⃣ Step 3.10: Customer Payment Collection & Invoice Due Knockdown

### 💡 Why & When to Use? (Business Purpose)
Following standard accounts receivable practices (SAP F-28 Customer Payment / Odoo Customer Payments), this engine records collections via Bank Transfer, Cash, Cheque, or Card, knocking down outstanding invoice balances (`due_amount`).
- **Sequential Receipt Numbers (`REC-YYYYMM-XXXX`):** Auto-generated via `OrderNumberService`.
- **1-Click Payment Voucher:** Clicking 💳 **Record Customer Payment** pre-populates the customer, invoice reference, and outstanding balance.
- **Automated Due Deduction (Knockdown):** Recording payment increments `paid_amount` and reduces `due_amount` to zero (`0.00`).
- **Double-Entry GL Posting:** Debits `Cash/Bank Account` and credits `Accounts Receivable`.
- **Credit Limit Restoration:** Collecting dues automatically frees up customer credit limit capacity.
- **DomPDF Payment Receipt Voucher:** Printable official payment receipt for customer delivery.

### 👣 Step-by-Step Testing Instructions:

#### A. Record Payment Receipt:
1. **Method 1:** Open an unpaid invoice (`http://b2bvikingerp.test/admin/sales-invoices/{id}`) and click 💳 **Record Customer Payment**.
2. **Method 2:** Go to `http://b2bvikingerp.test/admin/customer-payments/create`.
3. **Specify Payment Details:**
   - **Payment Method:** Choose Bank Transfer, Cash, Cheque, or Card.
   - **Reference / Cheque No:** Enter transaction ID (e.g., `TRF-981245`).
   - **Amount Received:** Enter collected amount.
4. Click **Post Payment Receipt**.

#### B. Verify Invoice Knockdown & GL Sync:
1. Redirects to the payment voucher page (`http://b2bvikingerp.test/admin/customer-payments/{id}`) with a success notification.
2. Refresh the original invoice page:  
   - Invoice `Due Amount` displays **`kr. 0.00`** (Fully Paid).
   - Customer's available Credit Limit capacity is restored.
   - General Ledger reflects updated cash/bank and receivables balances.

#### C. Download Payment Receipt PDF:
1. Click **Print / Download PDF**:  
   👉 `http://b2bvikingerp.test/admin/customer-payments/{id}/pdf`
2. **Result:** Streams an official Payment Receipt Voucher PDF.

---

## 1️⃣2️⃣ Step 3.11: Customer Sales Returns (RMA), Restock, Credit Notes & Limit Release

### 💡 Why & When to Use? (Business Purpose)
Handles product returns, damaged in-transit goods (RMA), inventory restocking, and official Credit Note (`CN-202608-XXXX`) issuance.
- **RMA (Return Merchandise Authorization):** Tracks returned quantities against the original sales order.
- **4 Warehouse Stock Actions (SAP / Odoo standard):**
  1. 📦 `Restock to Salable Inventory`: Restores undamaged goods to available stock (`StockLedger` + `InventoryStock`).
  2. 🗑️ `Scrap / Write-Off`: Discards damaged goods without inflating inventory counts.
  3. 🔁 `Return to Vendor (RTV)`: Flags factory/supplier defects for vendor claims.
  4. 🔬 `Quarantine (Inspection)`: Holds items for quality inspection.
- **Credit Limit Restoration:** Reduces customer outstanding dues and restores available credit line.
- **3 Financial Settlement Modes:**
  1. `Mode A: Invoice Offset`: Offsets against unpaid invoices (`due_amount > 0`).
  2. `Mode B: Product Replacement`: Issues clean replacement items.
  3. `Mode C: Direct Cash / Bank Refund`: Processes cash/bank refunds.
- **DomPDF Credit Note Export:** Generates official credit note documentation for audit compliance.

### 👣 Step-by-Step Testing Instructions:

#### A. Create RMA Return Request:
1. Go to **Orders ➔ Customer Returns (RMA) ➔ Create**:  
   👉 `http://b2bvikingerp.test/admin/sales-returns/create`
2. In the **Select Commercial Order** dropdown, choose a fulfilled sales order (e.g., `#SO-202608-XXXX`).
3. **Item Grid Loads:** Shows Ordered Qty, Previous Returns, and Unit Prices.
4. Enter **Return Qty** (e.g., `2`).
5. Select **Warehouse Stock Action**:  
   - Good condition: 📦 **Restock to Inventory**
   - Damaged in transit: 🗑️ **Scrap (Damaged in Transit)**
6. Enter **Return Reason** and click **Submit Return Request**.

#### B. Approve Return & Restock Test:
1. Open the created return (`http://b2bvikingerp.test/admin/sales-returns/{id}`).
2. Click **Approve & Issue Credit Note**.
3. **SweetAlert Notice:**  
   - Restock items: *"This will physically restock inventory and issue Credit Note."*
   - Scrap items: *"Return contains DAMAGED items (Scrap). Official Accounts Credit Note will be issued WITHOUT inventory restock."*
4. Confirm approval.  
   - Return status changes to green **`Approved`**.
   - Inventory restocks or scraps according to selected action.
   - An official **`CN-XXXX`** Credit Note is generated.

#### C. Credit Note Settlement & Credit Limit Release:
1. Go to **Orders ➔ Credit Notes**:  
   👉 `http://b2bvikingerp.test/admin/credit-notes`
2. Click **View / Settle** next to the credit note.
3. Click **Settle Credit Note**:
   - The **Target Unpaid Order** dropdown lists only orders with outstanding balances (`due_amount > 0`).
   - The **Amount to Settle** box dynamically constrains to `Math.min(CreditNoteBalance, InvoiceDue)`.
4. Click **Apply Settlement**. Outstanding dues reduce and customer available credit limit restores immediately.

#### D. Download Credit Note PDF:
1. Click **PDF Export**:  
   👉 `http://b2bvikingerp.test/admin/credit-notes/{id}/pdf`
2. **Result:** Streams a compliant Credit Note PDF document.

---

## 1️⃣3️⃣ Step 3.12: Enterprise Sales Reports & Customer AR Aging Dashboard

### 👣 Step-by-Step Testing Instructions:

#### A. Customer Portfolio AR Aging Receivables Report:
1. Navigate to the AR Aging report page:  
   👉 `http://127.0.0.1:8000/admin/reports/ar-aging`
2. **Review Executive Metric Cards:**  
   - 🟣 **Total Dues:** Aggregate uncollected receivables across all customers.
   - 🟢 **Current (0-30 Days):** Fresh invoices within standard terms.
   - 🟡 **31 - 60 Days:** Receivables 1 month overdue.
   - 🔵 **61 - 90 Days:** Receivables 2 months overdue.
   - 🔴 **90+ Days (Critical):** High-risk aged debts requiring credit collection intervention.
3. **Filter by B2B Customer:** Select a specific customer from the dropdown and click **Apply Filter** to inspect individual aging profiles.
4. **DataTable Live Search:** Type a customer name or phone number in the search box to filter rows in real-time.

#### B. Export AR Aging PDF Report:
1. Click **Export PDF Report**:  
   👉 `http://127.0.0.1:8000/admin/reports/ar-aging/pdf`
2. **Result:** Opens a formatted, printable official Customer AR Aging Report PDF in a new tab.

#### C. Salesperson & Account Manager Performance Report:
1. Navigate to the Salesperson Performance report page:  
   👉 `http://127.0.0.1:8000/admin/reports/salesperson-performance`
2. **Inspect Performance KPIs:**  
   - **Total Orders:** Count of orders managed by account rep.
   - **Gross Sales Revenue:** Total booked sales value.
   - **Collected Revenue:** Actual cash/bank collections.
   - **Outstanding Dues:** Uncollected receivables.
   - **Average Order Value (AOV):** Mean value per order.
3. **DataTable Sorting:** Click on the **Gross Sales Revenue** column header to sort reps by revenue performance.

---

💡 **Conclusion:** By following this manual, all Phase 3 features—Sales Quotations, Orders, Delivery Challans, Commercial Invoices, Payment Receipts, Credit Notes, and AR Aging Reports—can be thoroughly tested and validated against enterprise standards.

