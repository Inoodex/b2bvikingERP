# 📘 B2B Viking ERP — Phase 5 Accounts Module User Manual
### ব্যবহারকারী নির্দেশিকা ও পরিচালন সহায়িকা (বাংলা ও English সংস্করণ)

---

# ========================================================
# 🇧🇩 অংশ ১: বাংলা সংস্করণ (ব্যবহারকারী নির্দেশিকা)
# ========================================================

## 📑 সূচিপত্র (বাংলা)
1. [পরিচিতি ও অ্যাকাউন্টিং কাঠামো](#১-পরিচিতি-ও-অ্যাকাউন্টিং-কাঠামো)
2. [সাধারণ খতিয়ান ও প্রাথমিক সেটআপ (General Ledger & Setup)](#২-সাধারণ-খতিয়ান-ও-প্রাথমিক-সেটআপ)
   - ২.১ [চার্ট অব অ্যাকাউন্টস (COA)](#২১-চার্ট-অব-অ্যাকাউন্টস-coa)
   - ২.২ [ম্যানুয়াল জার্নাল ভাউচার (Manual JV)](#২২-ম্যানুয়াল-জার্নাল-ভাউচার-manual-jv)
   - ২.৩ [ফিসকাল ইয়ার ও পিরিয়ড লক (Fiscal Years & Lock)](#২৩-ফিসকাল-ইয়ার-ও-পিরিয়ড-লক)
3. [ব্যাংক ও নগদ ব্যবস্থাপনা (Banking & Treasury)](#৩-ব্যাংক-ও-নগদ-ব্যবস্থাপনা)
   - ৩.১ [ব্যাংক অ্যাকাউন্ট ও ক্যাশ ভল্ট](#৩১-ব্যাংক-অ্যাকাউন্ট-ও-ক্যাশ-ভল্ট)
   - ৩.২ [ব্যাংক রিকনসিলিয়েশন (হিসাব মেলানো)](#৩২-ব্যাংক-রিকনসিলিয়েশন-হিসাব-মেলানো)
   - ৩.৩ [পেটি ক্যাশ রেজিস্টার (অফিস খরচ)](#৩৩-পেটি-ক্যাশ-রেজিস্টার-অফিস-খরচ)
   - ৩.৪ [আন্তঃঅ্যাকাউন্ট ফান্ড ট্রান্সফার (কন্ট্রা ভাউচার)](#৩৪-আন্তঃঅ্যাকাউন্ট-ফান্ড-ট্রান্সফার-কন্ট্রা-ভাউচার)
4. [কাস্টমার হিসাব ও দেনাদার (Accounts Receivable - AR)](#৪-কাস্টমার-হিসাব-ও-দেনাদার-ar)
   - ৪.১ [সেলস ইনভয়েস](#৪১-সেলস-ইনভয়েস)
   - ৪.২ [টাকা গ্রহণ ও রিসিট (Receive Payment & Receipts)](#৪২-টাকা-গ্রহণ-ও-রিসিট)
   - ৪.৩ [কাস্টমারদের বকেয়া অর্ডার (Customer Due Orders)](#৪৩-কাস্টমারদের-বকেয়া-অর্ডার)
5. [সরবরাহকারী হিসাব ও পাওনাদার (Accounts Payable - AP)](#৫-সরবরাহকারী-হিসাব-ও-পাওনাদার-ap)
   - ৫.১ [ভেন্ডর বিল ও পেমেন্ট ভাউচার](#৫১-ভেন্ডর-বিল-ও-পেমেন্ট-ভাউচার)
   - ৫.২ [ভেন্ডর বকেয়া ও এজিন্গ বিশ্লেষণ (Vendor Due & AP Aging)](#৫২-ভেন্ডর-বকেয়া-ও-এজিন্গ-বিশ্লেষণ)
6. [স্থায়ী সম্পদ ও অবচয় ইঞ্জিন (Fixed Assets & Depreciation)](#৬-স্থায়ী-সম্পদ-ও-অবচয়-ইঞ্জিন)
7. [আর্থিক প্রতিবেদন ও অডিট (Financial Statements & Reports)](#৭-আর্থিক-প্রতিবেদন-ও-অডিট)
8. [সাধারণ জিজ্ঞাসা ও সমাধান (Troubleshooting & FAQs)](#৮-সাধারণ-জিজ্ঞাসা-ও-সমাধান)

---

## ১. পরিচিতি ও অ্যাকাউন্টিং কাঠামো

B2B Viking ERP-এর Phase 5 অ্যাকাউন্টিং সিস্টেমটি আন্তর্জাতিক **IFRS এবং পূর্ণাঙ্গ ডাবল-এন্ট্রি বুককিপিং** নিয়মানুযায়ী তৈরি করা হয়েছে। 

* **জিরো-ইমব্যালান্স গ্যারান্টি (Zero Imbalance):** সিস্টেমে প্রতিটি লেনদেনের মোট ডেবিট এবং মোট ক্রেডিট ১০০% সমান হতে হবে। এমনকি ১ পয়সার অমিল থাকলেও সিস্টেম স্বয়ংক্রিয়ভাবে লেনদেন আটকে দেবে।
* **৫-স্তরের আন্তর্জাতিক চার্ট অব অ্যাকাউন্টস (COA):**
  - **১০০০ সিরিজ:** Assets (সম্পদ — ক্যাশ, ব্যাংক, দেনাদার, ইনভেন্টরি, যন্ত্রপাতি ইত্যাদি)
  - **২০০০ সিরিজ:** Liabilities (দায় — পাওনাদার, বকেয়া বেতন, ঋণ ইত্যাদি)
  - **৩০০০ সিরিজ:** Equity (মালিকানাস্বত্ব — মূলধন, রিটেইন্ড আর্নিংস ইত্যাদি)
  - **৪০০০ সিরিজ:** Revenue (রাজস্ব — পণ্য বিক্রয়, সেবা আয় ইত্যাদি)
  - **৫০০০ সিরিজ:** Expenses (ব্যয় — ক্রয় খরচ, অফিস খরচ, অবচয় ইত্যাদি)
* **পরিচ্ছন্ন মেনু বিন্যাস:** দৈনন্দিন বিল ও ভাউচার তৈরির মতো কাজের অপশনগুলো রাখা হয়েছে সাইডবারের **`Accounts`** মেনুতে, আর সমস্ত খতিয়ান, অডিট স্টেটমেন্ট এবং ব্যালান্স শিট রাখা হয়েছে **`Reports`** মেনুতে।

---

## ২. সাধারণ খতিয়ান ও প্রাথমিক সেটআপ

### ২.১ চার্ট অব অ্যাকাউন্টস (COA)
**লোকেশন:** `সাইডবার` ➡️ `Accounts` ➡️ `General Ledger & Setup` ➡️ `Chart of Accounts (COA)`

#### 📌 কেন ব্যবহার করবেন? (Purpose)
কোম্পানির প্রতিটি পয়সার আয়, ব্যয়, সম্পদ এবং দায়ের একটি সুনির্দিষ্ট পরিচয় বা হেড থাকতে হয়। COA হলো পুরো অ্যাকাউন্টিং সিস্টেমের মেরুদণ্ড, যা ছাড়া কোনো ভাউচার বা রিপোর্ট তৈরি সম্ভব নয়।

#### ⏰ কখন ব্যবহার করবেন? (When to Use)
* ব্যবসার শুরুতে প্রাথমিক সব খতিয়ান হেড সাজানোর সময়।
* ব্যবসা সম্প্রসারণের কারণে নতুন কোনো খরচের খাত (যেমন: "Online Marketing Expense") বা নতুন আয়ের খাত তৈরি হলে।
* মূল হেডগুলোর বর্তমান রানিং ব্যালান্স এক নজরে দেখার জন্য।

#### 🛠️ ব্যবহারের নিয়ম (How to Use):
1. **ট্রি ভিউ দেখা:** এই পেজে ঢুকলেই পুরো কোম্পানির অ্যাকাউন্টিং কাঠামো একটি গাছের মতো সাজানো দেখতে পাবেন। প্রতিটি হেড বা গ্রুপের পাশে তার রিয়েল-টাইম ব্যালান্স দেখা যাবে। কোনো গ্রুপে ক্লিক করলে তার ভেতরের সাব-অ্যাকাউন্টগুলো খুলবে।
2. **নতুন অ্যাকাউন্ট হেড তৈরি:**
   - উপরের ডানপাশে **`Add Account Head`** বাটনে ক্লিক করুন।
   - **Account Code:** চার ডিজিটের ইউনিক কোড দিন (যেমন: ১০৬০, ৫০৪০)।
   - **Account Head Name:** অ্যাকাউন্টের নাম লিখুন (যেমন: Petty Cash Vault, Utility Expense)।
   - **Classification:** এটি কোন ধরনের অ্যাকাউন্ট (Asset, Liability, Equity, Revenue, Expense) তা নির্বাচন করুন।
   - **Normal Balance:** এটি কি Debit হবে নাকি Credit হবে তা নির্বাচন করুন।
   - **Parent Group Account:** এটি যদি কোনো মূল অ্যাকাউন্টের অধীনে সাব-অ্যাকাউন্ট হয়, তবে প্যারেন্ট অ্যাকাউন্ট সিলেক্ট করুন।
   - **Is Parent Group Account:** এই অ্যাকাউন্টের আন্ডারে ভবিষ্যতে আরও সাব-অ্যাকাউন্ট করতে চাইলে টিক দিন।
   - **Create Account** বাটনে চাপুন।
3. **কোর প্রটেকশন:** মূল সিস্টেম অ্যাকাউন্ট যেমন Cash (১০১০), Bank (১০২০), Accounts Payable (২০১০) ইত্যাদির পাশে হলুদ **Core Lock** চিহ্ন থাকে, যা ভুলবশত কেউ ডিলিট করতে পারবে না।

---

### ২.২ ম্যানুয়াল জার্নাল ভাউচার (Manual JV)
**লোকেশন:** `সাইডবার` ➡️ `Accounts` ➡️ `General Ledger & Setup` ➡️ `Manual Journal Vouchers (JV)`

#### 📌 কেন ব্যবহার করবেন? (Purpose)
স্বাভাবিক সেলস ইনভয়েস বা পারচেজ বিল ছাড়া ব্যবসার এমন অনেক লেনদেন থাকে যা কোনো পণ্যের কেনাবেচার সাথে জড়িত নয়। সেগুলোর জন্য অ্যাকাউনট্যান্টকে সরাসরি ডেবিট-ক্রেডিট সমন্বয় (Adjustment) করতে এই অপশনটি ব্যবহার করতে হয়।

#### ⏰ কখন ব্যবহার করবেন? (When to Use)
* **মাস শেষের বকেয়া খরচ রেকর্ড করতে (Accruals):** যেমন মাস শেষ, কিন্তু কর্মচারীদের বেতন বা অফিস ভাড়া আগামী মাসে দেওয়া হবে।
* **অগ্রিম খরচের অংশ সমন্বয় করতে (Prepayments):** পুরো বছরের ইনস্যুরেন্স প্রিমিয়াম থেকে চলতি মাসের অংশ খরচ হিসেবে দেখাতে।
* **হিসাবের ভুল সংশোধন করতে (Correction of Errors):** ভুল অ্যাকাউন্টে টাকা ঢুকে থাকলে ডেবিট-ক্রেডিট করে তা সঠিক অ্যাকাউন্টে সরাতে।
* **ক্ষতি বা নষ্ট পণ্য রাইট-অফ করতে (Write-offs):** গুদামে পণ্য নষ্ট হলে তা ক্ষতি হিসেবে দেখিয়ে ইনভেন্টরি কমাতে।

#### 🛠️ ব্যবহারের নিয়ম (How to Use):
1. ভাউচার হিস্ট্রি পেজে গিয়ে উপরের ডানপাশে **`New Journal Voucher`** বাটনে ক্লিক করুন।
2. **Entry Date:** ভাউচারের তারিখ নির্বাচন করুন।
3. **Narration:** ভাউচারের বিস্তারিত কারণ লিখুন (যেমন: "সেপ্টেম্বর ২০২৬ মাসের অফিস ভাড়ার সমন্বয়")।
4. **ডেবিট ও ক্রেডিট লাইন বসানো:**
   - **Row 1:** GL Account নির্বাচন করুন এবং **Debit** ঘরে টাকা লিখুন।
   - **Row 2:** সংশ্লিষ্ট GL Account নির্বাচন করুন এবং **Credit** ঘরে সমপরিমাণ টাকা লিখুন।
   - আরও অ্যাকাউন্ট প্রয়োজন হলে **`Add Line`** বাটনে ক্লিক করে যত খুশি লাইন বাড়াতে পারেন।
5. **লাইভ ব্যালান্স চেকার:**
   - ফর্মের নিচে লাইভ ডেবিট ও ক্রেডিটের যোগফল দেখা যাবে।
   - ডেবিট এবং ক্রেডিট সমান না হলে নিচে লাল রঙে **"Imbalanced — Cannot Post"** দেখাবে এবং সাবমিট বাটন বন্ধ থাকবে।
   - যখন উভয় পাশ সমান হবে, সাথে সাথে সবুজ হয়ে যাবে: **"Balanced — Ready to Post"**।
6. **`Post Journal Entry to GL`** বাটনে ক্লিক করুন। ভাউচারটি ইউনিক কোড (যেমন: `MJV-202609-00001`) নিয়ে মূল লেজারে যুক্ত হবে।

---

### ২.৩ ফিসকাল ইয়ার ও পিরিয়ড লক
**লোকেশন:** `সাইডবার` ➡️ `Accounts` ➡️ `General Ledger & Setup` ➡️ `Fiscal Years & Period Lock`

#### 📌 কেন ব্যবহার করবেন? (Purpose)
কোম্পানির বার্ষিক হিসাব ক্লোজ করার পর কেউ যেন অসদুপায় অবলম্বন করে বা ভুল করে পেছনের তারিখে ব্যাকডেটেড কোনো ইনভয়েস বা ভাউচার তৈরি করে হিসাব গড়মিল করতে না পারে।

#### ⏰ কখন ব্যবহার করবেন? (When to Use)
* প্রতি বছর কোম্পানি শুরুর সময় নতুন অর্থবছর চালু করতে।
* অডিট ফার্ম বা ট্যাক্স অডিট সম্পন্ন হওয়ার পর সেই অর্থবছর বা মাসটি চিরতরে লক করে দিতে।

#### 🛠️ ব্যবহারের নিয়ম (How to Use):
1. **নতুন অর্থবছর তৈরি:** **`Create New Fiscal Year`** এ ক্লিক করে নাম (যেমন: FY 2026-2027) এবং শুরুর ও শেষের তারিখ দিয়ে সেভ করুন।
2. **পিরিয়ড হার্ড লক (Hard Lock):** অডিট বা বছর শেষ হলে সংশ্লিষ্ট অর্থবছরের পাশে থাকা **`Close & Lock Period`** বাটনে ক্লিক করুন। লক হওয়া পিরিয়ডে কেউ কোনো ভাউচার পোস্ট করতে পারবে না।
3. **প্রয়োজনে রি-ওপেন:** জরুরি কোনো সমন্বয়ের দরকার হলে অনুমোদিত অ্যাডমিন **`Reopen Period`** বাটনে ক্লিক করে সাময়িকভাবে লক খুলতে পারবেন।

---

## ৩. ব্যাংক ও নগদ ব্যবস্থাপনা

### ৩.১ ব্যাংক অ্যাকাউন্ট ও ক্যাশ ভল্ট
**লোকেশন:** `সাইডবার` ➡️ `Accounts` ➡️ `Banking & Treasury` ➡️ `Bank Accounts`

#### 📌 কেন ব্যবহার করবেন? (Purpose)
কোম্পানির মূল মূলধন ও নগদ টাকা কোন কোন ব্যাংকে ও ভল্টে কত জমা আছে তা আলাদাভাবে ট্র্যাক করতে।

#### ⏰ কখন ব্যবহার করবেন? (When to Use)
* প্রতিষ্ঠানে নতুন কোনো ব্যাংক অ্যাকাউন্ট খোলা হলে।
* সফটওয়্যার চালুর শুরুতে ব্যাংকের ওপেনিং ব্যালান্স যুক্ত করতে।
* প্রতিদিনের মোট লিকুইড ক্যাশ পরিস্থিতি পর্যবেক্ষণ করতে।

#### 🛠️ ব্যবহারের নিয়ম (How to Use):
1. **`Add Bank Account`** বাটনে ক্লিক করুন।
2. ব্যাংকের নাম (যেমন: Danske Bank, Nordea), অ্যাকাউন্ট হোল্ডার ও অ্যাকাউন্ট নম্বর লিখুন।
3. **Linked GL Account:** Chart of Accounts থেকে এর সংশ্লিষ্ট লেজার অ্যাকাউন্ট হেডটি নির্বাচন করুন (যেমন: `1020 - Cash at Bank`)।
4. ওপেনিং ব্যালান্স দিয়ে **Save Account** চাপুন।

---

### ৩.২ ব্যাংক রিকনসিলিয়েশন (হিসাব মেলানো)
**লোকেশন:** `সাইডবার` ➡️ `Accounts` ➡️ `Banking & Treasury` ➡️ `Bank Reconciliation`

#### 📌 কেন ব্যবহার করবেন? (Purpose)
সফটওয়্যারে থাকা ব্যাংকের হিসাব এবং আসল ব্যাংকের স্টেটমেন্টের ব্যালান্সের মধ্যে কোনো অমিল বা গড়মিল আছে কিনা তা যাচাই ও সমাধান করতে।

#### ⏰ কখন ব্যবহার করবেন? (When to Use)
* প্রতি মাস বা সপ্তাহ শেষে ব্যাংক থেকে অফিসিয়াল স্টেটমেন্ট পাওয়ার পর।
* অডিটের আগে ব্যাংকের হিসাব নিখুঁত করতে।

#### 🛠️ ব্যবহারের নিয়ম (How to Use):
1. উপর থেকে যে ব্যাংকের হিসাব মিলাতে চান তা সিলেক্ট করুন।
2. ব্যাংকের মূল স্টেটমেন্টের শেষ তারিখ (Statement Date) এবং শেষ ব্যালান্স (Ending Balance) ইনপুট দিন।
3. নিচে সফটওয়্যারে থাকা অনিষ্পন্ন লেনদেনের তালিকা আসবে। ব্যাংক স্টেটমেন্টের সাথে যে যে লেনদেনগুলো মিলে যাবে সেগুলোর পাশের টিক বক্সে টিক দিন।
4. ডানপাশের অডিট বক্সে সফটওয়্যারের লেজার ব্যালান্সের সাথে স্টেটমেন্ট ব্যালান্সের পার্থক্য মিলিয়ে দেখে **`Save Reconciliation`** বাটনে চাপুন।

---

### ৩.৩ পেটি ক্যাশ রেজিস্টার (অফিস খরচ)
**লোকেশন:** `সাইডবার` ➡️ `Accounts` ➡️ `Banking & Treasury` ➡️ `Petty Cash Register`

#### 📌 কেন ব্যবহার করবেন? (Purpose)
অফিসের দৈনন্দিন ছোটখাটো খুচরা খরচ যেন মূল ব্যাংকের অ্যাকাউন্টে জ্যাম তৈরি না করে এবং স্বচ্ছভাবে নগদ টাকার হিসাব রাখা যায়।

#### ⏰ কখন ব্যবহার করবেন? (When to Use)
* ব্যাংক থেকে অফিস খরচের জন্য খুচরা টাকা (ফ্লোট) তোলার সময়।
* অফিসের চা-নাস্তা, মেহমানদারি, কুরিয়ার বা ছোটখাটো স্টেশনারি কেনার সময়।

#### 🛠️ ব্যবহারের নিয়ম (How to Use):
1. **ফ্লোট টপ-আপ (Replenish Float - In):** ব্যাংক থেকে টাকা তুললে **`Replenish Float (In)`** এ ক্লিক করে টাকার পরিমাণ ও বিবরণ দিন (`DR Petty Cash / CR Bank Account`)।
2. **দৈনিক খরচ রেকর্ড (Record Expense - Out):** ক্যাশ থেকে কোনো খরচ পরিশোধ করলে **`Record Expense (Out)`** এ ক্লিক করে টাকার পরিমাণ দিন এবং খরচের হেড (যেমন: `5010 Office Expense`) সিলেক্ট করে সেভ করুন (`DR Office Expense / CR Petty Cash`)।

---

### ৩.৪ আন্তঃঅ্যাকাউন্ট ফান্ড ট্রান্সফার (কন্ট্রা ভাউচার)
**লোকেশন:** `সাইডবার` ➡️ `Accounts` ➡️ `Banking & Treasury` ➡️ `Inter-Account Transfers`

#### 📌 কেন ব্যবহার করবেন? (Purpose)
কোনো প্রকার আয় বা ব্যয় ছাড়াই যখন শুধু কোম্পানির এক ব্যাংক থেকে অন্য ব্যাংকে অথবা ব্যাংক থেকে ক্যাশ ভল্টে টাকা স্থানান্তর করা হয়, তখন কন্ট্রা ভাউচারের মাধ্যমে ব্যালান্স সমান রাখতে।

#### ⏰ কখন ব্যবহার করবেন? (When to Use)
* এক ব্যাংক অ্যাকাউন্ট থেকে অন্য ব্যাংক অ্যাকাউন্টে টাকা পাঠালে।
* ব্যাংক থেকে টাকা তুলে মূল অফিসের ক্যাশ ভল্টে জমা করলে।

#### 🛠️ ব্যবহারের নিয়ম (How to Use):
1. **`New Fund Transfer`** এ ক্লিক করুন।
2. যে ব্যাংক বা ক্যাশ থেকে টাকা কাটবে (Source) এবং যেখানে জমা হবে (Destination) তা সিলেক্ট করে টাকার পরিমাণ ও তারিখ দিয়ে সাবমিট করুন।
3. ট্রান্সফারটি অনুমোদনের জন্য অপেক্ষমাণ থাকবে। ঊর্ধ্বতন কর্মকর্তা **`Approve`** বাটনে চাপলেই সিস্টেম স্বয়ংক্রিয়ভাবে কন্ট্রা ভাউচার লেজারে পোস্টিং করে দেবে (`DR Destination Account / CR Source Account`)।

---

## ৪. কাস্টমার হিসাব ও দেনাদার (AR)

### ৪.১ সেলস ইনভয়েস
**লোকেশন:** `সাইডবার` ➡️ `Accounts` ➡️ `Accounts Receivable (Customer)` ➡️ `Sales Invoices`
* **কেন ও কখন ব্যবহার করবেন:** পণ্য বিক্রি কনফার্ম হওয়ার পর কাস্টমারকে অফিসিয়াল চালান বা বিল দেওয়ার জন্য। ইনভয়েস জেনারেট হওয়ার সাথে সাথে স্বয়ংক্রিয়ভাবে কাস্টমারের নামে দেনা তৈরি হয় (`DR Accounts Receivable / CR Sales Revenue`)।

---

### ৪.২ টাকা গ্রহণ ও রিসিট (Receive Payment & Receipts)
**লোকেশন:** `সাইডবার` ➡️ `Accounts` ➡️ `Accounts Receivable (Customer)` ➡️ `Receive Payment` & `Payment Receipts`
* **কেন ও কখন ব্যবহার করবেন:** কাস্টমার যখন তার বকেয়া বিল ক্যাশ, ব্যাংক বা চেকের মাধ্যমে পরিশোধ করে।
* **ব্যবহারের নিয়ম:** অর্ডার বা কাস্টমার নির্বাচন করে সংগৃহীত টাকার পরিমাণ ও পেমেন্ট মেথড দিয়ে সাবমিট করুন। সাথে সাথে প্রিন্টযোগ্য রিসিট তৈরি হয়ে যাবে যা কাস্টমারকে দেওয়া যাবে।

---

### ৪.৩ কাস্টমারদের বকেয়া অর্ডার (Customer Due Orders)
**লোকেশন:** `সাইডবার` ➡️ `Accounts` ➡️ `Accounts Receivable (Customer)` ➡️ `Customer Due Orders`
* **কেন ও কখন ব্যবহার করবেন:** কোন কোন গ্রাহকের কাছে টাকা আটকে আছে তা এক নজরে দেখে দ্রুত তাগাদা দেওয়ার জন্য।
* **ব্যবহারের নিয়ম:** তালিকা থেকে সরাসরি নির্দিষ্ট অর্ডারের পাশে থাকা **`Pay Now`** বাটনে ক্লিক করে টাকা জমা রেকর্ড করা যায়।

---

## ৫. সরবরাহকারী হিসাব ও পাওনাদার (AP)

### ৫.১ ভেন্ডর বিল ও পেমেন্ট ভাউচার
**লোকেশন:** `সাইডবার` ➡️ `Accounts` ➡️ `Accounts Payable (Vendor)` ➡️ `Vendor Bills` & `Payment Vouchers`
* **Vendor Bills (কেন ও কখন):** সরবরাহকারীর কাছ থেকে মালামাল রিসিভ করার পর তাদের পাঠানো বিলটি সফটওয়্যারে বুক করতে, যাতে কোম্পানি জানতে পারে কার কাছে কত দায় রয়েছে।
* **Payment Vouchers (কেন ও কখন):** ভেন্ডরের বিল পরিশোধের সময় চেক নম্বর, ব্যাংক অ্যাকাউন্ট বা ট্রানজেকশন আইডি সহ অফিসিয়াল পেমেন্ট ভাউচার ইস্যু করতে।

---

### ৫.২ ভেন্ডর বকেয়া ও এজিন্গ বিশ্লেষণ
**লোকেশন:** `সাইডবার` ➡️ `Accounts` ➡️ `Accounts Payable (Vendor)` ➡️ `Vendor Due Purchases`

#### 📌 কেন ব্যবহার করবেন? (Purpose)
কোম্পানির নগদ প্রবাহ (Cash Flow) ঠিক রাখতে কোন সরবরাহকারীর টাকা আগে পরিশোধ করতে হবে তা নির্ধারণের জন্য।

#### ⏰ কখন ব্যবহার করবেন? (When to Use)
* প্রতি সপ্তাহে বা মাসে ভেন্ডরদের পেমেন্ট শিডিউল তৈরির সময়।
* কোন বিলের মেয়াদ কতদিন পার হয়েছে তা অডিট করার সময়।

#### 🛠️ ব্যবহারের নিয়ম (How to Use):
এই পেজের উপরে ৪টি রঙের বিশেষ বিশ্লেষণ কার্ড রয়েছে:
* 🟢 **`0–30 Days` (সবুজ):** চলতি বকেয়া (ক্রেডিট সীমার মধ্যে আছে)।
* 🟡 **`31–60 Days` (হলুদ):** এক মাস পার হওয়া বিল, পেমেন্ট প্ল্যান করা দরকার।
* 🔴 **`61–90 Days` (লাল):** দুই মাস পার হওয়া ঝুঁকিপূর্ণ বকেয়া।
* 🟤 **`90+ Days` (গাঢ় খয়েরি):** তিন মাসের বেশি পুরোনো অতি-জরুরি বকেয়া।

টেবিলের ডানপাশে **`Pay Now`** বাটনে ক্লিক করে সাথে সাথে ওই ভেন্ডরের বকেয়া পরিশোধ রেকর্ড করা যায়।

---

## ৬. স্থায়ী সম্পদ ও অবচয় ইঞ্জিন

**লোকেশন:** `সাইডবার` ➡️ `Accounts` ➡️ `Fixed Assets & Depreciation` ➡️ `Fixed Assets Register`

#### 📌 কেন ব্যবহার করবেন? (Purpose)
কোম্পানির গাড়ি, মেশিন, কম্পিউটার ইত্যাদি সম্পদ ব্যবহারের ফলে তার মান প্রতি মাসে কমে যায়। আন্তর্জাতিক অ্যাকাউন্টিং স্ট্যান্ডার্ড (IAS-16) অনুযায়ী এই মূল্যক্ষয়কে কোম্পানির খরচ হিসেবে হিসাবভুক্ত করতে হয়।

#### ⏰ কখন ব্যবহার করবেন? (When to Use)
* নতুন কোনো স্থায়ী সম্পদ কিনলে (Asset Register)।
* প্রতি মাসের শেষ দিনে সব সম্পদের মাসিক অবচয় এক ক্লিকে লেজারে কাটতে (Run Depreciation)।

#### 🛠️ ব্যবহারের নিয়ম (How to Use):
1. **নতুন সম্পদ নিবন্ধন:** **`Register New Asset`** এ ক্লিক করে নাম, ক্যাটাগরি, কেনার তারিখ, মূল্য, মেয়াদ (যেমন: ৫ বছর) এবং পদ্ধতি (Straight Line বা Reducing Balance) দিয়ে সেভ করুন।
2. **মাসিক অবচয় চালানো:** মাস শেষে **`⚡ Run Monthly Depreciation`** এ ক্লিক করে মাস সিলেক্ট করে সাবমিট করুন। সফটওয়্যার সাথে সাথে ডাবল-এন্ট্রি জার্নাল পোস্ট করে দেবে (`DR Depreciation Expense / CR Accumulated Depreciation`) এবং সম্পদের বর্তমান বুক ভ্যালু আপডেট করে দেবে।

---

## ৭. আর্থিক প্রতিবেদন ও অডিট

**লোকেশন:** `সাইডবার` ➡️ `Reports`

#### 📌 কেন ব্যবহার করবেন? (Purpose)
কোম্পানির আসল আর্থিক স্বাস্থ্য দেখতে, লাভ বা ক্ষতি জানতে এবং ব্যাংক বা ট্যাক্স অডিটরদের কাছে অফিসিয়াল রিপোর্ট পেশ করতে।

#### ⏰ কখন ব্যবহার করবেন? (When to Use)
* প্রতিদিনের ক্যাশ ও ব্যাংক মুভমেন্ট দেখতে (General Ledger)।
* মাস শেষে ডেবিট ও ক্রেডিটের ভারসাম্য চেক করতে (Trial Balance)।
* মাস বা বছর শেষে কোম্পানির মোট লাভ বা ক্ষতি জানতে (Profit & Loss)।
* বছর শেষে কোম্পানির মোট আর্থিক স্থিতি যাচাই করতে (Balance Sheet)।

#### 📊 মূল প্রতিবেদনসমূহের তালিকা:
* **`General Ledger`:** অ্যাকাউন্ট ও তারিখ নির্বাচন করে সম্পূর্ণ ডেবিট-ক্রেডিট খতিয়ান দেখা।
* **`Trial Balance`:** সমস্ত অ্যাকাউন্টের ব্যালান্স নিয়ে মোট ডেবিট = মোট ক্রেডিট মেলানো।
* **`Profit & Loss (P&L)`:** মোট রাজস্ব থেকে মোট ব্যয় বাদ দিয়ে নেট মুনাফা/ক্ষতি বের করা।
* **`Balance Sheet`:** `মোট সম্পদ = মোট দায় + মোট ইকুইটি`।
* **`Customer Transaction Ledger`:** সমস্ত কাস্টমারদের লেনদেন লগ ও পিডিএফ।
* **`AR Customer Aging`:** কাস্টমারদের বকেয়ার বয়স অনুযায়ী কালেকশন রিপোর্ট।
* **`Vendor Payment Ledger`:** ভেন্ডরদের দেওয়া সমস্ত পেমেন্টের ভাউচার হিস্ট্রি ও পিডিএফ।
* **`AP Vendor Aging`:** ভেন্ডরদের বকেয়ার বয়স অনুযায়ী দায় অডিট।
* **`Supplier Ledger & Statement`:** যে কোনো নির্দিষ্ট ভেন্ডরের একক খতিয়ান স্টেটমেন্ট।

---

## ৮. সাধারণ জিজ্ঞাসা ও সমাধান

* **প্রশ্ন: ভাউচার সেভ করার সময় "Journal Entry Imbalance Exception" দেখালে কী করব?**  
  **উত্তর:** ডাবল এন্ট্রির নিয়ম অনুযায়ী আপনার ডেবিট এবং ক্রেডিটের যোগফল সমান হয়নি। উভয় পাশের টাকার অংক সমান করে আবার সাবমিট করুন।

* **প্রশ্ন: পেছনের তারিখ দিয়ে ভাউচার পোস্ট করতে গেলে আটকে দিচ্ছে কেন?**  
  **উত্তর:** যে তারিখে এন্ট্রি দিতে চাচ্ছেন সেই অর্থবছরটি বা মাসটি লক করা রয়েছে। `Fiscal Years & Period Lock` পেজে গিয়ে অর্থবছরটি সাময়িকভাবে আনলক (Reopen) করুন।

* **প্রশ্ন: ক্যাশ বা ব্যাংক অ্যাকাউন্টের পাশে ডিলিট বাটন নেই কেন?**  
  **উত্তর:** সিস্টেমের মূল অ্যাকাউন্ট হেডগুলো (Core Protected) ডিলিট করা সফটওয়্যার থেকে স্থায়ীভাবে বন্ধ রাখা হয়েছে, যাতে কেউ ভুলবশত কোম্পানির মূল লেজার ধ্বংস করতে না পারে।

---
---

# ========================================================
# 🇬🇧 PART 2: ENGLISH EDITION (USER MANUAL)
# ========================================================

## 📑 Table of Contents (English)
1. [Overview & Architecture](#1-overview--architecture)
2. [General Ledger & Setup](#2-general-ledger--setup)
   - 2.1 [Chart of Accounts (COA)](#21-chart-of-accounts-coa)
   - 2.2 [Manual Journal Vouchers (MJV)](#22-manual-journal-vouchers-mjv)
   - 2.3 [Fiscal Years & Period Lock](#23-fiscal-years--period-lock)
3. [Banking & Treasury](#3-banking--treasury)
   - 3.1 [Bank Accounts & Cash Vaults](#31-bank-accounts--cash-vaults)
   - 3.2 [Bank Reconciliation](#32-bank-reconciliation)
   - 3.3 [Petty Cash Register](#33-petty-cash-register)
   - 3.4 [Inter-Account Fund Transfers (Contra)](#34-inter-account-fund-transfers-contra)
4. [Accounts Receivable (Customer AR)](#4-accounts-receivable-customer-ar)
   - 4.1 [Sales Invoices](#41-sales-invoices)
   - 4.2 [Receive Payment & Receipts](#42-receive-payment--receipts)
   - 4.3 [Customer Due Orders](#43-customer-due-orders)
5. [Accounts Payable (Vendor AP)](#5-accounts-payable-vendor-ap)
   - 5.1 [Vendor Bills & Payment Vouchers](#51-vendor-bills--payment-vouchers)
   - 5.2 [Vendor Due Purchases & AP Aging](#52-vendor-due-purchases--ap-aging)
6. [Fixed Assets & Depreciation Engine](#6-fixed-assets--depreciation-engine)
7. [Financial Statements & Reports](#7-financial-statements--reports)
8. [Auditing & Troubleshooting FAQs](#8-auditing--troubleshooting-faqs)

---

## 1. Overview & Architecture

The B2B Viking ERP Phase 5 Accounts module is designed to meet strict **GAAP / IFRS-compliant double-entry accounting standards**.

* **Zero-Imbalance Invariant:** Every debit entered into the system must strictly equal every credit. The central accounting core enforces a mathematical invariant: any transaction with a variance exceeding 0.001 throws an immediate exception and rolls back the database transaction.
* **Standard 5-Tier Chart of Accounts (COA):**
  - **1000 Class:** Assets (Cash, Bank, Accounts Receivable, Inventory, Plant & Equipment)
  - **2000 Class:** Liabilities (Accounts Payable, Accrued Expenses, Short/Long-Term Loans)
  - **3000 Class:** Equity (Share Capital, Retained Earnings, Owner's Reserves)
  - **4000 Class:** Revenue (Gross Sales, Wholesale Income, Service Income)
  - **5000 Class:** Expenses (Cost of Goods Sold, Operational Costs, Depreciation)
* **Separation of Concerns:** All daily operational tasks (invoicing, payments, transfers) are located under the **`Accounts`** menu, while audit statements, trial balance, and analytical ledgers are cleanly centralized in the **`Reports`** menu.

---

## 2. General Ledger & Setup

### 2.1 Chart of Accounts (COA)
**Navigation:** `Sidebar` ➡️ `Accounts` ➡️ `General Ledger & Setup` ➡️ `Chart of Accounts (COA)`

#### 📌 Purpose (Why Use This):
The Chart of Accounts organizes all company assets, liabilities, equity, revenues, and expenses into a systematic tree. It is the architectural backbone of the ERP; every voucher, invoice, and report depends on it.

#### ⏰ When to Use:
* Initial ERP onboarding to structure company ledger categories.
* Introducing new expenditure heads (e.g. "Software Subscriptions", "Logistics Fuel").
* Monitoring live account balances across all classifications.

#### 🛠️ How to Use:
1. **Interactive Tree View:** The primary tab presents an interactive tree view illustrating parent-child group accounts with live running balances in DKK. Click any group header to expand or collapse branches.
2. **Creating a New Account Head:**
   - Click the **`Add Account Head`** button.
   - Enter a unique **Account Code** (e.g. `1060`, `5040`) and descriptive **Account Head Name**.
   - Select the **Classification** (Asset, Liability, Equity, Revenue, Expense) and define its **Normal Balance** (Debit or Credit).
   - If nesting under an existing group head, select the **Parent Group Account**. Check **Is Parent Group Account?** if this head will have sub-accounts beneath it.
   - Click **Create Account**.
3. **Core Protection:** Core accounts (`1010 Cash`, `1020 Bank`, `2010 AP`, `1030 AR`, etc.) are protected with a lock icon and cannot be deleted by users to prevent ledger corruption.

---

### 2.2 Manual Journal Vouchers (MJV)
**Navigation:** `Sidebar` ➡️ `Accounts` ➡️ `General Ledger & Setup` ➡️ `Manual Journal Vouchers (JV)`

#### 📌 Purpose (Why Use This):
For non-invoiced financial events where accountants must directly adjust General Ledger balances with balanced debits and credits.

#### ⏰ When to Use:
* **Month-End Accruals:** Booking accrued unpaid salaries or utilities before cash payout.
* **Prepayment Amortization:** Expensing the monthly portion of annual prepaid rent or insurance.
* **Error Corrections:** Moving incorrectly booked expenses to the correct GL account without touching bank balances.
* **Write-Offs & Bad Debts:** Writing off damaged warehouse goods or uncollectible receivables.

#### 🛠️ How to Use:
1. Click **`New Journal Voucher`**.
2. Select **Entry Date** and input a clear, descriptive **Narration**.
3. **Debit & Credit Lines:**
   - **Row 1:** Select GL Account to debit, enter amount in **Debit**.
   - **Row 2:** Select offset GL Account to credit, enter amount in **Credit**.
   - Click **`Add Line`** to split multi-account compound entries.
4. **Live Balance Checker:**
   - The real-time checker continuously audits lines. If `Total Debit ≠ Total Credit`, it highlights red (**"Imbalanced — Cannot Post"**) and disables submission.
   - Once balanced, it highlights green (**"Balanced — Ready to Post"**).
5. Click **`Post Journal Entry to GL`**. The entry posts instantly with a sequential identifier (e.g. `MJV-202609-00001`).

---

### 2.3 Fiscal Years & Period Lock
**Navigation:** `Sidebar` ➡️ `Accounts` ➡️ `General Ledger & Setup` ➡️ `Fiscal Years & Period Lock`

#### 📌 Purpose (Why Use This):
To maintain auditing integrity and prevent fraudulent or accidental backdated entries into previously audited financial periods.

#### ⏰ When to Use:
* At the beginning of each business year to initialize operational fiscal dates.
* At the end of each month or annual statutory audit to lock the books.

#### 🛠️ How to Use:
1. **Define Fiscal Year:** Click **`Create New Fiscal Year`**, enter a Title (e.g., `FY 2026-2027`), specify Start and End dates, and save.
2. **Hard Period Lock:** Click **`Close & Lock Period`**. The core accounting engine blocks any user from posting backdated invoices, vouchers, or manual journal entries into that locked date range.
3. **Reopening Periods:** Authorized administrators can click **`Reopen Period`** if adjusting entries are officially sanctioned.

---

## 3. Banking & Treasury

### 3.1 Bank Accounts & Cash Vaults
**Navigation:** `Sidebar` ➡️ `Accounts` ➡️ `Banking & Treasury` ➡️ `Bank Accounts`

#### 📌 Purpose (Why Use This):
To segregate and monitor balances across distinct institutional bank accounts and office vaults in real-time.

#### ⏰ When to Use:
* Opening a new corporate bank account.
* Initial balance migration during ERP onboarding.
* Reviewing total live liquid treasury.

#### 🛠️ How to Use:
1. Click **`Add Bank Account`**, enter Bank Name (e.g., Danske Bank, Nordea), Account Holder, and IBAN/Account Number.
2. Link its corresponding General Ledger Head (e.g., `1020 - Cash at Bank`).
3. Set opening balance and save.

---

### 3.2 Bank Reconciliation
**Navigation:** `Sidebar` ➡️ `Accounts` ➡️ `Banking & Treasury` ➡️ `Bank Reconciliation`

#### 📌 Purpose (Why Use This):
To verify that software ledger records strictly match real-world bank statements and detect discrepancies or unrecorded charges.

#### ⏰ When to Use:
* Weekly or monthly upon receiving official bank statements.
* Prior to financial closing.

#### 🛠️ How to Use:
1. Select target bank account.
2. Input official statement date and ending statement balance.
3. Audit unreconciled items in the table. Check the box for each transaction appearing on your bank statement.
4. Verify variance on the right audit panel and click **`Save Reconciliation`**.

---

### 3.3 Petty Cash Register
**Navigation:** `Sidebar` ➡️ `Accounts` ➡️ `Banking & Treasury` ➡️ `Petty Cash Register`

#### 📌 Purpose (Why Use This):
To manage minor, day-to-day office cash expenditures without cluttering the corporate bank account.

#### ⏰ When to Use:
* Withdrawing cash from the bank to top up office cash float.
* Paying small daily expenses (refreshments, courier, emergency cleaning supplies).

#### 🛠️ How to Use:
1. **Float Replenishment (In):** Click **`Replenish Float (In)`**, enter amount and memo (`DR 1010 Petty Cash / CR 1020 Bank Account`).
2. **Minor Expense Recording (Out):** Click **`Record Expense (Out)`**, enter amount, choose expense account (`5010 Office Expense`), and save (`DR 5010 Office Expense / CR 1010 Petty Cash`).

---

### 3.4 Inter-Account Fund Transfers (Contra)
**Navigation:** `Sidebar` ➡️ `Accounts` ➡️ `Banking & Treasury` ➡️ `Inter-Account Transfers`

#### 📌 Purpose (Why Use This):
To transfer funds between two internal accounts (Bank-to-Bank, Bank-to-Cash) without creating artificial revenue or expense.

#### ⏰ When to Use:
* Moving operating capital between branch bank accounts.
* Transferring money from bank to physical office vaults.

#### 🛠️ How to Use:
1. Click **`New Fund Transfer`**, specify Source Account (Debit), Destination Account (Credit), and Amount.
2. Submit for approval. An authorized supervisor clicks **`Approve`** to post the balanced Contra Journal: `DR Destination Vault / CR Source Vault`.

---

## 4. Accounts Receivable (Customer AR)

### 4.1 Sales Invoices
**Navigation:** `Sidebar` ➡️ `Accounts` ➡️ `Accounts Receivable (Customer)` ➡️ `Sales Invoices`
* **Purpose & When to Use:** Generated upon order confirmation to bill customers and establish receivable assets (`DR Accounts Receivable / CR Sales Revenue & Output VAT`).

---

### 4.2 Receive Payment & Receipts
**Navigation:** `Sidebar` ➡️ `Accounts` ➡️ `Accounts Receivable (Customer)` ➡️ `Receive Payment` & `Payment Receipts`
* **Purpose & When to Use:** When customers remit funds via Cash, Bank Transfer, or Mobile Pay. Automatically settles customer dues and generates printable payment receipts.

---

### 4.3 Customer Due Orders
**Navigation:** `Sidebar` ➡️ `Accounts` ➡️ `Accounts Receivable (Customer)` ➡️ `Customer Due Orders`
* **Purpose & When to Use:** Live operational register of all customer orders carrying overdue or pending balances. Use the **`Pay Now`** button directly on the row to collect payment instantly.

---

## 5. Accounts Payable (Vendor AP)

### 5.1 Vendor Bills & Payment Vouchers
**Navigation:** `Sidebar` ➡️ `Accounts` ➡️ `Accounts Payable (Vendor)` ➡️ `Vendor Bills` & `Payment Vouchers`
* **Vendor Bills:** Book supplier invoices against goods receipts to record trade liabilities.
* **Payment Vouchers:** Record outflows disbursed to suppliers, specify cheque numbers or transaction IDs, deduct the bank vault, and print payment vouchers.

---

### 5.2 Vendor Due Purchases & AP Aging
**Navigation:** `Sidebar` ➡️ `Accounts` ➡️ `Accounts Payable (Vendor)` ➡️ `Vendor Due Purchases`

#### 📌 Purpose (Why Use This):
To prioritize supplier payments, optimize working capital, and maintain strong commercial credit ratings.

#### ⏰ When to Use:
* Weekly cash flow planning.
* Before executing vendor disbursement runs.

#### 🛠️ How to Use:
Review the 4 color-coded AP Aging analysis cards:
* 🟢 **`0–30 Days` (Green):** Current payable liabilities within typical commercial terms.
* 🟡 **`31–60 Days` (Yellow):** Maturing obligations requiring cash allocation.
* 🔴 **`61–90 Days` (Red):** Urgent dues past maturity.
* 🟤 **`90+ Days` (Dark Red):** High-risk overdue liabilities.

Click **`Pay Now`** on any row to immediately clear outstanding vendor dues.

---

## 6. Fixed Assets & Depreciation Engine

**Navigation:** `Sidebar` ➡️ `Accounts` ➡️ `Fixed Assets & Depreciation` ➡️ `Fixed Assets Register`

#### 📌 Purpose (Why Use This):
Tangible assets (vehicles, machinery, IT hardware) decline in value over time. Under IAS-16, this depreciation must be recognized as an operational expense.

#### ⏰ When to Use:
* Upon acquiring new capital assets.
* On the final day of each accounting month to execute automated depreciation.

#### 🛠️ How to Use:
1. **Register Asset:** Click **`Register New Asset`**, input Asset Title, Category, Acquisition Date, Cost, Useful Life (years), and method (Straight-Line SLM or Reducing Balance WDV).
2. **Run Monthly Depreciation:** Click **`⚡ Run Monthly Depreciation`**, select the target month (e.g. `2026-09`), and confirm. The system computes the monthly amortization across all active assets and posts a balanced entry (`DR 5030 Depreciation Expense / CR 1080 Accumulated Depreciation`), updating Net Book Value continuously.

---

## 7. Financial Statements & Reports

**Navigation:** `Sidebar` ➡️ `Reports`

#### 📌 Purpose (Why Use This):
To provide executive leadership, shareholders, and auditors with accurate insights into profitability, liquidity, and financial stability.

#### ⏰ When to Use:
* Daily cash auditing (General Ledger).
* Month-end debit/credit verification (Trial Balance).
* Periodic performance review (Profit & Loss).
* Annual corporate solvency review (Balance Sheet).

#### 📊 Statement Catalog:
* **`General Ledger`:** Filterable comprehensive account ledger displaying historical debits, credits, and running balances.
* **`Trial Balance`:** Single-query aggregate verifying total debits equal total credits across all accounts.
* **`Profit & Loss (P&L)`:** Income statement computing net profit or loss by comparing revenues against expenses.
* **`Balance Sheet`:** Fundamental corporate health report: `Assets = Liabilities + Equity`.
* **`Customer Transaction Ledger`:** Audit log of all customer receipts with PDF download.
* **`AR Customer Aging`:** Customer receivables categorized by age for credit control.
* **`Vendor Payment Ledger`:** Disbursement log of all payments made to suppliers with PDF export.
* **`AP Vendor Aging`:** Payable liabilities categorized by age to assist treasury planning.
* **`Supplier Ledger & Statement`:** Vendor-specific debit/credit statement of accounts.

---

## 8. Auditing & Troubleshooting FAQs

* **Q: Why does the system throw a "Journal Entry Imbalance Exception"?**  
  **A:** In compliance with double-entry accounting rules, total debits must equal total credits. Adjust your debit and credit figures until the imbalance reaches zero.

* **Q: Why is backdated posting blocked for a specific date?**  
  **A:** The accounting period for that date has been locked in `Fiscal Years & Period Lock`. An authorized administrator must reopen the period before posting.

* **Q: Why can't I find a delete button for Cash or Bank accounts?**  
  **A:** Essential system accounts (`1010 Cash`, `1020 Bank`, `2010 AP`, `1030 AR`, `3010 Equity`) are **Core Protected** to prevent irreversible corruption of the general ledger.

---
*B2B Viking ERP — Enterprise Accounting Engine Documentation*
