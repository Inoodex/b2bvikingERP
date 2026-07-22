<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ApprovalWorkflowDataTable;
use App\Http\Controllers\Controller;
use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class ApprovalWorkflowController extends Controller
{
    public function index(ApprovalWorkflowDataTable $dataTable)
    {
        return $dataTable->render('backend.master.approval_workflows.index');
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $users = User::where('status', 1)->orderBy('name')->get();
        $models = [
            'App\Models\ProductRequest' => 'Requisition (SR / PR)',
            'App\Models\Purchase' => 'Purchase Order (PO)',
            'App\Models\StockTransfer' => 'Internal Stock Transfer',
        ];
        return view('backend.master.approval_workflows.create', compact('roles', 'users', 'models'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'model_type' => 'required|string',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'status' => 'required|boolean',
            'steps' => 'required|array|min:1',
            'steps.*.step_name' => 'required|string|max:255',
            'steps.*.approver_role_id' => 'nullable|exists:roles,id',
            'steps.*.approver_user_id' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $workflow = ApprovalWorkflow::create([
                'name' => $request->name,
                'model_type' => $request->model_type,
                'min_amount' => $request->min_amount,
                'max_amount' => $request->max_amount,
                'status' => $request->status,
            ]);

            foreach ($request->steps as $order => $stepData) {
                ApprovalStep::create([
                    'approval_workflow_id' => $workflow->id,
                    'step_name' => $stepData['step_name'],
                    'step_order' => $order + 1,
                    'approver_role_id' => $stepData['approver_role_id'] ?? null,
                    'approver_user_id' => $stepData['approver_user_id'] ?? null,
                ]);
            }

            DB::commit();
            Toastr::success('Approval Workflow Created Successfully!');
            return redirect()->route('admin.master.approval-workflows.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('Failed to create workflow: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function edit(string $id)
    {
        $workflow = ApprovalWorkflow::with('steps')->findOrFail($id);
        $roles = Role::orderBy('name')->get();
        $users = User::where('status', 1)->orderBy('name')->get();
        $models = [
            'App\Models\ProductRequest' => 'Requisition (SR / PR)',
            'App\Models\Purchase' => 'Purchase Order (PO)',
            'App\Models\StockTransfer' => 'Internal Stock Transfer',
        ];
        return view('backend.master.approval_workflows.edit', compact('workflow', 'roles', 'users', 'models'));
    }

    public function update(Request $request, string $id)
    {
        $workflow = ApprovalWorkflow::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'model_type' => 'required|string',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'status' => 'required|boolean',
            'steps' => 'required|array|min:1',
            'steps.*.step_name' => 'required|string|max:255',
            'steps.*.approver_role_id' => 'nullable|exists:roles,id',
            'steps.*.approver_user_id' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $workflow->update([
                'name' => $request->name,
                'model_type' => $request->model_type,
                'min_amount' => $request->min_amount,
                'max_amount' => $request->max_amount,
                'status' => $request->status,
            ]);

            $workflow->steps()->delete();

            foreach ($request->steps as $order => $stepData) {
                ApprovalStep::create([
                    'approval_workflow_id' => $workflow->id,
                    'step_name' => $stepData['step_name'],
                    'step_order' => $order + 1,
                    'approver_role_id' => $stepData['approver_role_id'] ?? null,
                    'approver_user_id' => $stepData['approver_user_id'] ?? null,
                ]);
            }

            DB::commit();
            Toastr::success('Approval Workflow Updated Successfully!');
            return redirect()->route('admin.master.approval-workflows.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('Failed to update workflow: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(string $id)
    {
        $workflow = ApprovalWorkflow::findOrFail($id);
        $workflow->steps()->delete();
        $workflow->delete();
        return response(['status' => 'success', 'message' => 'Workflow Deleted Successfully!']);
    }
}
