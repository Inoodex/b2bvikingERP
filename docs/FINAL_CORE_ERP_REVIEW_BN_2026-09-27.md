**Final Core ERP Verification — B2B Viking ERP**

তারিখ: ২৭ সেপ্টেম্বর ২০২৬। Reviewed commit: 9b4b544631ad8ff30e061861d2364100745ce25e।

**সিদ্ধান্ত: Core ERP-এর বিস্তৃত implementation আছে; বর্তমান snapshot-কে production-ready enterprise ERP হিসেবে sign-off দেওয়া যায় না।**

এই সিদ্ধান্ত future modules বাদ থাকার কারণে নয়। বর্তমান core scope-এর stock, accounting, payment, permission এবং installation-এ নির্দিষ্ট failure পাওয়া গেছে। কিছু গুরুত্বপূর্ণ control সঠিকভাবে কাজও করছে।

Gemini-এর “enterprise-level” দাবি architectural scope বোঝালে তার ভিত্তি আছে: procurement, sales, inventory, accounting, approval, reporting ও background jobs রয়েছে। কিন্তু transaction integrity, security এবং production readiness বোঝালে বর্তমান evidence সেই দাবি সমর্থন করে না। Gemini-এর নিজস্ব test evidence বা মূল্যায়নের মানদণ্ড আমার কাছে নেই।

**Scope ও যাচাইয়ের সীমা**

বর্তমান scope: master data, users/roles, procurement, sales, warehouse, GRN, customer/vendor payments, journal, reports, approval, B2B ordering/reviews, backup ও deployment।

আপনার নির্দেশ অনুযায়ী POS, HR/payroll, Denmark government API, courier integration, bank feeds এবং external e-commerce sync future phase। এগুলোকে এই verdict-এ missing requirement হিসেবে ধরা হয়নি।

এটি codebase-wide static checks ও ঝুঁকিপূর্ণ core flow-এর targeted runtime verification। প্রতিটি screen-এর browser test, পূর্ণ production-like end-to-end flow, real gateway capture, concurrency/load test বা backup restore সম্পন্ন হয়েছে—এমন দাবি নয়। Application/business code পরিবর্তন করিনি। এই final pass-এর নতুন runtime probes synthetic SQLite in-memory database-এ হয়েছে; বাস্তব customer data বা payment ব্যবহার হয়নি।

**Enterprise readiness gate**

| Gate | ফল | ভিত্তি |
|---|---|---|
| Functional breadth | Substantial implementation | Core modules, services ও reports রয়েছে |
| Access control | FAIL | Permission-less Staff sensitive middleware gate পার হয় |
| Inventory integrity | FAIL | Wrong-outlet dispatch ও wrong-bin FIFO পুনরুৎপাদিত |
| Accounting integrity | FAIL | Missing GRN journal ও duplicate invoice journal |
| Basic journal controls | নির্বাচিত checks PASS | Imbalance ও closed fiscal period reject হয় |
| Online payment settlement | FAIL | Simulated capture-এর পরে local settlement ভেঙে যায় |
| Fresh installation | FAIL | Historical migration missing column query করে |
| Repeatable full test suite | প্রতিষ্ঠিত নয় | আগের full run setup/lock errors-এ অসম্পূর্ণ |
| Backup/recovery | যাচাই অসম্পূর্ণ | Export concerns আছে; restore drill হয়নি |
| Production operations | Sign-off হয়নি | Queue, deployment ও document access concerns আছে |

**এই pass-এ চালিয়ে পাওয়া ফল**

| ID | Scenario | প্রত্যাশিত | পাওয়া ফল |
|---|---|---|---|
| R1 | Outlet 2 থেকে 10 dispatch; outlet 1 stock 3, outlet 2 stock 20 | Outlet 2 stock 10 | Outlet 1 stock 0, outlet 2 stock 20; ledger outlet 2-তে out 10 |
| R2 | Bin 1 থেকে 3 unit FIFO; bin 1 cost 2, bin 2 cost 7 | Bin 1 batch কমবে; cost 6 | Bin 2 batch কমেছে; cost 21; physical stock কমেছে bin 1-এ |
| R3 | একই posted invoice দুবার GL-এ post | একটি journal | দুটি journal; invoice status এখনও posted |
| R4 | GRN header-এর পরে accepted item তৈরি | Receipt posting flow-এ journal নিশ্চিত | Item count 1; GRN journal count 0 |
| R5 | Public invoice callback-এ simulated successful capture | Payment persist ও invoice due কমা | Capture mock 1 বার; ArgumentCountError; transaction pending; due 100 |
| R6 | Permission-less Staff: backup, recycle-bin, feature toggle | Deny | Middleware next handler-এ পৌঁছেছে |
| R7 | একই Staff: Product index, PaymentSetting update | Deny | দুটোতেই 403 |
| R8 | Historical vendor migration; deleted_at তখন নেই | Migration এগোবে | vendors.deleted_at query exception |
| R9 | Debit 100, credit 0 journal | Reject | Reject |
| R10 | Closed fiscal year-এ balanced journal | Reject | Reject |
| R11 | 48 ঘণ্টা পুরোনো file-এর cleanup expression | পুরোনো শনাক্ত | Diff -48; বর্তমান >=24 condition false |

