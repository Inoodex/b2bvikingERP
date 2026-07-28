# B2B Viking ERP - User Manual (Phase 2)
**Purchase, Import (LC) & Quotation Management**

---

## 1. Introduction (পরিচিতি)
B2B Viking ERP-এর Phase 2-এর ম্যানুয়ালে আপনাকে স্বাগতম! এই ফেজে মূলত প্রকিউরমেন্ট (Procurement) বা কেনাকাটার সম্পূর্ণ প্রসেসটি ৪টি ধাপে (Steps) কভার করা হয়েছে। নিচে 
**Step 1 (RFQ & CS)**-এর কাজগুলো কীভাবে সিস্টেমে করতে হবে, তা ধাপে ধাপে বর্ণনা করা হলো।।

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

> [!IMPORTANT]
> **Approval Workflow Integration:**
> CS সাবমিট করার সাথে সাথেই তা সরাসরি আমাদের **Advanced Approval Engine**-এ চলে যাবে! অর্থাৎ, টাকার অংকের ওপর ভিত্তি করে আপনি যে রুলস বানিয়েছিলেন (যেমন: Manager -> Admin), সেই অনুযায়ী CS-টি অ্যাপ্রুভালের জন্য নির্দিষ্ট ইউজারদের কাছে **Pending** অবস্থায় আটকে থাকবে। তারা অ্যাপ্রুভ করলে তবেই এটি ফাইনাল হবে।

---

# English Version

## 1. Introduction
Welcome to the Phase 2 User Manual of B2B Viking ERP! This phase covers the complete Procurement process in 4 distinct Steps. Below, you will find the step-by-step guide for **Step 1 (RFQ & CS)**.

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

> [!IMPORTANT]
> **Approval Workflow Integration:**
> As soon as the CS is submitted, it directly enters our **Advanced Approval Engine**! This means it will be held in a **Pending** state and routed to specific users based on your predefined monetary rules (e.g., Manager -> Admin). It becomes final only after they approve it.

---
*Document Version: 1.0 (Phase 2, Step 1)*
*Generated for B2B Viking ERP Client*
