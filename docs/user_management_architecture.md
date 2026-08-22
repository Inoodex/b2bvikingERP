# 👥 User Management Architecture
**B2B Viking ERP — Copenhagen Tourist Point**
**Document Version:** 1.0
**Last Updated:** August 2026
**Status:** Living Document

---

## 1. System Overview — তিন ধরনের User Identity

আপনার ব্যবসায় তিনটি সম্পূর্ণ আলাদা user identity আছে। প্রতিটির login channel, data structure, এবং system access আলাদা।

```mermaid
graph TD
    SYSTEM["🏛️ B2B Viking ERP\nCopenhagen Tourist Point"]

    SYSTEM --> A["👨‍💼 Internal Staff\nusers table\nAdmin · Manager · Cashier · Warehouse"]
    SYSTEM --> B["🏢 B2B Wholesale Customer\ncustomers table\nCorporate Buyers · Distributors"]
    SYSTEM --> C["🛒 POS Retail Customer\npos_customers table\nWalk-in Tourist · Loyalty Member"]

    A --> A1["🔐 Backend ERP Login\nadmin.b2bviking.com"]
    B --> B1["🌐 B2B Portal Login\nb2bviking.com/account"]
    C --> C1["🏪 No Login Required\nPOS Terminal — Phone or Loyalty Card"]

    A1 --> A2["Purchase · Stock · Reports\nApprovals · Settings"]
    B1 --> B2["Place Orders · View Invoices\nTrack Deliveries · Quotations"]
    C1 --> C2["Walk-in Sale\nLoyalty Points · eWallet"]
```

---

## 2. Current State — এখন System যেভাবে আছে

### 2.1 Live Database (Production: b2bvikingerp)

| Metric | Value |
|:---|:---:|
| Total Users in `users` table | **33** |
| Total Roles | **6** |
| Total Permissions | **57** |
| Users with Spatie Role assigned | **33 (100%)** |

### 2.2 Current Role Structure

| Role Name | Permissions | Type |
|:---|:---:|:---|
| Admin | 57 | Internal Staff ✅ |
| Admin - Order Receive & Despatch | 12 | Internal Staff ✅ |
| Manager | 11 | Internal Staff ✅ |
| User | 5 | ⚠️ Ambiguous — B2B Customer? |
| Outlet User | 3 | Internal Staff (Cashier) ✅ |
| Staff | 3 | Internal Staff ✅ |

### 2.3 Current Architecture Flowchart

```mermaid
flowchart TD
    subgraph CURRENT["বর্তমান অবস্থা — একটিই users টেবিল সবার জন্য"]
        direction LR
        ADMIN_U["Admin User\nadmin@b2bviking.com\nrole_id=1 | outlet_id=NULL"]
        OUTLET_U["Outlet User\nostergade10@gmail.com\nrole_id=3 | outlet_id=27"]
        B2B_U["B2B Customer\ncustomer@company.dk\nrole_id=2 | credit_limit=?"]
        BROKEN["Test Outlate\ntest@gmail.com\nrole_id=3 | outlet_id=NULL"]
    end

    subgraph PROBLEMS["বর্তমান সমস্যা"]
        P1["department_id, company_id,\noutlet_id SAVE হচ্ছে না\nUserService Bug"]
        P2["Admin User এর\ncustomer_segment = retail\nData Inconsistency"]
        P3["B2B Customer এবং\nInternal Staff একই table-এ\nSchema Pollution"]
        P4["User role এ 5টি permission\nফলে isStaff ভুল করে"]
    end

    ADMIN_U --> P2
    OUTLET_U --> P1
    B2B_U --> P3
    B2B_U --> P4
    BROKEN --> P1
```

---

## 3. Target Architecture — সঠিক Design

### 3.1 Database Entity Relationship

```mermaid
erDiagram
    USERS {
        bigint id PK
        string name
        string email
        string password
        enum user_type "internal_staff"
        bigint role_id FK
        bigint outlet_id FK "nullable"
        bigint department_id FK "nullable"
        bigint company_id FK "nullable"
        string designation
        tinyint status
    }

    CUSTOMERS {
        bigint id PK
        string company_name
        string contact_person
        string email
        string password
        string vat_number
        enum customer_segment "wholesale, b2b_vip, distributor"
        decimal credit_limit
        bigint pricelist_id FK "nullable"
        string payment_terms
        tinyint status
    }

    POS_CUSTOMERS {
        bigint id PK
        string name
        string phone
        string email "nullable"
        string loyalty_card_number "nullable"
        decimal loyalty_points
        decimal ewallet_balance
        bigint preferred_outlet_id FK "nullable"
        boolean is_anonymous
    }

    ROLES {
        bigint id PK
        string name
        string guard_name
    }

    OUTLETS {
        bigint id PK
        string name
        enum type "warehouse, retail, wholesale"
        bigint manager_id FK
    }

    USERS ||--o{ ROLES : "Spatie role assigned"
    USERS }o--|| OUTLETS : "assigned to outlet"
    POS_CUSTOMERS }o--|| OUTLETS : "preferred outlet"
```

