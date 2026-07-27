# B2B Viking ERP - User Manual (Phase 1)
**Foundation, Outlet Management & Advanced Approval Engine**

---

## 1. Introduction (পরিচিতি)
B2B Viking ERP-এর Phase 1 সফলভাবে সম্পন্ন হয়েছে। এই ম্যানুয়ালটি এমনভাবে তৈরি করা হয়েছে যেন যেকোনো নন-টেকনিক্যাল ইউজার বা অ্যাডমিন খুব সহজেই সিস্টেমের কোর ফাংশনগুলো বুঝতে এবং ব্যবহার করতে পারেন। ডেভেলপারকে বারবার জিজ্ঞাসা করার প্রয়োজন নেই, সব উত্তর এখানেই আছে!

---

## 2. Company & Outlet Management (কোম্পানি এবং আউটলেট)
সিস্টেমে কোম্পানি এবং আউটলেটকে আলাদাভাবে সংজ্ঞায়িত করা হয়েছে।
* **Company (Sister Concern):** এটি হলো মূল আইনি প্রতিষ্ঠান (যেমন: *Copenhagen Tourist Point A/S*).
* **Outlet:** এটি হলো ওই কোম্পানির অধীনে থাকা দোকান বা গুদাম (যেমন: *Nyhavn Store*, *Central Warehouse*).

### কীভাবে নতুন Outlet যুক্ত করবেন?
1. বাম পাশের মেন্যু থেকে **Master Setup** > **Outlets**-এ ক্লিক করুন।
2. **Create New Outlet** বাটনে ক্লিক করুন।
3. আউটলেটের নাম দিন এবং এটি কোন Company-এর অধীনে, তা ড্রপডাউন থেকে সিলেক্ট করে **Save** করুন।

---

## 3. User Management (ইউজার ম্যানেজমেন্ট)
একটি নির্দিষ্ট আউটলেটের জন্য ইউজার বা স্টাফ তৈরি করা।

### কীভাবে ইউজার তৈরি এবং অ্যাসাইন করবেন?
1. মেন্যু থেকে **User Management** > **Users**-এ যান।
2. **Add New User**-এ ক্লিক করুন।
3. ইউজারের নাম, ইমেইল এবং পাসওয়ার্ড দিন।
4. **Role**: ইউজারের পদবী সিলেক্ট করুন (যেমন: Manager, Sales Staff)।
5. **Company & Outlet**: এই ইউজার কোন দোকান/আউটলেটের জন্য কাজ করবে তা নির্দিষ্ট করে দিন। 
*(লক্ষ্য করুন: একজন Outlet User শুধুমাত্র তার নিজের দোকানের ডাটাই দেখতে ও কাজ করতে পারবেন।)*

---

## 4. Advanced Approval Engine (অ্যাপ্রুভাল ইঞ্জিন) - ⚠️ Very Important!
এই ERP-তে Odoo/SAP-এর মতো একটি অত্যাধুনিক (Enterprise-grade) ডাইনামিক অ্যাপ্রুভাল সিস্টেম রয়েছে। এর মাধ্যমে আপনি টাকার অংকের (Amount) ওপর ভিত্তি করে বিভিন্ন রুলস বানাতে পারবেন।

### 💡 কনসেপ্ট (কীভাবে কাজ করে?)
ধরুন, আপনি চান:
* ৫,০০০ টাকার নিচে কোনো রিকুইজিশন হলে শুধু **Manager** অ্যাপ্রুভ করবে।
* ৫,০০০ টাকার বেশি হলে প্রথমে **Manager** এবং পরে **Finance Head** অ্যাপ্রুভ করবে।
এই পুরো রুলস আপনি সিস্টেম থেকেই বানাতে পারবেন কোনো কোডিং ছাড়াই!

### 🛠️ কীভাবে নতুন Approval Workflow তৈরি করবেন?
1. মেন্যু থেকে **Master Setup** > **Approval Workflows**-এ ক্লিক করুন।
2. **Create Approval Workflow** বাটনে ক্লিক করুন।
3. **Workflow Name**: রুলের একটি নাম দিন (যেমন: *High Value Requisition*).
4. **Target Module**: কোন কাজের জন্য এই রুল? ড্রপডাউন থেকে সিলেক্ট করুন (যেমন: *Requisition / Product Request*).
5. **Min Amount & Max Amount**: 
   - *উদাহরণ ১:* যদি চান ৫০০০ থেকে ১০০০০ টাকার মধ্যে কাজ করবে, তবে Min = 5000, Max = 10000 দিন।
   - *উদাহরণ ২:* যদি চান ৫০০০ এর ওপরে যেকোনো অ্যামাউন্টে কাজ করবে, তবে Min = 5000 দিন এবং Max ফাঁকা রাখুন (ফাঁকা মানে আনলিমিটেড)।
   - *উদাহরণ ৩:* যদি চান সব অ্যামাউন্টের জন্যই কাজ করবে, তবে দুটিই ফাঁকা বা 0 রাখুন।

