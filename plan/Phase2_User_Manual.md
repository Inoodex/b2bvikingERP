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
4. সিস্টেম স্বয়ংক্রিয়ভাবে কারেন্সি কনভার্ট করে বেস কারেন্সিতে (যেমন: DKK বা BDT) সবচেয়ে কম দাম (L1 Bidder) কে দিয়েছে, তা হাইলাইট করে দেখাবে!
5. **Submit CS for Approval** বাটনে ক্লিক করুন।

---

## Step 2: Purchase Orders (PO), Proforma Invoices (PI) & LC Register (ইম্পোর্ট ট্র্যাকিং)

### ২.১ Automatic Purchase Order (PO) Generation
Comparison Statement (CS) অনুমোদন হয়ে গেলে বিজয়ী ভেন্ডরদের নামে অটোমেটিক Purchase Order জেনারেট করা যায়।

**কীভাবে PO জেনারেট ও ম্যানেজ করবেন?**
1. অনুমোদিত RFQ বা CS পেজে গিয়ে **+ Generate PO** বাটনে ক্লিক করুন।
2. একাধিক ভেন্ডর বিজয়ী হলে সিস্টেম স্বয়ংক্রিয়ভাবে আলাদা আলাদা **Split PO (`PO-00001`, `PO-00002`...)** তৈরি করবে।
3. **Procurement > Purchase Orders (PO)** ড্রপডাউন মেন্যুতে ক্লিক করে সকল PO-এর তালিকা ও মাইলস্টোন স্ট্যাটাস দেখতে পাবেন।
4. **View Details** বাটনে ক্লিক করে **Download PO PDF** এবং **Send PO Email to Supplier** বাটনের মাধ্যমে সাপ্লায়ারকে সরাসরি ইমেইল পাঠাতে পারবেন।

---

### ২.২ Proforma Invoice (PI) Document Attachment
সাপ্লায়ার PO পাওয়ার পর তাদের চুড়ান্ত Proforma Invoice (PI) পাঠাবে, যা সিস্টেমে আপলোড করতে হবে।

**কীভাবে PI ফাইল অ্যাটাচ করবেন?**
1. নির্দিষ্ট PO Details পেজে প্রবেশ করুন।
2. ডানে থাকা **Proforma Invoice (PI)** কার্ড ফর্মে PI নম্বর, ইস্যু তারিখ ও মোট টাকার পরিমাণ লিখুন।
3. ভেন্ডরের পাঠানো PI ডকুমেন্ট (PDF/Image) আপলোড করে **Upload & Attach PI** বাটনে ক্লিক করুন।
4. সাথে সাথে PO-এর স্ট্যাটাস **`pi_attached`** ধাপে আপডেট হয়ে যাবে।

---

### ২.৩ International Letter of Credit (LC) & Expenses Breakdown
বিদেশী ক্রয়ের জন্য ব্যাংকে খোলা এলসি (LC) রেজিস্টার করা এবং ১৩টি ইমপোর্ট খরচ হিসাব রাখা।

**কীভাবে LC রেজিস্টার ও খরচ ইনপুট দেবেন?**
1. নির্দিষ্ট PO Details পেজের **Register LC** ফর্মে এলসি নম্বর, ইস্যুয়িং ব্যাংক, এলসি অ্যামাউন্ট, মার্জিন % এবং মেয়াদের তারিখ লিখুন।
2. **Register LC** বাটনে ক্লিক করার সাথে সাথে LC রেজিস্টার তৈরি হয়ে যাবে এবং স্ট্যাটাস **`lc_opened`** হবে।
3. **Procurement > LC Register** মেন্যুতে ক্লিক করে সকল এলসির তালিকা, মার্জিন % এবং **১৩টি আমদানিকৃত খরচ (CD, RD, SD, VAT, AIT, AT, Margin, Insurance, Freight, C&F Agent Cost)** দেখতে পারবেন।
4. এলসির মেয়াদ বাড়লে **Record LC Amendment** বাটনে ক্লিক করে অ্যামেন্ডমেন্ট হিস্ট্রি সংরক্ষণ করতে পারবেন।

---

# English Version

## 1. Introduction
Welcome to the Phase 2 User Manual of B2B Viking ERP! This phase covers the complete Procurement process in 4 distinct Steps. Below, you will find the step-by-step guide for **Step 1 (RFQ & CS)** and **Step 2 (Purchase Orders, PI & LC Tracking)**.

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
4. Click **View Details** to **Download PO PDF** or click **Send PO Email to Supplier** to email the PO document to the vendor.

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
*Document Version: 2.0 (Phase 2, Step 1 & Step 2 Complete)*  
*Generated for B2B Viking ERP Client*
