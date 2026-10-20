**B2B Viking ERP — প্রজেক্ট বিশ্লেষণ রিপোর্ট**

পরবর্তী core-scope final verification: [Final Core ERP Review](/home/agent47/Sites/b2bvikingERP/docs/FINAL_CORE_ERP_REVIEW_BN_2026-09-27.md)। সেখানে নতুন runtime evidence ও বর্তমান scope-এর verdict আছে; POS/HR/integration future phase হিসেবে বাদ দেওয়া হয়েছে।

পর্যালোচনার তারিখ: ২৭ সেপ্টেম্বর ২০২৬। Repository: `/home/agent47/Sites/b2bvikingERP`।

প্রজেক্টে একটি বিস্তৃত B2B ERP-এর বাস্তব implementation আছে। Procurement, sales, warehouse, accounting, approval, payment এবং reporting একই Laravel application-এ যুক্ত হয়েছে। তবে বর্তমান snapshot-কে production-ready বা client requirement সম্পূর্ণ পূরণ করেছে বলা যাচ্ছে না: permission, payment, stock, review security এবং clean installation-এ গুরুত্বপূর্ণ সমস্যা পাওয়া গেছে।

এটি repository-wide inventory, first-party PHP syntax check, মূল business flow-এর গভীর code review এবং নির্বাচিত runtime verification-এর রিপোর্ট। প্রতিটি template বা dependency-এর প্রতিটি লাইন manual audit করা হয়েছে—এমন দাবি নয়। Application source পরিবর্তন করা হয়নি। Production server, live PayPal, বাস্তব customer data বা browser-based end-to-end পরীক্ষা করা হয়নি।

**প্রজেক্টের পরিসর ও প্রযুক্তি**

নিচের সংখ্যা রিপোর্ট তৈরির আগের snapshot-এর।

| বিষয় | পর্যবেক্ষণ |
|---|---|
| Backend | PHP requirement `^8.2`; স্থানীয় runtime PHP 8.3.33 |
| Framework | Laravel `^12.0`; composer.lock-এ v12.63.0 |
| UI | Blade, jQuery/DataTables, Alpine, Tailwind, Vite |
| Permission | Spatie Permission ও নিজস্ব `CheckPermission` middleware |
| PDF / export | Dompdf, Laravel Excel, background jobs |
| App files | 477টি; প্রায় 57,502 লাইন |
| Controllers / Models | 112 / 118 |
| Services / Form Requests | 44 / 64 |
| DataTables / Jobs | 67 / 20 |
| Migrations | 234টি |
| Blade views | 386টি; প্রায় 87,607 লাইন |
| Tests | 45টি `*Test.php`; 310টি discovered test case |
| Registered routes | testing bootstrap-এ 762টি |
| Documentation | `docs/`-এ 56টি, `plan/`-এ 39টি ফাইল |

`vendor`, generated assets, storage content এবং `.kilo/worktrees`-এর duplicate checkout মূল application inventory-এর অংশ নয়। `.env`-এর secret values এই রিপোর্টে পড়া বা প্রকাশ করা হয়নি। Dependency vulnerability database scan করা হয়নি; তাই কোনো CVE-clean দাবি করা হচ্ছে না।

**Business flow ও মডিউলের অবস্থা**