R1–R5 ও R8–R10 production services/models/controller দিয়ে reduced SQLite fixture-এ চালানো। এতে MySQL locking, complete schema constraints বা external network behaviour প্রমাণ হয় না। R4 model-event ordering reproduction; পুরো GRN service end-to-end test নয়। Production service-এর header/items ordering-এর সঙ্গে ফল মিলিয়ে দেখা হয়েছে। R6–R7 synthetic Staff দিয়ে middleware test; বাস্তব backup download/delete হয়নি। R5-এর capture simulated; কোনো টাকা নেওয়া হয়নি।

Evidence: [runtime-evidence.txt](/home/agent47/Sites/b2bvikingERP/docs/audit/core-erp-2026-09-27/runtime-evidence.txt), [controls-evidence.txt](/home/agent47/Sites/b2bvikingERP/docs/audit/core-erp-2026-09-27/controls-evidence.txt)।

**প্রধান release blockers**

**১. Stock dispatch — P0; runtime-confirmed।** Stock lookup-এ outlet/bin নেই। Stock কম হলেও block না করে max(0, ...) দিয়ে quantity লিখে পুরো dispatch ledger-এ দেখায়। FIFO batches-ও এই পথে update হয় না। [DeliveryOrderService](/home/agent47/Sites/b2bvikingERP/app/Services/DeliveryOrderService.php:86)।

Acceptance: explicit source location, locked stock selection, stock sufficiency, batch/ledger alignment এবং repeated/concurrent dispatch-এ একবার deduction।

**২. FIFO costing — P1; runtime-confirmed।** Physical stock query bin filter করলেও batch query outlet-wide। ফলে quantity ও costing আলাদা bin-এর ওপর প্রয়োগ হয়। [FifoDepletionService](/home/agent47/Sites/b2bvikingERP/app/Services/Inventory/FifoDepletionService.php:42)।

Acceptance: picked batch, physical stock, ledger bin ও COGS একই movement-এর জন্য মিলতে হবে।

**৩. Duplicate invoice journal — P0; নতুন runtime finding।** Controller শুধু paid/sent invoice block করে। Posted invoice আবার service-এ যায়। Service নতুন journal তৈরি করে; posting state/unique event guard নেই। একই invoice দুবার post করে 2 journal পাওয়া গেছে। [Controller gate](/home/agent47/Sites/b2bvikingERP/app/Http/Controllers/Backend/SalesInvoiceController.php:221), [posting service](/home/agent47/Sites/b2bvikingERP/app/Services/SalesInvoiceService.php:188)।

Acceptance: source/event-ভিত্তিক atomic idempotency; retries ও concurrent requests-এ একটি posting। Invoice status এবং GL posting completion-এর অর্থও সামঞ্জস্যপূর্ণ হতে হবে।

**৪. GRN journal missing — P1; lifecycle reproduction + source-confirmed।** Header created event-এ observer items পড়ে; items তখনও তৈরি হয়নি। Zero amount পেয়ে return করে। পরে items/stock তৈরি হলেও StockReceiveService journal posting ডাকে না। Posting exception swallow করার পথও আছে। [Creation ordering](/home/agent47/Sites/b2bvikingERP/app/Services/StockReceiveService.php:171), [observer](/home/agent47/Sites/b2bvikingERP/app/Observers/AccountingObserver.php:39)।

Acceptance: items ও landed cost final হওয়ার পরে explicit, atomic posting; silent missing journal নয়; stock-to-GL reconciliation।

**৫. Public invoice payment — P0; controller-path runtime-confirmed।** Capture-এর পরে recordPayment()-এ required actor argument পাঠানো হয় না। ArgumentCountError controller-এর catch(Exception) ধরে না। পরীক্ষায় simulated capture হওয়ার পর স্থানীয় transaction rollback হয়েছে। Allocations-এর shape এবং order-reference overwrite-ও source-level সমস্যা। [Caller](/home/agent47/Sites/b2bvikingERP/app/Http/Controllers/Frontend/InvoicePaymentController.php:142), [service contract](/home/agent47/Sites/b2bvikingERP/app/Services/CustomerPaymentService.php:39)।

Acceptance: correct contract, amount/currency/reference binding, immutable payment identity, duplicate callback test এবং capture-success/local-failure recovery।

