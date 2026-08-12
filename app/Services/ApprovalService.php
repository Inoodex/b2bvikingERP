<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalService
{
    /**
     * Submit any model (ProductRequest, Purchase, StockTransfer, etc.) for Approval.
     */
    public function submitForApproval(Model $model, float $amount = 0.0): bool
    {
        $modelType = get_class($model);

        $workflow = ApprovalWorkflow::active()
            ->where('model_type', $modelType)
            ->where(function ($q) use ($amount) {
                $q->where('min_amount', '<=', $amount)
                  ->orWhereNull('min_amount');
            })
            ->where(function ($q) use ($amount) {
                $q->where('max_amount', '>=', $amount)
                  ->orWhereNull('max_amount');
            })
            ->with('steps')
            ->first();

        if (!$workflow || $workflow->steps->isEmpty()) {
            // No approval workflow required; auto-approve
            if (isset($model->approval_status)) {
                $model->approval_status = 'approved';
                $model->save();
            }
            return true;
        }

        // Set status to pending
        if (isset($model->approval_status)) {
            $model->approval_status = 'pending';
            $model->save();
        }

        // Create first step approval entry if not exists
        $firstStep = $workflow->steps->first();
        Approval::firstOrCreate([
            'approvable_type' => $modelType,
            'approvable_id' => $model->id,
            'approval_step_id' => $firstStep->id,
        ], [
            'status' => 'pending',
        ]);

        return true;
    }

    /**
     * Check if a specific user can approve the current pending step of a model.
     */
    public function canUserApproveCurrentStep(Model $model, ?\App\Models\User $user = null): bool
    {
        if (!$user) {
            $user = \Illuminate\Support\Facades\Auth::user();
        }

        if (!$user) {
            return false;
        }

        // OPTIONAL ADMIN OVERRIDE (Commented out for strict dynamic workflow enforcement):
        // if ($user->hasRole('Admin') || $user->hasRole('Super Admin') || $user->hasRole('Root Super Admin')) {
        //     return true;
        // }

        $modelType = get_class($model);
        $currentApproval = Approval::where('approvable_type', $modelType)
            ->where('approvable_id', $model->id)
            ->where('status', 'pending')
            ->first();

        if (!$currentApproval || !$currentApproval->approval_step_id) {
            return false;
        }

        $step = ApprovalStep::find($currentApproval->approval_step_id);
        if (!$step) {
            return false;
        }

        if ($step->approver_user_id && (int)$step->approver_user_id === (int)$user->id) {
            return true;
        }

        if ($step->approver_role_id) {
            $role = \Spatie\Permission\Models\Role::find($step->approver_role_id);
            if ($role && $user->hasRole($role->name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Approve the current step for the model.
     */
    public function approveStep(Model $model, int $userId, ?string $comments = null): bool
    {
        $user = \App\Models\User::find($userId);
        if (!$user || !$this->canUserApproveCurrentStep($model, $user)) {
            Log::warning("Unauthorized approval attempt by User #{$userId} on " . get_class($model) . " #{$model->id}");
            return false;
        }

        $modelType = get_class($model);

        DB::beginTransaction();
        try {
            // Find current pending approval entry
            $currentApproval = Approval::where('approvable_type', $modelType)
                ->where('approvable_id', $model->id)
                ->where('status', 'pending')
                ->first();

            if (!$currentApproval) {
                // If no entry exists, find workflow and create next
                $amount = (float) ($model->total_amount ?? $model->subtotal_amount ?? 0);
                $this->submitForApproval($model, $amount);
                $currentApproval = Approval::where('approvable_type', $modelType)
                    ->where('approvable_id', $model->id)
                    ->where('status', 'pending')
                    ->first();
            }

            if ($currentApproval) {
                $currentApproval->update([
                    'user_id' => $userId,
                    'status' => 'approved',
                    'comments' => $comments,
                ]);

                // Check if there are further steps in workflow
                $currentStep = ApprovalStep::find($currentApproval->approval_step_id);
                $nextStep = ApprovalStep::where('approval_workflow_id', $currentStep->approval_workflow_id)
                    ->where('step_order', '>', $currentStep->step_order)
                    ->orderBy('step_order')
                    ->first();

                if ($nextStep) {
                    // Create pending entry for next step
                    Approval::create([
                        'approvable_type' => $modelType,
                        'approvable_id' => $model->id,
                        'approval_step_id' => $nextStep->id,
                        'status' => 'pending',
                    ]);

                    if (isset($model->approval_status)) {
                        $model->approval_status = 'level1_approved';
                        $model->save();
                    }
                } else {
                    // All steps completed -> Fully Approved
                    if (isset($model->approval_status)) {
                        $model->approval_status = 'approved';
                        $model->save();
                    }
                }
            } else {
                if (isset($model->approval_status)) {
                    $model->approval_status = 'approved';
                    $model->save();
                }
            }

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Approval error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject step for the model.
     */
    public function rejectStep(Model $model, int $userId, ?string $reason = null): bool
    {
        $user = \App\Models\User::find($userId);
        if (!$user || !$this->canUserApproveCurrentStep($model, $user)) {
            Log::warning("Unauthorized rejection attempt by User #{$userId} on " . get_class($model) . " #{$model->id}");
            return false;
        }

        $modelType = get_class($model);

        DB::beginTransaction();
        try {
            $currentApproval = Approval::where('approvable_type', $modelType)
                ->where('approvable_id', $model->id)
                ->where('status', 'pending')
                ->first();

            if ($currentApproval) {
                $currentApproval->update([
                    'user_id' => $userId,
                    'status' => 'rejected',
                    'comments' => $reason,
                ]);
            }

            if (isset($model->approval_status)) {
                $model->approval_status = 'rejected';
                $model->save();
            }

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Rejection error: ' . $e->getMessage());
            return false;
        }
    }
}