| মডিউল | পাওয়া implementation | মূল্যায়ন |
|---|---|---|
| Foundation | Users, roles, permissions, company, department, outlet, currency | বড় অংশ আছে; permission coverage ও company boundary অসম্পূর্ণ |
| Procurement | Product request, RFQ, vendor quotation, comparison statement, PO, PI, LC, shipment, GRN, landed cost, vendor bill/return | Flow আছে; receiving ও accounting সংযোগে সমস্যা |
| Sales | Quotation, sales order, approval, delivery order, invoice, payment, return, credit note | Flow আছে; dispatch ও payment settlement আগে ঠিক করা দরকার |
| Inventory / WMS | Outlet/bin stock, stock ledger, FIFO batches, transfer, adjustment, reorder, snapshot, handling units | বিস্তৃত implementation; বিভিন্ন stock representation সব পথে একসঙ্গে update হয় না |
| Accounting | Chart of accounts, fiscal year, journal, bank, petty cash, assets, AR/AP, financial reports | মৌলিক engine আছে; journal completeness ও multi-company integrity নিশ্চিত নয় |
| B2B frontend | Login, catalog visibility, cart, checkout, saved form, custom product request, wishlist, reviews | কার্যকর code আছে; payment callback ও review modal ঝুঁকিপূর্ণ |
| Operations | Queue PDFs, mail jobs, backup, recycle bin, feature toggles, deployment webhook | Controls আছে; permission, retry ও deployment handling-এ ঘাটতি |
| POS / HR / Payroll | `docs/07_pos_and_retail` ও `docs/08_hr_and_payroll`-এ specification | পর্যালোচিত app/routes-এ পূর্ণ executable implementation পাওয়া যায়নি |
| External integration | PayPal API ও Google Drive storage adapter | Denmark government, courier, bank feeds, external commerce sync-এর পূর্ণ flow-এর প্রমাণ পাওয়া যায়নি |

মূল procurement flow: requisition → RFQ → quotation comparison → PO → shipment/LC → GRN → stock batch/ledger → vendor bill/payment।

মূল sales flow: catalog/cart অথবা quotation → order → approval → delivery → invoice → payment → journal/report। এই সংযোগগুলোর correctness আলাদা CRUD screen-এর উপস্থিতির চেয়ে বেশি গুরুত্বপূর্ণ।

Requirement-এ উল্লিখিত LIFO, নির্বাচিত batch দিয়ে issue, consolidation, cash-flow reporting এবং সব external sync-কে সম্পূর্ণ ধরে নেওয়ার মতো প্রমাণ এই review-তে পাওয়া যায়নি। এগুলোর জন্য requirement-to-code-to-test acceptance matrix দরকার।

**যা ভালো আছে**

- বেশ কিছু গুরুত্বপূর্ণ domain operation controller থেকে service-এ সরানো হয়েছে।
- Transaction, কিছু জায়গায় row lock, validation request এবং foreign key ব্যবহার আছে। বিশেষ করে FIFO engine stock ও batch lock করে।
- Journal service debit/credit balance ও closed fiscal period পরীক্ষা করে। Customer payment service-এর মূল flow transaction-এর মধ্যে চলে।
- Dynamic approval steps, document numbering configuration, stock ledger এবং receipt/delivery documents-এর বাস্তব code আছে।
- PDF generation-এর জন্য queue jobs আছে; সব বড় report request-এর মধ্যে তৈরি করা হচ্ছে না।
- Review DataTable relationship eager-load করে এবং তালিকার comment/customer text escape করে।
- Feature test suite-এর পরিসর উল্লেখযোগ্য। তবে বর্তমান run ও coverage gaps-এর কারণে সেটি এখন reliable release gate নয়।

**অগ্রাধিকারভিত্তিক সমস্যা**

P0 = release-এর আগে সমাধান করা দরকার। P1 = উচ্চ গুরুত্বের correctness/security/installation সমস্যা। P2 = reliability, maintainability ও scale-এর পরবর্তী কাজ। Severity এই repository-র code path ও সম্ভাব্য প্রভাবের ভিত্তিতে; live exploitation পরীক্ষা করা হয়নি।

**১. P0 — Review detail modal-এ stored XSS-এর পথ আছে**

Customer comment server-এ string হিসেবে গ্রহণ করা হয়। DataTable সেটি escape করলেও detail API raw comment ফেরত দেয়। Modal JavaScript `${r.comment}`, `${r.user.name}` এবং অন্যান্য text HTML template-এ ঢুকিয়ে `.html(html)` চালায়। তাই customer-controlled markup admin-এর browser-এ HTML হিসেবে render হতে পারে।

প্রমাণ: [review modal](/home/agent47/Sites/b2bvikingERP/resources/views/backend/reviews/index.blade.php:704), [HTML insertion](/home/agent47/Sites/b2bvikingERP/resources/views/backend/reviews/index.blade.php:707), [review controller](/home/agent47/Sites/b2bvikingERP/app/Http/Controllers/Backend/ReviewController.php:131)। এটি source-এ input থেকে HTML sink পর্যন্ত trace করা finding; browser exploit চালানো হয়নি।

