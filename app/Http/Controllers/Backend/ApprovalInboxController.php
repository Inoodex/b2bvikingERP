<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\FundTransfer;
use App\Services\Accounting\JournalEntryService;
use App\Services\ApprovalService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalInboxController extends Controller
{
    protected ApprovalService $approvalService;
    protected JournalEntryService $journalService;

    public function __construct(ApprovalService $approvalService, JournalEntryService $journalService)
    {
        $this->approvalService = $approvalService;
        $this->journalService = $journalService;
    }

    /**
     * Display centralized Pending Approvals Inbox for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRoleIds = $user ? $user->roles->pluck('id')->toArray() : [];
        $isSuperAdmin = $user && ($user->hasRole('Admin') || $user->hasRole('Super Admin') || $user->hasRole('Root Super Admin'));

        $query = Approval::with(['step.workflow', 'user'])
            ->where('status', 'pending')
            ->whereHas('step', function ($q) use ($user, $userRoleIds, $isSuperAdmin) {
                if (!$isSuperAdmin) {
                    $q->where(function ($sub) use ($user, $userRoleIds) {
                        $sub->where('approver_user_id', $user->id)
                            ->orWhereIn('approver_role_id', $userRoleIds);
                    });
                }
            })
            ->latest();

        // Optional filter by category
        $category = $request->query('category', 'all');
        if ($category === 'procurement') {
            $query->whereIn('approvable_type', [
                'App\Models\ProductRequest',
                'App\Models\ComparisonStatement',
                'App\Models\Purchase',
                'App\Models\LetterOfCredit',
                'App\Models\VendorReturn',
            ]);
        } elseif ($category === 'inventory') {
            $query->whereIn('approvable_type', [
                'App\Models\StockTransfer',
            ]);
        } elseif ($category === 'sales') {
            $query->whereIn('approvable_type', [
                'App\Models\Order',
            ]);
        } elseif ($category === 'accounts') {
            $query->whereIn('approvable_type', [
                'App\Models\VendorBill',
                'App\Models\FundTransfer',
            ]);
        }

        $approvals = $query->paginate(20);

        // Counts for badge tabs
        $baseCountQuery = Approval::where('status', 'pending')
            ->whereHas('step', function ($q) use ($user, $userRoleIds, $isSuperAdmin) {
                if (!$isSuperAdmin) {
                    $q->where(function ($sub) use ($user, $userRoleIds) {
                        $sub->where('approver_user_id', $user->id)
                            ->orWhereIn('approver_role_id', $userRoleIds);
                    });
                }
            });

        $counts = [
            'all' => (clone $baseCountQuery)->count(),
            'procurement' => (clone $baseCountQuery)->whereIn('approvable_type', [
                'App\Models\ProductRequest',
                'App\Models\ComparisonStatement',
                'App\Models\Purchase',
                'App\Models\LetterOfCredit',
                'App\Models\VendorReturn',
            ])->count(),
            'inventory' => (clone $baseCountQuery)->whereIn('approvable_type', [
                'App\Models\StockTransfer',
            ])->count(),
            'sales' => (clone $baseCountQuery)->whereIn('approvable_type', [
                'App\Models\Order',
            ])->count(),
            'accounts' => (clone $baseCountQuery)->whereIn('approvable_type', [
                'App\Models\VendorBill',
                'App\Models\FundTransfer',
            ])->count(),
        ];

        return view('backend.approvals.index', compact('approvals', 'counts', 'category'));
    }

    /**
     * Approve a pending approval step.
     */
    public function approve(Request $request, $id)
    {
        $approval = Approval::with('step')->findOrFail($id);
        $model = $approval->approvable;

        if (!$model) {
            Toastr::error('Target document not found.');
            return redirect()->back();
        }

        $comments = $request->input('comments');
        $success = $this->approvalService->approveStep($model, Auth::id(), $comments);

        if ($success) {
            // Check if model is a FundTransfer and just reached fully approved status
            if ($model instanceof FundTransfer && $model->approval_status === 'approved') {
                $this->executeApprovedFundTransfer($model);
            }

            Toastr::success('Document approval step approved successfully!');
        } else {
            Toastr::error('You are not authorized to approve this step, or an error occurred.');
        }

        return redirect()->back();
    }

    /**
     * Reject a pending approval step.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $approval = Approval::with('step')->findOrFail($id);
        $model = $approval->approvable;

        if (!$model) {
            Toastr::error('Target document not found.');
            return redirect()->back();
        }

        $success = $this->approvalService->rejectStep($model, Auth::id(), $request->input('reason'));

        if ($success) {
            Toastr::warning('Document step has been rejected.');
        } else {
            Toastr::error('You are not authorized to reject this step, or an error occurred.');
        }

        return redirect()->back();
    }

    /**
     * Execute balance deduction and Contra GL posting once Fund Transfer is fully approved.
     */
    protected function executeApprovedFundTransfer(FundTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            $fromAccount = $transfer->fromAccount;
            $toAccount = $transfer->toAccount;
            $amount = (float) $transfer->amount;

            if ($fromAccount && $toAccount) {
                $fromAccount->decrement('current_balance', $amount);
                $toAccount->increment('current_balance', $amount);

                $fromGlCode = $fromAccount->glAccount ? $fromAccount->glAccount->account_code : '1020';
                $toGlCode = $toAccount->glAccount ? $toAccount->glAccount->account_code : '1020';

                $lines = [
                    ['account_code' => $toGlCode, 'debit' => $amount, 'credit' => 0],
                    ['account_code' => $fromGlCode, 'debit' => 0, 'credit' => $amount],
                ];

                $this->journalService->recordEntry(
                    event: 'fund_transfer',
                    sourceModel: $transfer,
                    lines: $lines,
                    entryDate: $transfer->transfer_date ? $transfer->transfer_date->toDateString() : now()->toDateString(),
                    narration: "Approved Contra Fund Transfer: kr. {$amount} from {$fromAccount->account_name} to {$toAccount->account_name}"
                );
            }
        });
    }
}