### 3.2 Login Flow — কে কোথায় Login করে

```mermaid
flowchart LR
    subgraph PORTALS["Login Portals"]
        ERP["ERP Backend\nadmin.b2bviking.com"]
        B2B["B2B Portal\nb2bviking.com"]
        POS["POS Terminal\nBrowser-based Outlet"]
    end

    subgraph AUTH["Authentication Guard"]
        G1["Guard: web\nAuth::user()"]
        G2["Guard: customer\nAuth::guard('customer')"]
        G3["POS Session\nCashier Login"]
    end

    subgraph DATA["Data Source"]
        T1["users table\nInternal Staff"]
        T2["customers table\nB2B Corporate Buyers"]
        T3["pos_customers table\nIdentified not authenticated"]
    end

    ERP --> G1 --> T1
    B2B --> G2 --> T2
    POS --> G3 --> T1
    POS -.->|"Customer search\nby phone or loyalty card"| T3
```

---

## 4. Spatie RBAC — Role এবং Permission Map

### 4.1 Role Hierarchy

```mermaid
graph TD
    subgraph INTERNAL["Internal Staff Roles — Spatie RBAC"]
        R1["Admin\n57 permissions\nসব দেখে সব করে"]
        R2["Manager\n11 permissions\nApprovals Reports Team"]
        R3["Admin Order Dispatch\n12 permissions\nOrders DO Dispatch"]
        R4["Outlet User — POS Cashier\n3 permissions\nPOS Stock View Receipt"]
        R5["Staff\n3 permissions\nLimited view only"]
    end

    subgraph PERMISSIONS["Permission Groups"]
        PG1["purchase\nক্রয়"]
        PG2["sales\nবিক্রয়"]
        PG3["inventory\nস্টক"]
        PG4["accounts\nহিসাব"]
        PG5["reports\nরিপোর্ট"]
        PG6["master\nSetup"]
        PG7["pos\nPOS Terminal"]
    end

    R1 --> PG1 & PG2 & PG3 & PG4 & PG5 & PG6 & PG7
    R2 --> PG2 & PG3 & PG5
    R3 --> PG2 & PG3
    R4 --> PG7
    R5 --> PG3
```

### 4.2 Outlet Scoping — কে কতটুকু দেখবে

```mermaid
flowchart TD
    LOGIN["User Login করল"]
    LOGIN --> CHECK["Role Check"]

    CHECK -->|"Admin"| FULL["সব Outlet এর সব Data দেখবে\n20 Outlets + Main Warehouse"]
    CHECK -->|"Manager"| ASSIGNED["Assigned Outlet এর Data দেখবে"]
    CHECK -->|"Outlet User — Cashier"| OWN["শুধু নিজের Outlet এর Data\nPOS Screen only"]
    CHECK -->|"Staff"| LIMITED["Assigned Outlet এর Limited View"]

    FULL --> HQ["Main Warehouse + সব 20টি Outlet"]
    ASSIGNED --> OUTLET_DATA["outlet_id অনুযায়ী filter"]
    OWN --> POS_DATA["POS Screen — অন্য outlet নয়"]
    LIMITED --> STOCK_VIEW["Stock View only"]
```

---

## 5. User Create Flow — নতুন User কীভাবে তৈরি হয়

```mermaid
flowchart TD
    START["Admin — Create User Form"]
    START --> ROLE["Role Select করো\nSpatie Role থেকে"]

    ROLE -->|"permissions_count > 0\nStaff/Manager/Cashier"| STAFF_FORM["Internal Enterprise Form দেখাও"]
    ROLE -->|"permissions_count = 0\nCustomer Role"| CUST_FORM["B2B Customer Form দেখাও"]

    STAFF_FORM --> SF1["Company Select — optional"]
    STAFF_FORM --> SF2["Department Select — optional"]
    STAFF_FORM --> SF3["Outlet Select — required for Cashier"]

    CUST_FORM --> CF1["Customer Segment\nwholesale / b2b_vip"]
    CUST_FORM --> CF2["Credit Limit"]
    CUST_FORM --> CF3["Discount Override"]

    SF1 & SF2 & SF3 --> SAVE_STAFF["UserService::createUser\noutlet_id, dept_id, company_id সব save"]
    CF1 & CF2 & CF3 --> SAVE_CUST["UserService::createUser\nB2B fields save"]

    SAVE_STAFF --> ASSIGN["Spatie Role assign\nassignRole()"]
    SAVE_CUST --> ASSIGN

    ASSIGN --> DONE["User Created — Audit Log"]
```

---

## 6. POS Outlet Flow — Outlet এ কীভাবে কাজ হবে