করণীয়: comment/name/category-এর মতো text `.text()`/`textContent` দিয়ে বসানো; URL/attribute আলাদাভাবে validate ও escape করা; malicious text দিয়ে browser regression test যোগ করা।

**২. P0 — Permission map-এ না থাকা sensitive controller অনুমতি পেয়ে যায়**

`CheckPermission` controller না চিনলে `null` ফেরত দেয় এবং request pass করে। Backend route group-এ সব route-এর জন্য আলাদা Admin-only restriction নেই। `BackupController`, `RecycleBinController`, `FeatureToggleController` এই fallback-এ পড়ে; পর্যালোচিত action-এ নিজস্ব authorization-ও নেই।

In-memory middleware probe-এ কোনো permission নেই এমন Staff-এর জন্য backup download, recycle-bin force-delete এবং feature toggle pass হয়েছে। একই probe-এর control case `ProductController@index` সঠিকভাবে 403 হয়েছে। Payment settings control-ও 403 হয়েছে; সেটিকে এই bypass finding-এর মধ্যে ধরা হয়নি।

প্রমাণ: [permission fallback](/home/agent47/Sites/b2bvikingERP/app/Http/Middleware/CheckPermission.php:183), [sensitive routes](/home/agent47/Sites/b2bvikingERP/routes/web.php:534), [backup download](/home/agent47/Sites/b2bvikingERP/app/Http/Controllers/Backend/BackupController.php:50)।

করণীয়: explicit route permission/policy; unknown backend action deny করা; অনুমতি ছাড়া staff-এর জন্য sensitive endpoint-এ negative authorization tests। Substring দিয়ে controller matching-ও exact mapping-এ বদলানো দরকার।

**৩. P0 — Existing-order PayPal callback order ownership ও captured amount যাচাই করে না**

Frontend callback query/session থেকে `order_id` নেয় এবং শুধু `Order::find()` করে। সংশ্লিষ্ট branch-এ current customer ownership, PayPal purchase-unit reference, captured amount ও currency-এর সঙ্গে order মিলিয়ে দেখা হয় না। `COMPLETED` response পেলেই স্থানীয় order-এর পুরো total paid হিসেবে লেখা হয়।

ফলে অন্য order বা ছোট অঙ্কের payment ভুল order-এ apply হওয়ার code path আছে। এটি live payment দিয়ে পরীক্ষা করা হয়নি, কিন্তু callback-এর প্রয়োজনীয় binding check অনুপস্থিত।

প্রমাণ: [existing-order callback](/home/agent47/Sites/b2bvikingERP/app/Http/Controllers/Frontend/CartController.php:852)।

করণীয়: payment attempt-এর সঙ্গে immutable order/customer/amount/currency binding; callback-এ সবগুলো যাচাই; due amount অনুযায়ী settlement; gateway capture ID-তে unique idempotency। একই branch-এ `OrderPayment` observer এবং order-level explicit journal posting দুটো থাকায় duplicate accounting posting-এর সম্ভাবনাও reconcile করতে হবে।

**৪. P0 — Public invoice payment capture-এর পরে local settlement ভেঙে যায়**

`InvoicePaymentController` এক argument দিয়ে `recordPayment()` ডাকে, অথচ service-এর required argument দুটি: `array $data, int $userId`। In-memory direct call-এ `ArgumentCountError` পুনরুৎপাদিত হয়েছে। Controller-এ `catch (Exception)` এই `Error` ধরবে না।

এই call-এর আগে gateway capture হয়। তাই ওই line পর্যন্ত execution পৌঁছালে বাইরে টাকা capture হওয়ার পর local transaction rollback হওয়ার ঝুঁকি আছে। এছাড়া allocations পাঠানো হচ্ছে `invoice_id => amount` map হিসেবে, অথচ service প্রত্যেক allocation-এ invoice ID ও amount-সহ array চায়।

Idempotency check PayPal order ID দিয়ে হলেও update-এ একই `external_reference` capture ID-তে বদলে যায়। পরবর্তী callback আগের key দিয়ে captured record খুঁজে পাবে না। এটি পুনরায় capture attempt/reconciliation failure ঘটাতে পারে; gateway অবশ্যই দ্বিতীয়বার charge করবে—এমন দাবি নয়।

