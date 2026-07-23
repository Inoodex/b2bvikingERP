<?php

namespace App\Services\ApprovalWorkflow;

use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApprovalWorkflowService
{
    public function createWorkflow(array $data): ApprovalWorkflow
    {
        return DB::transaction(function () use ($data) {
            $workflow = ApprovalWorkflow::create([
                'name' => $data['name'],
                'document_type' => Str::snake(class_basename($data['model_type'])),
                'model_type' => $data['model_type'],
                'min_amount' => $data['min_amount'] ?? 0,
                'max_amount' => $data['max_amount'] ?? null,
                'status' => $data['status'],
            ]);

            $this->createSteps($workflow, $data['steps']);

            return $workflow;
        });
    }

    public function updateWorkflow(ApprovalWorkflow $workflow, array $data): ApprovalWorkflow
    {
        return DB::transaction(function () use ($workflow, $data) {
            $workflow->update([
                'name' => $data['name'],
                'document_type' => Str::snake(class_basename($data['model_type'])),
                'model_type' => $data['model_type'],
                'min_amount' => $data['min_amount'] ?? 0,
                'max_amount' => $data['max_amount'] ?? null,
                'status' => $data['status'],
            ]);

            $workflow->steps()->delete();
            $this->createSteps($workflow, $data['steps']);

            return $workflow;
        });
    }

    private function createSteps(ApprovalWorkflow $workflow, array $steps): void
    {
        foreach ($steps as $order => $stepData) {
            ApprovalStep::create([
                'approval_workflow_id' => $workflow->id,
                'step_name' => $stepData['step_name'],
                'step_order' => $order + 1,
                'approver_role_id' => $stepData['approver_role_id'] ?? null,
                'approver_user_id' => $stepData['approver_user_id'] ?? null,
            ]);
        }
    }
}