```mermaid
flowchart TD
    subgraph HQ["Head Office — Main Warehouse"]
        ADMIN_CREATE["Admin নতুন Cashier User তৈরি করে"]
        ASSIGN_OUTLET["outlet_id = 27 Ostergade\nrole = Outlet User POS Cashier\nPermissions: pos.sell, pos.receipt, stock.view"]
    end

    subgraph OUTLET["Ostergade Outlet — POS Terminal"]
        CASHIER_LOGIN["Cashier Login করে"]
        SCOPE["System outlet_id=27 scope apply করে"]
        POS_SCREEN["POS Screen — শুধু outlet 27 এর stock"]
        SALE["Tourist এর কাছে বিক্রি করে"]
        RECEIPT["Receipt Print বা Email"]
        STOCK_UPDATE["Stock কমে — outlet 27 inventory"]
    end

    subgraph WALK_IN["Walk-in Customer"]
        ANON["Anonymous Sale\nকোনো registration লাগে না"]
        LOYALTY["Loyalty Customer\nPhone দিয়ে identify\npos_customers table"]
    end

    ADMIN_CREATE --> ASSIGN_OUTLET
    ASSIGN_OUTLET --> CASHIER_LOGIN
    CASHIER_LOGIN --> SCOPE
    SCOPE --> POS_SCREEN
    POS_SCREEN --> SALE
    SALE --> ANON & LOYALTY
    ANON & LOYALTY --> RECEIPT
    RECEIPT --> STOCK_UPDATE
    STOCK_UPDATE -.->|"Real-time sync"| HQ
```

---

## 7. Who Can Manage Users — Control Matrix

```mermaid
flowchart TD
    subgraph WHO["কে কী করতে পারে?"]
        SUPER["Super Admin HQ"]
        MGR["Manager"]
        CASHIER["Outlet Cashier"]
        CUSTOMER["B2B Customer"]
    end

    SUPER -->|"পারে"| CAN1["যেকোনো User তৈরি\nযেকোনো Role assign\nযেকোনো Outlet-এ assign"]

    MGR -->|"পারবে না"| CANT1["User Create করতে পারবে না"]
    MGR -->|"পারবে"| CAN2["নিজের outlet Cashier এর Password Reset"]

    CASHIER -->|"পারবে না"| CANT2["শুধু POS চালাবে\nUser management নেই"]

    CUSTOMER -->|"পারবে না"| CANT3["ERP Backend-এ Access নেই"]
```

---

## 8. Implementation Roadmap

### Phase A — Critical Bug Fix (এখনই)

| Task | File | Status |
|:---|:---|:---:|
| `outlet_id`, `dept_id`, `company_id` save করা | `UserService.php` | 🔴 Pending |
| Validation rules add করা | `StoreUserRequest.php` + `UpdateUserRequest.php` | 🔴 Pending |
| `user_type` enum column | New Migration | 🔴 Pending |
| `"User"` role এর 5 permissions clean করা | Database | 🔴 Pending |

### Phase B — B2B Customer Separation

| Task | Description | Status |
|:---|:---|:---:|
| `customers` table migration | Schema design | 🟡 Planned |
| B2B Customer data migrate | Script | 🟡 Planned |
| B2B Portal Auth Guard | `config/auth.php` | 🟡 Planned |
| Sales dropdown update | `SalesQuotationController` | 🟡 Planned |

### Phase C — POS Module (20 Outlets)

| Task | Description | Status |
|:---|:---|:---:|
| `pos_customers` table migration | Schema | ⚪ Future |
| `POS Cashier` Spatie role | Permission seeder | ⚪ Future |
| POS Terminal UI | Browser-based 20 outlets | ⚪ Future |
| Offline mode | Service Worker | ⚪ Future |

---

## 9. Quick Reference Summary

| Component | বর্তমান অবস্থা | Target Design | Priority |
|:---|:---|:---|:---:|
| Internal Staff storage | `users` table ✅ | `users` table ✅ | Done |
| B2B Customer storage | `users` table ⚠️ | `customers` table | Phase B |
| POS Customer storage | নেই ❌ | `pos_customers` table | Phase C |
| `outlet_id` save হওয়া | হচ্ছে না ❌ | UserService fix | 🔴 Phase A |
| `department_id` save হওয়া | হচ্ছে না ❌ | UserService fix | 🔴 Phase A |
| `company_id` save হওয়া | হচ্ছে না ❌ | UserService fix | 🔴 Phase A |
| `user_type` explicit column | নেই ❌ | Migration | 🔴 Phase A |
| Outlet-level user create | Central only ✅ | Central only ✅ | Done |
| POS Cashier role | নেই ❌ | Spatie role | Phase C |
| Spatie RBAC core | কাজ করছে ✅ | কাজ করছে ✅ | Done |
| Sidebar permission guards | কাজ করছে ✅ | কাজ করছে ✅ | Done |