প্রমাণ: [payment caller](/home/agent47/Sites/b2bvikingERP/app/Http/Controllers/Frontend/InvoicePaymentController.php:142), [service signature](/home/agent47/Sites/b2bvikingERP/app/Services/CustomerPaymentService.php:39), [reference overwrite](/home/agent47/Sites/b2bvikingERP/app/Http/Controllers/Frontend/InvoicePaymentController.php:124)।

করণীয়: service contract ও allocation format ঠিক করা; order ID/capture ID পৃথকভাবে রাখা; capture-success/local-failure recovery ও verified webhook/reconciliation flow তৈরি করা।

**৫. P0 — Delivery dispatch ভুল outlet-এর stock কমাতে পারে**

`dispatchDeliveryOrder()` stock খোঁজে শুধু product ও variant দিয়ে; outlet/bin বাদ যায়। Quantity কম হলেও exception না দিয়ে `max(0, stock - dispatch)` করে এবং পুরো dispatch quantity ledger-এ লেখে। এই পথে FIFO batch depletion-ও নেই।

In-memory reproduction:

| ধাপ | Outlet 1 stock | Outlet 2 stock | ফল |
|---|---:|---:|---|
| শুরু | 3 | 20 | Order outlet = 2; dispatch quantity = 10 |
| Dispatch-এর পরে | 0 | 20 | Delivery status = dispatched |
| Ledger | — | out_qty = 10, balance = 0 | Ledger outlet 2 বললেও stock কমেছে outlet 1-এ |

প্রমাণ: [dispatch service](/home/agent47/Sites/b2bvikingERP/app/Services/DeliveryOrderService.php:86)।

করণীয়: explicit fulfillment outlet/bin, locked stock selection, stock sufficiency validation, FIFO/COGS integration এবং একই delivery একবারই dispatch করার transactional guard।

**৬. P1 — GRN তৈরির সময় journal posting বাদ পড়ে**

`GoodsReceipt::create()`-এর `created` event-এ observer চলে। GRN items তৈরি হয় তার পরে। Observer তখন empty items থেকে amount শূন্য পায় এবং return করে। Stock receipt service-এ পরে journal posting-এর আলাদা call নেই।

Observer-এর calculation-এ landed cost-এর বদলে product purchase price ব্যবহৃত হয়েছে। Posting exception শুধু log করে থামানো হয়; ফলে অন্য observed path-এও business record থাকলেও journal missing হতে পারে।

প্রমাণ: [GRN creation ordering](/home/agent47/Sites/b2bvikingERP/app/Services/StockReceiveService.php:171), [observer calculation](/home/agent47/Sites/b2bvikingERP/app/Observers/AccountingObserver.php:39)।

করণীয়: items ও landed cost final হওয়ার পরে একই transaction-এ explicit journal posting; source/event-এর unique posting guard; failure হলে operation rollback অথবা স্পষ্ট pending-accounting state।

**৭. P1 — Clean database installation বর্তমানে ব্যর্থ**

আলাদা MySQL testing database-এ `PasswordUpdateTest`-এর প্রথম test setup-এই error পাওয়া গেছে:

`SQLSTATE[42S22]: Unknown column 'vendors.deleted_at' in 'where clause'`।

জুলাইয়ের migration বর্তমান `Vendor` Eloquent model দিয়ে query করে। বর্তমান model-এ SoftDeletes আছে; কিন্তু `deleted_at` column আসে সেপ্টেম্বরের migration-এ। তাই fresh installation-এ migration নিজেই এমন column ব্যবহার করছে যেটি তখনও তৈরি হয়নি।

প্রমাণ: [historical migration](/home/agent47/Sites/b2bvikingERP/database/migrations/2026_07_27_041317_refactor_vendors_currency_columns.php:24), [Vendor model](/home/agent47/Sites/b2bvikingERP/app/Models/Vendor.php:11), [later migration](/home/agent47/Sites/b2bvikingERP/database/migrations/2026_09_08_000002_add_soft_deletes_to_core_erp_tables.php:14)।