### 🔗 Approval Steps (কে কে অ্যাপ্রুভ করবে তা ঠিক করা)
একই ফর্মে নিচের দিকে **Approval Chain Steps** সেকশন পাবেন।
1. **Step 1:** প্রথম কে অ্যাপ্রুভ করবে? `Approver Role` থেকে Manager সিলেক্ট করুন।
2. **Add Another Level/Step:** বাটনে ক্লিক করে ২য় স্টেপ আনুন।
3. **Step 2:** ২য় কে অ্যাপ্রুভ করবে? `Approver Role` থেকে Finance Head সিলেক্ট করুন।
4. সবশেষে **Create/Save** বাটনে ক্লিক করুন।

🎉 **ব্যাস! আপনার ডাইনামিক রুল তৈরি!** এখন কেউ ৫,০০০ টাকার বেশি রিকুইজিশন দিলেই অটোমেটিক ২ জনের কাছে অ্যাপ্রুভালের জন্য চলে যাবে।

---

# English Version

## 1. Introduction
Phase 1 of the B2B Viking ERP has been successfully completed. This manual is designed so that any non-technical user or admin can easily understand and use the core functions of the system. You don't need to ask the developer repeatedly, all answers are here!

## 2. Company & Outlet Management
The system defines companies and outlets separately.
* **Company (Sister Concern):** This is the main legal entity (e.g., *Copenhagen Tourist Point A/S*).
* **Outlet:** This is a store or warehouse under that company (e.g., *Nyhavn Store*, *Central Warehouse*).

### How to Add a New Outlet?
1. Click on **Master Setup** > **Outlets** from the left menu.
2. Click the **Create New Outlet** button.
3. Provide the outlet name, select the Company it belongs to from the dropdown, and click **Save**.

## 3. User Management
Creating users or staff for a specific outlet.

### How to Create and Assign Users?
1. Go to **User Management** > **Users** from the menu.
2. Click on **Add New User**.
3. Provide the user's name, email, and password.
4. **Role**: Select the user's designation (e.g., Manager, Sales Staff).
5. **Company & Outlet**: Specify which store/outlet this user will work for.
*(Note: An Outlet User can only view data and perform tasks for their assigned store.)*

## 4. Advanced Approval Engine - ⚠️ Very Important!
This ERP features a state-of-the-art dynamic approval system (Enterprise-grade) similar to Odoo/SAP. Through this, you can create various rules based on monetary amounts.

### 💡 Concept (How does it work?)
Suppose you want:
* For requisitions under 5,000 DKK, only the **Manager** approves.
* For requisitions over 5,000 DKK, first the **Manager** and then the **Finance Head** approves.
You can create all these rules directly from the system without any coding!

### 🛠️ How to Create a New Approval Workflow?
1. Click on **Master Setup** > **Approval Workflows** from the menu.
2. Click the **Create Approval Workflow** button.
3. **Workflow Name**: Give the rule a name (e.g., *High Value Requisition*).
4. **Target Module**: What task is this rule for? Select from the dropdown (e.g., *Requisition / Product Request*).
5. **Min Amount & Max Amount**: 
   - *Example 1:* If you want it to work between 5,000 and 10,000, set Min = 5000, Max = 10000.
   - *Example 2:* If you want it to work for any amount above 5,000, set Min = 5000 and leave Max blank (blank means unlimited).
   - *Example 3:* If you want it to apply to all amounts, leave both blank or set to 0.

### 🔗 Approval Steps (Defining who will approve)
At the bottom of the same form, you'll find the **Approval Chain Steps** section.
1. **Step 1:** Who will approve first? Select *Manager* from the `Approver Role`.
2. **Add Another Level/Step:** Click the button to add a 2nd step.
3. **Step 2:** Who will approve second? Select *Finance Head* from the `Approver Role`.
4. Finally, click the **Create/Save** button.

🎉 **Done! Your dynamic rule is created!** Now, if someone submits a requisition over 5,000, it will automatically go to 2 people for approval.

---
*Document Version: 1.0 (Phase 1)*
*Generated for B2B Viking ERP Client*
