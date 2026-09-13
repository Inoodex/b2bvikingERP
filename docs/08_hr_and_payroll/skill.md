# 🧠 Skill: Danish Gross-to-Net Payroll & Employee Lifecycle Engine
**Category:** `08_hr_and_payroll`  
**Standard:** Enterprise Execution Playbook (Danish Labor Law & SKAT Compliance)  

---

## 1. Danish Gross-to-Net Payroll Calculation Engine

When generating a monthly salary disbursement for employees under Danish tax jurisdiction:

### Mathematical Steps:
```text
INPUT: Employee $employee, float $baseMonthlySalary, float $overtimePay = 0, float $bonusPay = 0

1. Calculate Total Gross Earnings:
   $grossEarnings = $baseMonthlySalary + $overtimePay + $bonusPay;

2. Calculate AM-bidrag (Arbejdsmarkedsbidrag - Mandatory 8%):
   // Under Danish Tax Law, 8% is withheld before income tax calculations
   $amBidrag = round($grossEarnings * 0.08, 2);
   $incomeAfterAm = $grossEarnings - $amBidrag;

3. Apply SKAT Tax Card Deductions (Skattekort):
   // Check if employee uses Primary Card (Hovedkort) or Secondary Card (Bikort)
   IF $employee->use_secondary_tax_card:
      // Bikort has zero monthly fradrag (allowance)
      $appliedFradrag = 0.00;
   ELSE:
      $monthlyFradrag = (float) $employee->skat_monthly_deduction; // e.g. kr. 4,350
      $appliedFradrag = min($incomeAfterAm, $monthlyFradrag);

   $taxableBase = max(0, $incomeAfterAm - $appliedFradrag);

4. Calculate A-skat (Personal Withholding Tax):
   $taxRate = (float) ($employee->skat_tax_percentage ?? 38.00) / 100;
   $aSkat = round($taxableBase * $taxRate, 2);

5. Calculate ATP (Arbejdsmarkedets Tillægspension):
   // Standard monthly ATP for full-time employment (>= 117 hrs/month)
   IF $employee->employment_type === 'full_time':
      $atpEmployee = 99.00;   // Deducted from employee
      $atpEmployer = 198.00;  // Paid additionally by company
   ELSE:
      // Proportionate ATP based on logged hours
      $atpEmployee = round(($employee->logged_monthly_hours / 160.33) * 99.00, 2);
      $atpEmployer = round($atpEmployee * 2, 2);

6. Calculate Pension Contributions (if configured in contract):
   $contract = $employee->activeContract;
   $pensionEmployee = round($grossEarnings * (($contract->pension_employee_percent ?? 0) / 100), 2);
   $pensionEmployer = round($grossEarnings * (($contract->pension_employer_percent ?? 0) / 100), 2);

7. Calculate Final Net Salary Payout:
   $netPayout = round($incomeAfterAm - $aSkat - $atpEmployee - $pensionEmployee, 2);

8. RETURN Calculation Summary:
   [
      'gross_salary'      => $grossEarnings,
      'am_bidrag'         => $amBidrag,
      'applied_fradrag'   => $appliedFradrag,
      'taxable_base'      => $taxableBase,
      'a_skat'            => $aSkat,
      'atp_employee'      => $atpEmployee,
      'atp_employer'      => $atpEmployer,
      'pension_employee'  => $pensionEmployee,
      'pension_employer'  => $pensionEmployer,
      'net_salary'        => $netPayout,
      'total_company_cost'=> $grossEarnings + $atpEmployer + $pensionEmployer
   ];
```

---

## 2. GDPR-Compliant CPR & Bank Encryption/Decryption Security Routine

In compliance with Danish Data Protection Authority (Datatilsynet) and EU GDPR:

### 2.1 Encryption on Storage:
```text
INPUT: string $rawCpr (Format: DDMMYY-XXXX), string $rawBankAccount

1. Validate CPR Format:
   IF !preg_match('/^\d{6}-?\d{4}$/', $rawCpr):
      THROW ValidationException("Invalid Danish CPR number format.");

2. Strip dashes for clean storage:
   $cleanCpr = str_replace('-', '', $rawCpr);

3. AES-256 Encryption:
   $encryptedCpr = Crypt::encryptString($cleanCpr);
   $last4 = substr($cleanCpr, -4); // Retain unencrypted last 4 digits for UI masking

4. Store into database:
   $employee->update([
      'cpr_number_encrypted' => $encryptedCpr,
      'cpr_last4'            => $last4,
      'bank_account_encrypted'=> Crypt::encryptString($rawBankAccount)
   ]);
```

### 2.2 Masked Display in UI:
```text
// Never display full CPR in open HTML tables:
$maskedCpr = "******-" . $employee->cpr_last4; // e.g. "******-1234"
```

---

## 3. Automated General Ledger Payroll Journal Posting Routine

When a monthly payroll run is approved by the HR Manager / CFO:

### Journal Voucher Posting Algorithm:
```text
INPUT: Payroll $payroll

1. START DB::transaction()

2. Enforce Payroll Status 'calculated':
   IF $payroll->status !== 'calculated':
      THROW InvalidOperationException("Payroll must be calculated before posting.");

3. Calculate Aggregate Line Amounts across all employee payslips:
   $totalGross        = $payroll->items->sum('gross_salary');
   $totalCompanyATP   = $payroll->items->sum('atp_employer');
   $totalCompanyPension = $payroll->items->sum('pension_employer');
   $totalSkatPayable  = $payroll->items->sum('am_bidrag') + $payroll->items->sum('a_skat');
   $totalAtpPayable   = $payroll->items->sum('atp_employee') + $totalCompanyATP;
   $totalPensionPayable = $payroll->items->sum('pension_employee') + $totalCompanyPension;
   $totalNetPayable   = $payroll->items->sum('net_salary');

4. Execute Journal Voucher:
   $journalService = app(JournalEntryService::class);
   $journal = $journalService->postJournal(
      'Monthly Payroll Disbursement',
      $payroll,
      [
         // Debits (Expenses to Company)
         ['account_code' => '5020', 'debit' => $totalGross, 'credit' => 0], // Salaries & Wages
         ['account_code' => '5021', 'debit' => $totalCompanyATP, 'credit' => 0], // Employer ATP Expense
         ['account_code' => '5022', 'debit' => $totalCompanyPension, 'credit' => 0], // Employer Pension

         // Credits (Liabilities & Net Payables)
         ['account_code' => '2030', 'debit' => 0, 'credit' => $totalSkatPayable], // SKAT (A-skat + AM)
         ['account_code' => '2031', 'debit' => 0, 'credit' => $totalAtpPayable], // ATP Pension Fund
         ['account_code' => '2032', 'debit' => 0, 'credit' => $totalPensionPayable], // Pension Provider
         ['account_code' => '2033', 'debit' => 0, 'credit' => $totalNetPayable], // Net Wages Payable
      ],
      $payroll->payment_date ?? now()->toDateString(),
      "Salary Journal for Period {$payroll->payroll_period}"
   );

5. Update Payroll Header:
   $payroll->update([
      'status' => 'approved',
      'journal_entry_id' => $journal->id,
      'approved_by' => Auth::id()
   ]);

6. COMMIT DB::transaction()
```

---

## 4. Hourly Feriepenge (Holiday Allowance) Accrual Engine

Under the Danish Holiday Act (Ferieloven):

### Calculation Routine:
```text
INPUT: Employee $employee, float $hourlyEarnings

1. Hourly / non-salaried staff earn 12.5% holiday pay:
   $feriepengeAccrued = round($hourlyEarnings * 0.125, 2);

2. Record in employee holiday balance:
   $employee->increment('accrued_holiday_pay', $feriepengeAccrued);
   $employee->increment('accrued_holiday_days', (2.08 / 160.33) * $workedHours); // 2.08 days/month

3. Monthly reporting export sent to FerieKonto / eIndkomst.
```