করণীয়: historical migration-এ current model/global scope-এর dependency বাদ দিয়ে query builder ব্যবহার; empty database migration CI-তে বাধ্যতামূলক করা।

**৮. P1 — GRN validation business invariants enforce করে না**

Request product/variant/bin-এর existence পরীক্ষা করে, কিন্তু সেগুলো নির্বাচিত PO/outlet/product-এর সঙ্গে সম্পর্কিত কি না, accepted + rejected = received কি না এবং অবশিষ্ট ordered quantity-এর বেশি receive হচ্ছে কি না—এই constraints পর্যালোচিত request/service-এ নেই। Approved PO UI dropdown-এ filter করা হলেও service-এ স্থানীয় PO-এর approval check নেই।

আরও mismatch: `receipt_date` required হলেও GRN create-এ সংরক্ষণ করা হয় না; view-এর `remarks`/QC check fields validated payload-এর সঙ্গে মেলে না। Request-এর `conditionally_accepted` enum সব ক্ষেত্রে schema-র `partial`-এ normalize হয় না।

প্রমাণ: [GRN request](/home/agent47/Sites/b2bvikingERP/app/Http/Requests/Procurement/StoreGoodsReceiptRequest.php:16), [GRN service](/home/agent47/Sites/b2bvikingERP/app/Services/StockReceiveService.php:133)।

করণীয়: PO/item ownership, receiving balance, quantity conservation, bin/outlet relation এবং QC state mapping transaction-এর মধ্যে enforce করা।

**৯. P1 — FIFO batch এবং physical bin একই scope-এ নেই**

Physical stock query-তে optional bin filter আছে; batch query-তে নেই। Bin দিলে অন্য bin-এর batch cost/quantity কমতে পারে। Bin না দিলে multiple stock row-এর মধ্যে শুধু প্রথমটি নেওয়া হয়, কিন্তু batch সংগ্রহ হয় outlet জুড়ে। Transfer service এই bin-less path ব্যবহার করে।

প্রমাণ: [FIFO queries](/home/agent47/Sites/b2bvikingERP/app/Services/Inventory/FifoDepletionService.php:42), [transfer dispatch](/home/agent47/Sites/b2bvikingERP/app/Services/Inventory/StockTransferService.php:79)।

করণীয়: depletion log, physical stock এবং batch একই location scope-এ update করা; multi-bin scenario-এর reconciliation test। Adjustment-এর ক্ষেত্রেও batch, catalog quantity এবং warehouse quantity সমন্বয় দরকার।

**১০. P1 — Deployment secret source code-এ; failed command-ও success response পায়**

Root ও public deploy script-এ fixed deploy secret আছে। Secret জানা থাকলে HTTP endpoint দিয়ে migration/cache operation চালানো যায়। এই রিপোর্টে secret value রাখা হয়নি। `runCmd()` exit code ফেরালেও deployment flow সেটি failure হিসেবে বিচার করে না; শেষে HTTP 200 এবং `status: success` আসে।

প্রমাণ: [deploy authentication](/home/agent47/Sites/b2bvikingERP/public/deploy.php:15), [commands](/home/agent47/Sites/b2bvikingERP/public/deploy.php:83), [response](/home/agent47/Sites/b2bvikingERP/public/deploy.php:119)।

করণীয়: secret rotate করে environment/secret store-এ রাখা; failed command-এ stop ও failure response; deployment trigger-এর explicit authentication, method ও concurrent-run guard। Source থেকে মুছলেই আগের secret নিরাপদ হয়ে যায় না।

**১১. P1 — Admin login-এ 2FA implementation ব্যবহার হচ্ছে না**

OTP service, controller ও route থাকলেও normal login-এর OTP send/logout/challenge অংশ comment করা। Password login-এর পরে সরাসরি dashboard redirect হয়।

প্রমাণ: [login flow](/home/agent47/Sites/b2bvikingERP/app/Http/Controllers/Auth/AuthenticatedSessionController.php:50)।

করণীয়: intended admin policy অনুযায়ী challenge enforce; mail unavailable হলে কী হবে তা স্পষ্ট করা; login, resend ও challenge-এর automated tests। শুধু 2FA screen আছে বলে feature সক্রিয় ধরা যাবে না।

