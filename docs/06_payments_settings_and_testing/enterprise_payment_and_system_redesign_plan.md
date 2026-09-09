# Enterprise Payment Settings, Frontend Checkout, & System Controls Redesign Plan

**File Name:** `docs/06_payments_settings_and_testing/enterprise_payment_and_system_redesign_plan.md`  
**Author:** AI Pair Programmer & System Architect  
**Date:** 2026-09-08  
**Status:** DRAFT (Awaiting User Review & Approval)

---

## ১. সারসংক্ষেপ ও ইউজারের পর্যবেক্ষণ (Feedback Summary)

১. **পেমেন্ট সেটিংস UI রিডিজাইন:**
   - সেটিংস এবং কালেকশন একই পেজে গুলিয়ে ফেলা যাবে না।
   - ব্যবহারকারীর রেফারেন্স ইমেজ অনুযায়ী `/admin/payment-settings` রাউটে বাম পাশে ভার্টিক্যাল ট্যাব (`Paypal`, `Payoneer`, `Mobile Pay`, `COD`) এবং ডান পাশে এন্টারপ্রাইজ ইনপুট কার্ড তৈরি করতে হবে।
২. **শেয়ার্ড হোস্টিং লেবেলিং রিমুভাল ও VPS পোর্টেবিলিটি:**
   - UI থেকে "Shared Hosting" বা পরিবেশ সম্পর্কিত সব ধরনের টেক্সট ও ব্যাজ বাদ দেওয়া।
   - ভবিষ্যতে Namecheap থেকে যেকোনো লিনাক্স VPS (Ubuntu, Debian, Nginx/Apache) বা ক্লাউডে মাইগ্রেট করলেও আমাদের পিওর REST v2 API কোড ১০০% সাপোর্ট করবে।
৩. **ফ্রন্টএন্ড চেকআউট পেজে পেমেন্ট গেটওয়ে সিলেকশন:**
   - `resources/views/frontend/pages/checkout.blade.php`-তে ডায়নামিক পেমেন্ট মেথড (PayPal ও COD) রেডিও অপশন এবং লোগো সহ কার্ড যুক্ত করা।
   - পেমেন্ট সিলেক্ট করে অর্ডার প্লেস করলে যথাক্রমে ক্যাশ অন ডেলিভারি অথবা পেপাল গেটওয়েতে রিডাইরেক্ট করা।
৪. **Maintenance & Cache Purge মডিউল রিমুভ:**
   - `MaintenanceController`, `clear_cache.blade.php`, রাউট এবং মেনু থেকে পুরোপুরি মুছে ফেলা।
৫. **Universal Recycle Bin ও Backups-এ Yajra DataTables:**
   - প্রজেক্টের অন্য ৫৯টি মডিউলের মতো রিসাইকেল বিন এবং ডাটাবেজ ব্যাকআপের জন্য সার্ভার-সাইড `Yajra DataTables` ইন্টিগ্রেট করা।
৬. **ডেডিকেটেড ডাটাবেজ মাইগ্রেশন (`payment_settings`):**
   - `general_settings` টেবিল থেকে পেমেন্ট ফিল্ডগুলো সরিয়ে আলাদা ডেডিকেটেড `payment_settings` টেবিল ও মডেল তৈরি করা।

---

## ২. আর্কিটেকচারাল ব্লুপ্রিন্ট (Architectural Blueprint)

### Pillar 1: ডেডিকেটেড ডাটাবেজ স্কিমা (`payment_settings`)

`general_settings` টেবিল যাতে পরিচ্ছন্ন থাকে, সেজন্য পেমেন্ট কনফিগারেশনের জন্য আলাদা টেবিল তৈরি হবে:

```sql
CREATE TABLE `payment_settings` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(50) UNIQUE NOT NULL,             -- 'paypal', 'cod', 'payoneer', 'mobile_pay'
    `name` VARCHAR(100) NOT NULL,                  -- 'PayPal Express', 'Cash On Delivery'
    `status` ENUM('enable', 'disable') DEFAULT 'disable',
    `mode` ENUM('sandbox', 'live') DEFAULT 'sandbox',
    `country_name` VARCHAR(100) NULL,              -- 'United States', 'Denmark'
    `currency_name` VARCHAR(100) NULL,             -- 'USD', 'DKK', 'EUR'
    `currency_rate` DECIMAL(12, 4) DEFAULT 1.0000, -- Rate per DKK
    `client_id` TEXT NULL,                         -- PayPal Client ID
    `client_secret` TEXT NULL,                     -- PayPal Secret
    `max_limit` DECIMAL(12, 2) NULL,               -- COD Maximum limit
    `instructions` TEXT NULL,                      -- Delivery / Wire instructions
    `deposit_account_id` BIGINT UNSIGNED NULL,     -- COA Head (e.g. 1010 Petty Cash)
    `additional_config` JSON NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    FOREIGN KEY (`deposit_account_id`) REFERENCES `chart_of_accounts`(`id`) ON DELETE SET NULL
);
```

---

### Pillar 2: অ্যাডমিন পেমেন্ট সেটিংস UI (User Screenshot হুবহু অনুকরণ)

- **URL:** `http://127.0.0.1:8000/admin/payment-settings`
- **মেনু অবস্থান:** সাইডবারে `Settings` ড্রপডাউনের ভেতরে `Payment Settings`।
- **লেআউট ও স্টাইলিং:**
  - **হেডার:** `Settings`
  - **বাম পাশের ভার্টিক্যাল ট্যাব তালিকা:**
    1. **Paypal** (ডিফল্ট অ্যাক্টিভ ব্লু ট্যাব)
    2. **Payoneer** (ট্যাব)
    3. **Mobile Pay** (ট্যাব)
    4. **COD** (ট্যাব)
  - **PayPal ট্যাব কার্ড (ডান পাশে):**
    - Row 1: `Paypal Status` (Enable / Disable) | `Account Mode` (Sandbox / Live)
    - Row 2: `Country Name` (Dropdown) | `Currency Name` (Dropdown)
    - Row 3: `Paypal Client Id` (Full width input)
    - Row 4: `Paypal Secret Key` (Full/half input) | `Currency Rate (Per DKK)` (Numeric input)
    - Action: `Update` (Primary Blue Button)
  - **COD ট্যাব কার্ড:**
    - Row 1: `COD Status` (Enable / Disable) | `Country Name`
    - Row 2: `Currency Name` | `Currency Rate (Per DKK)`
    - Row 3: `Max Order Limit (DKK)` | `Deposit Account Head` (Chart of Accounts: 1010 Cash in Hand)
    - Row 4: `Delivery Instructions` (Textarea)
    - Action: `Update` (Primary Blue Button)
  - **Payoneer & Mobile Pay ট্যাব:**
    - একই মার্জিত লেআউট সহ তৈরি থাকবে (ভবিষ্যতে API কি বসালেই যাতে কাজ করে)।

---

### Pillar 3: অপারেশনাল কালেকশনের পৃথকীকরণ (Separation of Concerns)

- **Payment Settings:** শুধুমাত্র সিস্টেম কনফিগারেশন (`Settings -> Payment Settings`)।
- **COD Collections:** ফিল্ড ড্রাইভার কালেকশন ও ক্যাশিয়ার হ্যান্ডওভার (`Sales -> COD Collections` অথবা `Finance -> COD Collections`) এ থাকবে। সেটিংসের ভেতরে কোনো অপারেশনাল ডাটা থাকবে না।
- **Payment Transactions:** সেন্ট্রাল অডিট ট্রেইল হিসেবে `Finance -> Payment Transactions`-এ আলাদা থাকবে।

---

### Pillar 4: ফ্রন্টএন্ড চেকআউট পেজে পেমেন্ট মেথড ইন্টিগ্রেশন