**৬. Existing-order PayPal callback — P0; source-confirmed।** order_id থেকে order load করার branch-এ ownership, captured amount/currency ও gateway reference order-এর সঙ্গে মিলিয়ে দেখা নেই। COMPLETED capture পেলেই স্থানীয় order-এর total full paid ধরা হয়। Live gateway exploit test করা হয়নি। [Callback](/home/agent47/Sites/b2bvikingERP/app/Http/Controllers/Frontend/CartController.php:852)।

Acceptance: unrelated customer/order, underpayment, wrong currency ও unrelated gateway reference সব reject হবে।

**৭. Sensitive permissions — P0; middleware runtime-confirmed।** Controller permission map-এ না মিললে request allow হয়। Backup, recycle-bin ও feature-toggle actions এই পথে পড়ে। Product/PaymentSetting control 403 হয়েছে; সব permission ভাঙা বলা হচ্ছে না। [Fallback](/home/agent47/Sites/b2bvikingERP/app/Http/Middleware/CheckPermission.php:183), [routes](/home/agent47/Sites/b2bvikingERP/routes/web.php:534)।

Acceptance: explicit policies/permissions, default-deny, এবং প্রতিটি sensitive endpoint-এর negative authorization tests।

**৮. Clean migration — P1; পুনরুৎপাদিত।** জুলাইয়ের migration বর্তমান SoftDeletes-যুক্ত Vendor model ব্যবহার করে। deleted_at column তৈরি হয় সেপ্টেম্বরের migration-এ। আগের MySQL failure এবার নির্দিষ্ট migration-এর reduced SQLite reproduction-এও নিশ্চিত হয়েছে। [Historical migration](/home/agent47/Sites/b2bvikingERP/database/migrations/2026_07_27_041317_refactor_vendors_currency_columns.php:24)।

Acceptance: historical migration current model scope-এর ওপর নির্ভর করবে না; clean MySQL migration ও full suite পাস করতে হবে।

**৯. Sales-return ledger — P1; নতুন source/schema finding।** Return restock product/variant দিয়ে stock খোঁজে, outlet/bin দিয়ে নয়। Ledger create-এ transaction_type/quantity পাঠায়, কিন্তু model fillable/schema-তে in_qty/out_qty/date ব্যবহৃত। প্রয়োজনীয় in_qty ও date পাঠানো হয় না। Strict schema-তে error/rollback অথবা অন্য schema-তে incomplete movement record হওয়ার ঝুঁকি। পূর্ণ return HTTP flow চালানো হয়নি। [Return approval](/home/agent47/Sites/b2bvikingERP/app/Http/Controllers/Backend/SalesReturnController.php:203), [ledger model](/home/agent47/Sites/b2bvikingERP/app/Models/StockLedger.php:12), [schema](/home/agent47/Sites/b2bvikingERP/database/migrations/2026_01_15_134048_create_inventory_tables.php:25)।

Acceptance: return → correct location restock → batch → ledger → credit note/GL integration test।

**১০. Governance switches — P2; নতুন source finding।** Auto-GL, strict fiscal lock, batch tracking ও customer credit-lock-এর named toggles UI/controller-এ defined। সংশ্লিষ্ট engine-এ এই flags ব্যবহারের প্রমাণ নেই। Searched app/routes/resources-এ is_feature_enabled()-এর definition আছে, caller নেই। তাই switch save হওয়া behaviour বদলানোর প্রমাণ নয়। PayPal/COD legacy boolean-এও লেখা হয়; সব toggle অকার্যকর বলা হচ্ছে না। [Definitions](/home/agent47/Sites/b2bvikingERP/app/Http/Controllers/Backend/FeatureToggleController.php:32), [helper](/home/agent47/Sites/b2bvikingERP/app/Helpers/helpers.php:125)।

Acceptance: প্রতিটি switch-এর on/off test অথবা unsupported switch বাদ দেওয়া।

**আগের audit-এর বহাল concerns**

| বিষয় | প্রমাণ ও অবস্থা |
|---|---|
| Review modal XSS | Customer text সরাসরি HTML insertion; source-confirmed; browser exploit test নয় |
| Admin 2FA | Normal login-এর challenge code commented |
| Deployment | Hardcoded secret এবং command failure হলেও success response; secret value report-এ নেই |
| GRN validation | PO/item/bin ownership ও quantity constraints অসম্পূর্ণ |
| Multi-company accounting | Account/fiscal-year lookup company-scoped নয় |
| PDF privacy | Public disk/predictable filename; deployment-dependent exposure |
| Numbering | কয়েকটি generator unlocked read/count flow; concurrency test বাকি |
| Backup fidelity | Numeric-looking string ও consistent snapshot concerns; restore drill হয়নি |
| Operations | Queue default retry-after/job timeout mismatch; cleanup sign error |
| Route hygiene | 53 registered application action-এর method নেই; অনেকটি unused resource action হতে পারে |