**১২. P1 — Invoice PDFs public disk-এ predictable নামে লেখা হয়**

Invoice/PI/customer invoice PDF public disk-এ `invoice-{order_no}.pdf`-জাতীয় নামে থাকে। Standard public storage link চালু হলে controller authentication পাশ কাটিয়ে file URL থেকে download হওয়ার ঝুঁকি থাকে। স্থানীয় checkout-এ `public/storage` link পাওয়া যায়নি; deployment script সেটি তৈরি করে। তাই এটি deployment-dependent exposure, বর্তমানে live exposure প্রমাণ নয়।

প্রমাণ: [invoice PDF storage](/home/agent47/Sites/b2bvikingERP/app/Jobs/GeneratePdfJob.php:133), [deployment storage link](/home/agent47/Sites/b2bvikingERP/public/deploy.php:88)।

করণীয়: private storage এবং authenticated/expiring download; financial report download-এ owner/company access policy-ও নিশ্চিত করা।

**১৩. P1 — Multi-company accounting boundary অসম্পূর্ণ**

Chart of accounts, fiscal years ও journal model/schema-তে company field আছে। কিন্তু journal service fiscal year ও account code company filter ছাড়া খোঁজে; journal create-এও company context পাঠায় না। Multiple company ব্যবহার করলে অন্য company-এর closed year বা account selection প্রভাব ফেলতে পারে।

প্রমাণ: [journal service](/home/agent47/Sites/b2bvikingERP/app/Services/Accounting/JournalEntryService.php:36)।

করণীয়: company context বাধ্যতামূলক করা; account/fiscal-period/source/company matching; report-level isolation; দুই company-তে একই account code ও পৃথক fiscal year দিয়ে test।

**১৪. P2 — Document number generation concurrent request-এর জন্য যথেষ্ট সুরক্ষিত নয়**

`DocumentSequence::generateNext()` model পড়ে candidate তৈরি করে, পরে increment করে; sequence row read-এ lock নেই। Concurrent request একই পুরনো value থেকে candidate তৈরি করতে পারে। Exists check race পুরোপুরি দূর করে না। Unique constraint থাকলে duplicate data ঠেকলেও request fail হতে পারে।

প্রমাণ: [number generation](/home/agent47/Sites/b2bvikingERP/app/Models/DocumentSequence.php:64)।

করণীয়: sequence allocation lock/atomic mechanism; transaction ও bounded retry; concurrency test। `reset_policy` configuration-এর বাস্তব প্রয়োগও যাচাই দরকার।

**১৫. P2 — Route registration, cleanup এবং queue defaults-এ reliability gap**

- Route reflection-এ 53টি registered application action-এর controller method পাওয়া যায়নি। উদাহরণ: `UserController@show`, `SalesOrderController@edit`, `CartController@getVendor`। কিছু unused resource action হতে পারে; তবুও endpoint register হয়ে আছে। `only()`/`except()` দিয়ে অপ্রযোজ্য route সরানো বা প্রয়োজনীয় method implement করা দরকার।
- PDF cleanup-এ `now()->diffInHours(oldTimestamp) >= 24` ব্যবহার করা। Installed Carbon 3.13.1-এ 48 ঘণ্টা পুরোনো timestamp দিয়ে ফল `-48` হয়েছে; condition true হয় না। Date comparison/positive elapsed age ব্যবহার করা দরকার। [cleanup](/home/agent47/Sites/b2bvikingERP/routes/console.php:32)।
- Queue config-এর default retry-after 90 seconds, অথচ একাধিক job timeout 600–3600 seconds। একাধিক worker ও default configuration-এ দীর্ঘ job পুনরায় reserve হতে পারে। Worker timeout/retry-after/job duration একসঙ্গে configure করতে হবে। Actual production environment override পরীক্ষা করা হয়নি। [queue config](/home/agent47/Sites/b2bvikingERP/config/queue.php:42)।

**১৬. P2 — Backup restore fidelity নিশ্চিত নয়**