- **ফাইল:** `resources/views/frontend/pages/checkout.blade.php`
- **অর্ডার সামারির উপরে পেমেন্ট মেথড রেডিও কার্ড যুক্ত হবে:**
  ```html
  <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <h3 class="text-base font-bold text-slate-800 mb-4">Select Payment Method</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- COD Card (যদি PaymentSetting এ Enable থাকে) -->
          <label class="cursor-pointer border-2 rounded-xl p-4 flex items-center gap-3 transition-all hover:border-indigo-500">
              <input type="radio" name="payment_method" value="cod" checked class="h-4 w-4 text-indigo-600" />
              <div>
                  <span class="font-bold text-slate-800 block">Cash On Delivery (COD)</span>
                  <span class="text-xs text-slate-500">Pay physically upon delivery</span>
              </div>
          </label>

          <!-- PayPal Card (যদি PaymentSetting এ Enable থাকে) -->
          <label class="cursor-pointer border-2 rounded-xl p-4 flex items-center gap-3 transition-all hover:border-indigo-500">
              <input type="radio" name="payment_method" value="paypal" class="h-4 w-4 text-indigo-600" />
              <div>
                  <span class="font-bold text-slate-800 block">PayPal Express</span>
                  <span class="text-xs text-slate-500">Pay securely via PayPal</span>
              </div>
          </label>
      </div>
  </div>
  ```
- **`CartController@placeOrder` লজিক:**
  - ভ্যালিডেশন: `'payment_method' => 'required|in:cod,paypal'`.
  - যদি `cod` হয়:
    - অর্ডার তৈরি হবে (`payment_method = 'cod'`, `payment_status = 'pending'`).
    - `CodCollection` রেকর্ড যুক্ত হবে।
    - অর্ডার কনফার্মেশন পেজে রিডাইরেক্ট হবে।
  - যদি `paypal` হয়:
    - অর্ডার তৈরি হবে (`payment_method = 'paypal'`, `payment_status = 'pending'`).
    - `PayPalService` দিয়ে পেপাল অর্ডার তৈরি করে সরাসরি পেপাল চেকআউট স্ক্রিনে রিডাইরেক্ট করবে।
    - পেপালে টাকা পেমেন্ট করার পর গ্রাহক সিস্টেমে ফিরে আসলে রিটার্ন ইউআরএলে পেমেন্ট ইনস্ট্যান্ট ক্যাপচার হবে, ইনভয়েস `paid` হবে এবং স্বয়ংক্রিয় জিএল জার্নাল পোস্ট হবে।

---

### Pillar 5: Maintenance & Cache Purge মডিউল সম্পূর্ণ বাদ দেওয়া

- `app/Http/Controllers/Backend/MaintenanceController.php` সম্পূর্ণ মুছে ফেলা হবে।
- `resources/views/backend/maintenance/clear_cache.blade.php` মুছে ফেলা হবে।
- `routes/web.php` থেকে সব ক্যাশ ক্লিয়ার রাউট ডিলিট হবে।
- সাইডবার মেনু থেকে "Maintenance & Cache" অপশন সম্পূর্ণ বাদ পড়বে।

---

### Pillar 6: Universal Recycle Bin ও Backups-এ Yajra DataTables ইন্টিগ্রেশন

প্রজেক্টের অন্য ৫৯টি টেবিলের মতো `app/DataTables/` ডিরেক্টরিতে ডেডিকেটেড ক্লাস তৈরি হবে:
1. `app/DataTables/RecycleBin/RecycleBinProductDataTable.php`
2. `app/DataTables/RecycleBin/RecycleBinOrderDataTable.php`
3. `app/DataTables/RecycleBin/RecycleBinUserDataTable.php`
4. `app/DataTables/RecycleBin/RecycleBinVendorDataTable.php`
5. `app/DataTables/BackupDataTable.php`
- প্রতিটিতে লাইভ AJAX সার্চ, কলাম সর্টিং, পেজিনেশন এবং সুদৃশ্য `Restore` ও `Permanent Delete` বাটন থাকবে।

---

### Pillar 7: শেয়ার্ড হোস্টিং বনাম VPS সাপোর্ট

