<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ApprovalWorkflowDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalWorkflow\StoreApprovalWorkflowRequest;
use App\Http\Requests\ApprovalWorkflow\UpdateApprovalWorkflowRequest;
use App\Models\ApprovalWorkflow;
use App\Models\User;
use App\Services\ApprovalWorkflow\ApprovalWorkflowService;
use Brian2694\Toastr\Facades\Toastr;
use Spatie\Permission\Models\Role;

class ApprovalWorkflowController extends Controller
{
    protected $workflowService;

    public function __construct(ApprovalWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

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
            'App\Models\ComparisonStatement' => 'Comparison Statement (CS)',
            'App\Models\Purchase' => 'Purchase Order (PO)',
            'App\Models\LetterOfCredit' => 'Import Letter of Credit (LC)',
            'App\Models\VendorReturn' => 'Vendor Return / Debit Note',
            'App\Models\StockTransfer' => 'Internal Stock Transfer',
        ];
        return view('backend.master.approval_workflows.create', compact('roles', 'users', 'models'));
    }

    public function store(StoreApprovalWorkflowRequest $request)
    {
        try {
            $this->workflowService->createWorkflow($request->validated());
            Toastr::success('Approval Workflow Created Successfully!');
            return redirect()->route('admin.master.approval-workflows.index');
        } catch (\Throwable $e) {
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
            'App\Models\ComparisonStatement' => 'Comparison Statement (CS)',
            'App\Models\Purchase' => 'Purchase Order (PO)',
            'App\Models\LetterOfCredit' => 'Import Letter of Credit (LC)',
            'App\Models\VendorReturn' => 'Vendor Return / Debit Note',
            'App\Models\StockTransfer' => 'Internal Stock Transfer',
        ];
        return view('backend.master.approval_workflows.edit', compact('workflow', 'roles', 'users', 'models'));
    }

    public function update(UpdateApprovalWorkflowRequest $request, string $id)
    {
        try {
            $workflow = ApprovalWorkflow::findOrFail($id);
            $this->workflowService->updateWorkflow($workflow, $request->validated());
            
            Toastr::success('Approval Workflow Updated Successfully!');
            return redirect()->route('admin.master.approval-workflows.index');
        } catch (\Throwable $e) {
            Toastr::error('Failed to update workflow: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(string $id)
    {
        try {
            $workflow = ApprovalWorkflow::findOrFail($id);
            $workflow->steps()->delete();
            $workflow->delete();
            return response(['status' => 'success', 'message' => 'Workflow Deleted Successfully!']);
        } catch (\Throwable $e) {
            return response(['status' => 'error', 'message' => 'Failed to delete workflow.']);
        }
    }
}
