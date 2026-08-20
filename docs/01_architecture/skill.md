# 🧠 Skill: Polymorphic Approval Engine & Architecture Patterns
**Category:** `01_architecture`  
**Standard:** Enterprise Execution Playbook

---

## 1. Polymorphic Approval Execution Algorithm

When an approvable model (`Purchase`, `Order`, `StockTransfer`, `StockAdjustment`, `ProductRequest`) executes approval logic:

### Algorithm Steps:
```text
INPUT: Model $approvable, int $userId, string $action ('approve' | 'reject'), string $comments

1. START DB::transaction()
2. LOCK $approvable FOR UPDATE
3. Fetch active ApprovalWorkflow where:
   - document_type matches $approvable->getMorphClass()
   - company_id == $approvable->company_id (or default)
   - min_amount <= $approvable->total_amount <= max_amount
   - status == 1 (active)

4. IF no workflow found:
   - Auto-approve $approvable (approval_status = 'approved')
   - COMMIT and RETURN true

5. Fetch current pending ApprovalStep:
   - SELECT step FROM approval_steps WHERE approval_workflow_id = $workflow->id
   - ORDER BY step_order ASC
   - Filter out steps already approved in approvals table for this $approvable

6. Verify $userId has authorization for current pending step:
   - Check if $step->user_id == $userId OR $user->hasRole($step->role_id)
   - IF unauthorized -> THROW UnauthorizedApprovalException

7. IF $action == 'reject':
   - Create Approval entry (status = 'rejected', comments = $comments, approved_at = now())
   - Set $approvable->approval_status = 'rejected'
   - Dispatch DocumentRejectedNotification to creator
   - COMMIT and RETURN true

8. IF $action == 'approve':
   - Create Approval entry (status = 'approved', comments = $comments, approved_at = now())
   - Check if NEXT step exists in $workflow->steps:
     a) IF NEXT step exists:
        - Set $approvable->approval_status = 'in_review'
        - Dispatch NextApproverPendingNotification to next step's role/user
     b) IF NO MORE steps:
        - Set $approvable->approval_status = 'approved'
        - Execute PostApprovalTrigger (e.g., PO Ready for Vendor Email / DO Ready for Dispatch)
        - Dispatch DocumentFullyApprovedNotification to creator
   - COMMIT and RETURN true
```

---

## 2. Multi-Company & Branch Isolation Query Scope

Every tenant-sensitive model must apply a global/local tenant scope:

```php
// Invariant: Non-admin users are strictly scoped to their assigned outlet/company
public function scopeForUserOutlet(Builder $query, User $user): Builder
{
    if ($user->hasRole('Admin') || $user->hasRole('Super Admin')) {
        return $query; // Full visibility for global headquarters
    }

    return $query->where('outlet_id', $user->outlet_id);
}
```

---

## 3. Atomic Document Number Generation Pattern

All document codes (`PO-XXXX`, `SO-XXXX`, `DO-XXXX`, `GRN-XXXX`, `TR-XXXX`) must use atomic sequence locking to avoid duplicates in concurrent environments:

```php
// Formula: Prefix-YYYYMMDD-XXXX (4-digit padded daily increment)
$prefix = 'DO';
$today = now()->format('Ymd');
$seq = DocumentSequence::where('prefix', $prefix)
    ->whereDate('date', now()->toDateString())
    ->lockForUpdate()
    ->firstOrCreate(
        ['prefix' => $prefix, 'date' => now()->toDateString()],
        ['last_number' => 0]
    );

$seq->increment('last_number');
$documentNo = sprintf('%s-%s-%04d', $prefix, $today, $seq->last_number);
```