Custom backup exporter `is_numeric()` হলে value quote করে না। Numeric-looking string, যেমন leading-zero phone/SKU, restore-এর সময় বদলে যেতে পারে। Table dump-এ stable ordering বা consistent snapshot transaction-ও দেখা যায়নি; live write চললে cross-table consistency নিশ্চিত নয়।

প্রমাণ: [backup exporter](/home/agent47/Sites/b2bvikingERP/app/Services/Maintenance/BackupService.php:95)।

করণীয়: column-aware export বা উপযুক্ত database dump mechanism; নিয়মিত isolated restore test; leading-zero strings ও cross-table balance যাচাই। Backup file তৈরি হওয়া এবং usable restore হওয়া আলাদা acceptance criterion।

**আপনার খোলা `ReviewDataTable.php` সম্পর্কে নির্দিষ্ট পর্যবেক্ষণ**

এই ফাইলটির মূল কাজ server-side review listing, product/customer presentation, filters, publication switch ও action button। Eager loading এবং table text escaping ভালো আছে; ৮টি existing backend review test পাস করেছে।

তবে review module-এর বাকি অংশে নিচের কাজ প্রয়োজন:

| বিষয় | বর্তমান আচরণ | প্রয়োজন |
|---|---|---|
| Detail modal | Raw customer text HTML-এ বসে | উপরের P0 XSS fix |
| Moderation | Customer পুরোনো hidden review edit করলে `status = 1` হয় | Admin hide decision বজায় রাখা বা re-approval policy |
| Search | Product/customer/rating computed column searchable নয় | Product name, SKU, customer name দিয়ে explicit search |
| Pagination | DataTable-এ `All`; frontend reviews endpoint-এ unrestricted `get()` | Server-side limit ও pagination |
| Dashboard metrics | AJAX render-এও count/average/dropdown queries চলে | প্রয়োজনমতো aggregate/cache বা separate endpoint |
| Best-rated report | সব relevant product/review নিয়ে PHP-তে sort | Database aggregate/order/limit |
| Image URL | Raw HTML attribute-এ URL interpolation | Validated scheme এবং attribute escaping |
| Test gaps | Happy path আছে | XSS, hidden-review edit, permissions, large dataset tests |

প্রমাণ: [DataTable](/home/agent47/Sites/b2bvikingERP/app/DataTables/ReviewDataTable.php:23), [republish on edit](/home/agent47/Sites/b2bvikingERP/app/Http/Controllers/Backend/ReviewController.php:155), [unbounded reviews](/home/agent47/Sites/b2bvikingERP/app/Http/Controllers/Backend/ReviewController.php:221)।

Review endpoint-এর product lookup-এ storefront visibility policy reuse করা হয়নি। Restricted product-এর review/name customer দেখতে বা সেখানে review লিখতে পারবে কি না—B2B visibility requirements-এর সঙ্গে এটি মিলিয়ে enforce করা দরকার।

**পরীক্ষার ফল ও সীমা**

| পরীক্ষা | ফল |
|---|---|
| First-party PHP syntax | 791 ফাইল; syntax error নেই |
| Unit suite | 2 test, 2 assertion; pass |
| Backend ReviewManagementTest | 8 test, 29 assertion; pass; full suite চালানোর আগে বিদ্যমান test schema-তে |
| Composer validate | Pass; Dompdf-এর `*` constraint নিয়ে warning |
| Route/action reflection | 762 registered route; 53 missing application controller action |
| Permission probe | Permission-less Staff backup/recycle-bin/feature-toggle gate পার হয়েছে; Product ও PaymentSetting control 403 |
| Delivery stock probe | ভুল outlet stock deduction ও inconsistent ledger পুনরুৎপাদিত |
| Payment service call probe | এক argument-এ `ArgumentCountError` পুনরুৎপাদিত |
| Cleanup probe | 48 ঘণ্টা পুরোনো file-এর diff = -48 |
| Full test suite | 310 discovered; run সম্পূর্ণ হয়নি |
| Interrupted run | Progress output-এ 132টি case শেষ: 24 pass marker, 13 failure marker, 95 error marker |
| Isolated setup diagnosis | PasswordUpdateTest: 1 test, 0 assertion, 1 error; `vendors.deleted_at` migration failure |
| Frontend production build | চালানো হয়নি; `node_modules` নেই |