- ম্যানুয়াল PayPal REST v2 সার্ভিস সম্পূর্ণ প্ল্যাটফর্ম-নিরপেক্ষ (Platform-Agnostic)।
- এটি লারাভেলের নেটিভ `Http` ক্লায়েন্ট দিয়ে তৈরি। তাই ক্লায়েন্ট বর্তমান Namecheap শেয়ার্ড হোস্টিংয়ে থাকুক অথবা ভবিষ্যতে Nginx/Apache সম্বলিত যে কোনো VPS / Cloud সার্ভারে শিফট করুক—এই কোডে ১ লাইনও পরিবর্তন করতে হবে না।

---

## ৩. ফাইল পরিবর্তন তালিকা (File Change Manifest)

| অ্যাকশন | ফাইলের নাম ও পাথ | বিবরণ |
|---|---|---|
| **[NEW]** | `database/migrations/2026_09_08_000003_create_dedicated_payment_settings_table.php` | ডেডিকেটেড `payment_settings` টেবিল |
| **[NEW]** | `app/Models/PaymentSetting.php` | পেমেন্ট গেটওয়ে সেটিংস মডেল ও হেল্পার |
| **[NEW]** | `app/Http/Controllers/Backend/PaymentSettingController.php` | স্ক্রিনশট অনুকরণে সেটিংস কন্ট্রোলার |
| **[NEW]** | `resources/views/backend/settings/payment_settings.blade.php` | স্ক্রিনশটের হুবহু ভার্টিক্যাল ট্যাব UI |
| **[NEW]** | `app/DataTables/RecycleBin/...` | রিসাইকেল বিনের জন্য Yajra DataTable ক্লাউড |
| **[NEW]** | `app/DataTables/BackupDataTable.php` | ডাটাবেজ ব্যাকআপের জন্য Yajra DataTable |
| **[MODIFY]** | `resources/views/frontend/pages/checkout.blade.php` | পেমেন্ট মেথড রেডিও কার্ড অপশন |
| **[MODIFY]** | `app/Http/Controllers/Frontend/CartController.php` | চেকআউট রিকোয়েস্টে পেমেন্ট মেথড হ্যান্ডলিং |
| **[MODIFY]** | `resources/views/backend/layouts/navbar.blade.php` | ন্যাভবার রি-অর্গানাইজেশন |
| **[MODIFY]** | `routes/web.php` | রাউট পরিচ্ছন্নকরণ |
| **[DELETE]** | `app/Http/Controllers/Backend/MaintenanceController.php` | ক্যাশ পার্জ কন্ট্রোলার বাদ |
| **[DELETE]** | `resources/views/backend/maintenance/clear_cache.blade.php` | ক্যাশ পার্জ ভিউ বাদ |

---

## ৪. ভেরিফিকেশন ও টেস্ট প্ল্যান (Verification Plan)

1. **ডাটাবেজ মাইগ্রেশন:** `php artisan migrate` রান করে `payment_settings` টেবিল নিশ্চিত করা।
2. **অ্যাডমিন UI টেস্ট:** `/admin/payment-settings` পেজে গিয়ে স্ক্রিনশটের মতো ভার্টিক্যাল ট্যাবগুলো চেক করা এবং সেটিংস সেভ করা।
3. **ফ্রন্টএন্ড চেকআউট টেস্ট:** ফ্রন্টএন্ড থেকে কার্টে প্রোডাক্ট যুক্ত করে চেকআউট পেজে যাওয়া, COD ও PayPal মেথড সিলেক্ট করে অর্ডার প্লেস সফল হয় কিনা যাচাই করা।
4. **Yajra DataTable টেস্ট:** রিসাইকেল বিন এবং ব্যাকআপ পেজে সার্ভার-সাইড সার্চ ও পেজিনেশন কাজ করছে কিনা দেখা।
5. **অটোমেটেড টেস্ট স্যুট:** `php artisan test tests/Feature/Phase6` দিয়ে সব ফিচার গ্রিন নিশ্চিত করা।
