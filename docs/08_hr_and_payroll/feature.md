# 👥 Spec: Human Resources (HR) & Danish Payroll Engine
**Module:** `08_hr_and_payroll`  
**Phase:** Phase 8 (HR & Danish Payroll Compliance)  
**Status:** Approved Specification  
**Document Standard:** Spec-Driven Development (SDD) Specification

---

## 1. 📌 Executive Summary & Regulatory Context

This module provides an integrated, audit-compliant Human Resources and Payroll engine designed for **Copenhagen Tourist Point** under **Danish Labor Law (Funktionærloven)** and **SKAT (Danish Tax Authority)** standards.

### Key Capabilities:
1. **Employee Master & Contracts**: Centralized employee profiles linked to existing `companies` and `departments` tables.
2. **GDPR-Compliant Security**: Sensitive personal identifiers (CPR Number, bank account details) encrypted at rest using AES-256 (`Illuminate\Support\Facades\Crypt`).
3. **Automated Danish Gross-to-Net Calculation Engine**:
   - **AM-bidrag (Arbejdsmarkedsbidrag)**: 8% deduction from gross earnings.
   - **A-skat (Income Tax)**: Progressive deduction using individual tax cards (Skattekort: Fradrag + Trækprocent).
   - **ATP (Arbejdsmarkedets Tillægspension)**: Standard Danish pension contribution.
   - **Feriepenge (Holiday Allowance)**: 12.5% accrual for hourly/flex workers.
4. **General Ledger Integration (Phase 5 COA)**: Automated double-entry salary expense and withholding tax journal vouchers.

---

## 2. 🏛️ System Topology & Data Relationships

```
┌────────────────────────────────────────────────────────────────────────┐
│                        COMPANIES & DEPARTMENTS                         │
│             (companies.id, departments.id, branches/outlets)           │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
       ┌────────────────────────────┴────────────────────────────┐
       ▼                                                         ▼
┌──────────────────────────────┐          ┌──────────────────────────────┐
│ EMPLOYEE MASTER & CONTRACTS  │          │ TIME, ATTENDANCE & LEAVE     │
│ (users, employees, contracts)│          │ (shifts, clock-in, leaves)   │
└──────────────┬───────────────┘          └──────────────┬───────────────┘
               │                                         │
               └────────────────────┬────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                      DANISH PAYROLL ENGINE                             │
│ • Gross Salary (Base + Overtime + Bonuses)                             │
│ • Minus AM-bidrag (8%)                                                 │
│ • Minus Personfradrag (Personal Allowance)                             │
│ • Minus A-skat (Tax percentage via SKAT tax card)                      │
│ • Minus ATP / Pension Contribution                                     │
│ = Net Salary Payout                                                    │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                   FINANCIAL ACCOUNTING (GL POSTING)                    │
│ • DR 5020 (Salaries & Wages Expense)                                   │
│ • DR 5021 (Employer ATP / Pension Expense)                             │
│ • CR 2030 (A-skat & AM-bidrag Payable to SKAT)                        │
│ • CR 2031 (Net Wages Payable / Bank Account 1020)                      │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 3. 🗄️ Database Schema & Data Modeling

### 3.1 `employees` (Staff Records & Danish Identifiers)
Extends the base `users` table:
```sql
CREATE TABLE employees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    department_id BIGINT UNSIGNED NOT NULL,
    outlet_id BIGINT UNSIGNED NULL, -- Optional branch assignment
    cpr_number_encrypted TEXT NOT NULL, -- AES-256 Encrypted CPR
    cpr_last4 VARCHAR(4) NOT NULL,      -- Unencrypted for quick search
    job_title VARCHAR(100) NOT NULL,
    employment_type ENUM('full_time', 'part_time', 'hourly', 'intern') DEFAULT 'full_time',
    joining_date DATE NOT NULL,
    termination_date DATE NULL,
    bank_reg_no VARCHAR(10) NOT NULL,   -- Danish Reg. Nr. (4 digits)
    bank_account_encrypted TEXT NOT NULL, -- Bank Account Number
    skat_tax_percentage DECIMAL(5,2) DEFAULT 38.00, -- Trækprocent (e.g. 38%)
    skat_monthly_deduction DECIMAL(10,2) DEFAULT 4000.00, -- Fradrag (e.g. kr. 4,000)
    use_secondary_tax_card BOOLEAN DEFAULT FALSE, -- Bikort vs Hovedkort
    status ENUM('active', 'on_leave', 'terminated') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (outlet_id) REFERENCES outlets(id)
);
```

### 3.2 `employee_contracts` (Salary Structure & Working Hours)
```sql
CREATE TABLE employee_contracts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    contract_type ENUM('salaried', 'hourly') DEFAULT 'salaried',
    base_salary_monthly DECIMAL(12,2) DEFAULT 0.00, -- For salaried staff
    hourly_rate DECIMAL(10,2) DEFAULT 0.00,         -- For hourly staff
    weekly_hours DECIMAL(5,2) DEFAULT 37.00,        -- Standard Danish 37 hrs/week
    pension_employee_percent DECIMAL(5,2) DEFAULT 0.00,
    pension_employer_percent DECIMAL(5,2) DEFAULT 0.00,
    effective_from DATE NOT NULL,
    effective_to DATE NULL,
    status ENUM('active', 'expired', 'superseded') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);