Full suite-এ repeated database lock waits ও setup-related error দেখা যাওয়ায় আমার চালানো process থামানো হয়েছে। ওই marker সংখ্যা completed PHPUnit summary নয়; 95টি পৃথক product defect-ও বোঝায় না। Migration ব্যর্থ হয়ে partial schema থাকায় downstream error বাড়ে। Test suite-এ `RefreshDatabase`, `DatabaseTransactions` এবং existing row-নির্ভর fixtures মিশ্রিত আছে; first-party test isolation ঠিক করে clean run আবার প্রয়োজন।

সব database test-এ explicit `b2bvikingerp_testing` ব্যবহার করা হয়েছে। `RefreshDatabase`-এর কারণে testing database পুনর্গঠিত হয়েছে এবং failed migration-এর পরে partial test schema থাকতে পারে। Application database target করা হয়নি। In-memory probes কোনো বাস্তব stock/payment বদলায়নি।

Diagnostic artifacts: `/tmp/b2bviking-audit-tests.log`, `/tmp/b2bviking-audit-first-error.log`, `/tmp/b2bviking-audit-probes.log`, `/tmp/b2bviking-stock-probe.log`। এগুলো temporary local artifacts; production data/report হিসেবে ব্যবহারযোগ্য নয়।

**Maintainability ও documentation**

`ProductsImport.php` 1,634 লাইন, frontend `CartController.php` 1,499 লাইন, backend `ReportController.php` 1,317 লাইন এবং `ProductController.php` 1,141 লাইন। এসব জায়গায় validation, calculation, stock, payment, notification ও presentation কাছাকাছি থাকায় change-এর প্রভাব বোঝা কঠিন। Money ও stock invariants shared services-এ একত্র করা বেশি জরুরি।

Views-এ প্রায় 87 হাজার লাইন; review modal-এ দেখা escaping mismatch inline JavaScript/HTML duplication-এর একটি বাস্তব উদাহরণ। Shared presentation component এবং structured frontend rendering এই class-এর সমস্যা কমাতে পারে।

README এখনো Laravel boilerplate। `docs/project_map.md`-এর পুরোনো service/model/controller সংখ্যা বর্তমান inventory-এর সঙ্গে মেলে না। Docs-এ কোনো feature-কে complete বললেই acceptance test পাস হয়েছে ধরে নেওয়া যাবে না। Root checkout-এ `.github` CI workflow পাওয়া যায়নি; অন্য provider-এ CI আছে কি না যাচাই করা হয়নি।

**কাজের প্রস্তাবিত ক্রম ও acceptance criteria**

| ধাপ | কাজ | সম্পন্ন হওয়ার প্রমাণ |
|---|---|---|
| ১ | Review XSS, sensitive route permissions, deploy secret | Browser text-safety test; unauthorized staff 403; rotated external secret |
| ২ | Payment ownership/amount binding, service contract, capture recovery | Fake gateway integration tests; duplicate callback নিরাপদ; capture-success/local-failure recovery |
| ৩ | Delivery/FIFO/GRN ও accounting alignment | Multi-outlet/bin tests; stock conservation; landed-cost journal; insufficient stock reject |
| ৪ | Historical migration ও test isolation | Empty MySQL database migration success; reproducible full test run |
| ৫ | Company isolation, document sequence, private PDFs | Cross-company negative tests; concurrent numbering; unauthorized download reject |
| ৬ | Queue/cleanup/backup/route cleanup | Long-job retry test; old-file cleanup; isolated restore; missing-action count শূন্য |
| ৭ | Requirement gap closure | POS/HR/integration-সহ প্রত্যেক agreed requirement-এর implementation ও acceptance evidence |

বর্তমান codebase উন্নয়ন চালিয়ে যাওয়ার মতো বিস্তৃত ভিত্তি দিয়েছে। পরবর্তী milestone হওয়া উচিত critical flow-গুলোকে নিরাপদ ও পুনরাবৃত্ত পরীক্ষায় নির্ভরযোগ্য করা। Security, stock, payment, accounting ও installation-এর এই সমস্যাগুলো সমাধান না হওয়া পর্যন্ত production readiness sign-off দেওয়া উচিত নয়।