বিস্তারিত references [আগের রিপোর্টে](/home/agent47/Sites/b2bvikingERP/docs/PROJECT_ANALYSIS_BN_2026-09-27.md)। আগের future-phase gaps বর্তমান ERP verdict-এ গণ্য নয়; scope-এর বিষয়ে এই final report প্রাধান্য পাবে।

**মডিউলভিত্তিক acceptance**

| বর্তমান ERP অংশ | মূল্যায়ন |
|---|---|
| Master/catalog/customer/vendor | Implementation আছে; সব CRUD/role permutation certified নয় |
| RFQ/CS/PO/LC/shipment | Substantial implementation; complete lifecycle acceptance বাকি |
| GRN/stock receive | Accounting ও validation blockers |
| Delivery/transfer/FIFO | Location/cost integrity failures |
| Invoice/GL | Duplicate invoice posting ও missing GRN posting |
| Online payment | Simulated settlement failure; binding issues |
| Offline/vendor payment | Transaction ও কিছু locking/overpayment guard আছে; সব allocation/ownership/FX scenario verified নয় |
| Sales/vendor return | Ledger mismatch; replacement/batch reconciliation আরও যাচাই প্রয়োজন |
| Approval/credit | Real workflow আছে; concurrency/reversal/re-approval coverage প্রমাণিত নয় |
| Reports | UI/PDF আছে; underlying stock/GL ভুল হলে report correctness sign-off হয় না |
| Security/operations | Sensitive permission, review, 2FA ও deployment concerns |

**Test evidence ও সীমা**

- এই pass-এ 791 first-party PHP file পুনরায় lint: error 0।
- Unit suite পুনরায়: 2 tests, 2 assertions, pass। এটি core workflow coverage নয়।
- Route reflection পুনরায়: 762 registered routes, 53 missing application controller methods।
- আগের backend review tests: 8 tests, 29 assertions pass; এই pass-এ পুনরায় চালানো হয়নি।
- আগের full run: 310 discovered; 132 progress markers-এ 24 pass, 13 failure, 95 error, তারপর interrupted। এটি completed PHPUnit summary বা 95টি আলাদা product defect নয়।
- এই pass-এ unresolved migration blocker নির্দিষ্টভাবে reproduce করেছি; পূর্ণ MySQL suite পুনরায় চালাইনি।
- Browser E2E, live gateway/webhook, concurrency/load, frontend production build, production config ও backup restore এখনও acceptance evidence হিসেবে অনুপস্থিত।

[Source snapshot](/home/agent47/Sites/b2bvikingERP/docs/audit/core-erp-2026-09-27/syntax-snapshot.json)-এর fingerprint app/routes/config/database/tests/bootstrap PHP files কভার করে; Blade/vendor নয়।

Runtime cases পুনরায় চালানোর [diagnostic script](/home/agent47/Sites/b2bvikingERP/docs/audit/core-erp-2026-09-27/reproduce.php) সংরক্ষণ করেছি। Repository root থেকে কমান্ড: **php docs/audit/core-erp-2026-09-27/reproduce.php**।

Script SQLite in-memory database বাধ্যতামূলক করে এবং simulated gateway ব্যবহার করে। আবার চালিয়ে stored runtime evidence-এর সঙ্গে output মিলেছে। Exit code 0 মানে observations সংগ্রহ সম্পন্ন; ERP সব পরীক্ষায় pass করেছে নয়। Script stock/accounting/payment/migration cases চালায়; permission/route probe output আলাদা controls artifact-এ আছে।

**Release sign-off-এর জন্য কাজের ক্রম**

1. Permission bypass, review XSS ও payment binding/settlement fix।
2. Outlet/bin/batch/catalog/ledger একসঙ্গে update; dispatch/return negative tests।
3. GRN journal completeness এবং invoice posting idempotency।
4. Fresh MySQL migration ও isolated fixtures ঠিক করে full suite।
5. Procurement acceptance: PO → partial GRN → landed cost → vendor bill → partial payment → vendor return।
6. Sales acceptance: approval/credit hold → partial delivery → invoice → partial/full payment → return/credit note।
7. Failure acceptance: insufficient stock, duplicate/concurrent request, unrelated payment, capture-success/local-failure, unauthorized staff।
8. Operations acceptance: private document delivery, queue retries, deploy failure response, backup restore ও agreed load test।

প্রতিটি flow-তে success message ছাড়াও stock, batch cost, customer/vendor due, balanced journal এবং permission outcome মিলতে হবে।

Client-facing সঠিক বক্তব্য: **“Core ERP-এর বড় অংশের development হয়েছে। Final verification-এ critical transaction ও security defects পাওয়া গেছে। Fix, regression testing এবং business acceptance-এর পরে enterprise production readiness নিশ্চিত করা যাবে।”**