```

### 3.3 `payrolls` & `payroll_items` (Monthly Payroll Runs & Payslips)
```sql
CREATE TABLE payrolls (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payroll_period VARCHAR(7) NOT NULL, -- '2026-09'
    company_id BIGINT UNSIGNED NOT NULL,
    total_gross DECIMAL(14,2) DEFAULT 0.00,
    total_am_bidrag DECIMAL(14,2) DEFAULT 0.00,
    total_a_skat DECIMAL(14,2) DEFAULT 0.00,
    total_atp DECIMAL(14,2) DEFAULT 0.00,
    total_net DECIMAL(14,2) DEFAULT 0.00,
    status ENUM('draft', 'calculated', 'approved', 'paid') DEFAULT 'draft',
    journal_entry_id BIGINT UNSIGNED NULL, -- Links to Phase 5 General Ledger
    approved_by BIGINT UNSIGNED NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

CREATE TABLE payroll_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payroll_id BIGINT UNSIGNED NOT NULL,
    employee_id BIGINT UNSIGNED NOT NULL,
    base_pay DECIMAL(12,2) NOT NULL,
    overtime_pay DECIMAL(12,2) DEFAULT 0.00,
    bonus DECIMAL(12,2) DEFAULT 0.00,
    gross_salary DECIMAL(12,2) NOT NULL,
    am_bidrag DECIMAL(12,2) NOT NULL,      -- 8% of Gross
    taxable_income DECIMAL(12,2) NOT NULL, -- Gross - AM-bidrag
    applied_fradrag DECIMAL(12,2) NOT NULL,
    a_skat DECIMAL(12,2) NOT NULL,         -- (Taxable - Fradrag) * Trækprocent
    atp_employee DECIMAL(10,2) DEFAULT 99.00, -- Danish monthly ATP
    atp_employer DECIMAL(10,2) DEFAULT 198.00,
    pension_employee DECIMAL(12,2) DEFAULT 0.00,
    pension_employer DECIMAL(12,2) DEFAULT 0.00,
    net_salary DECIMAL(12,2) NOT NULL,
    payslip_pdf_path VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (payroll_id) REFERENCES payrolls(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
);
```

---

## 4. 🧮 Danish Payroll Calculation Engine Formula

$$\text{Gross Earnings} = \text{Base Salary} + \text{Overtime} + \text{Bonuses}$$
$$\text{AM-bidrag} = \text{Gross Earnings} \times 8\%$$
$$\text{Income after AM} = \text{Gross Earnings} - \text{AM-bidrag}$$
$$\text{Tax Base} = \max(0, \text{Income after AM} - \text{Personal Allowance (Fradrag)})$$
$$\text{A-skat} = \text{Tax Base} \times \text{Tax Card Rate (Trækprocent)}$$
$$\mathbf{Net\ Payout} = \text{Income after AM} - \text{A-skat} - \text{Employee ATP} - \text{Employee Pension}$$

---

## 5. 📑 General Ledger Journal Posting (Observer Flow)

Upon approval of the monthly payroll run (`status = 'approved'`), the system generates an automated journal via `JournalEntryService`:

$$\begin{aligned}
\text{DR}\quad & 5020\text{ (Salaries \& Wages Expense)} && \text{Total Gross Salary} \\
\text{DR}\quad & 5021\text{ (Employer Pension \& ATP Expense)} && \text{Total Employer ATP + Pension} \\
\text{CR}\quad & 2030\text{ (Withholding Tax / AM-bidrag / SKAT Payable)} && \text{Total AM-bidrag + A-skat} \\
\text{CR}\quad & 2031\text{ (ATP \& Pension Contributions Payable)} && \text{Total ATP + Pension} \\
\text{CR}\quad & 2032\text{ (Net Salaries Payable / Bank Clearing)} && \text{Total Net Wages}
\end{aligned}$$

When bank payment (`Nets / Betalingsservice`) executes:
$$\begin{aligned}
\text{DR}\quad & 2032\text{ (Net Salaries Payable)} && \text{Total Net Wages} \\
\text{CR}\quad & 1020\text{ (Bank Account)} && \text{Total Net Wages}
\end{aligned}$$

---

## 6. 🚀 Implementation Milestones

| Milestone | Deliverables | Target Timeline |
|---|---|---|
| **M1: Employee Master & Cryptography** | `employees`, `employee_contracts` schema with AES-256 encryption for CPR numbers. | 2 Days |
| **M2: Time, Leave & Attendance** | Leave application, approval workflow, and shift attendance tracking. | 2 Days |
| **M3: Danish Payroll Engine** | Automated 8% AM-bidrag, A-skat, ATP, and Net salary calculation engine. | 3 Days |
| **M4: Payslip PDF & Portal** | Responsive Danish payslip layout with 1-click PDF download for employees. | 2 Days |
| **M5: Accounting & General Ledger** | Automated double-entry GL journal posting with Phase 5 COA integration. | 2 Days |
| **M6: Bank Export (Nets / SEPA)** | Direct payment export file generation for Danish bank processing. | 2 Days |
