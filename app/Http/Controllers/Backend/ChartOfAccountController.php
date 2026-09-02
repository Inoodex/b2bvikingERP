<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ChartOfAccountDataTable;
use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function index(ChartOfAccountDataTable $dataTable)
    {
        $groupAccounts = ChartOfAccount::where('is_group', true)->where('is_active', true)->orderBy('account_code')->get();

        // Compute 4 Core Financial KPI Metrics
        $allAccounts = ChartOfAccount::with('journalLines')->where('is_active', true)->get();
        $totalAssets = (float) $allAccounts->where('account_type', 'asset')->sum('balance');
        $totalLiabilities = (float) $allAccounts->where('account_type', 'liability')->sum('balance');
        $totalEquity = (float) $allAccounts->where('account_type', 'equity')->sum('balance');
        $activeAccountsCount = $allAccounts->count();

        // Build hierarchical tree for Collapsible Tree View
        $treeAccounts = ChartOfAccount::with(['children.children.journalLines', 'children.journalLines', 'journalLines'])
            ->whereNull('parent_id')
            ->orderBy('account_code')
            ->get();

        return $dataTable->render('backend.accounts.chart_of_accounts.index', compact(
            'groupAccounts',
            'totalAssets',
            'totalLiabilities',
            'totalEquity',
            'activeAccountsCount',
            'treeAccounts'
        ));
    }

    public function create()
    {
        $groupAccounts = ChartOfAccount::where('is_group', true)->where('is_active', true)->orderBy('account_code')->get();
        return view('backend.accounts.chart_of_accounts.create', compact('groupAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_code'   => 'required|string|max:20|unique:chart_of_accounts,account_code',
            'account_name'   => 'required|string|max:255',
            'account_type'   => 'required|in:asset,liability,equity,revenue,expense',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id'      => 'nullable|exists:chart_of_accounts,id',
            'is_group'       => 'nullable|boolean',
        ]);

        ChartOfAccount::create([
            'account_code'   => $request->account_code,
            'account_name'   => $request->account_name,
            'account_type'   => $request->account_type,
            'normal_balance' => $request->normal_balance,
            'parent_id'      => $request->parent_id,
            'is_group'       => $request->boolean('is_group'),
            'is_active'      => true,
        ]);

        Toastr::success('Account Head created successfully.', 'Success');
        return redirect()->route('admin.chart-of-accounts.index');
    }

    public function edit(ChartOfAccount $chartOfAccount)
    {
        $groupAccounts = ChartOfAccount::where('is_group', true)->where('id', '!=', $chartOfAccount->id)->orderBy('account_code')->get();
        return view('backend.accounts.chart_of_accounts.edit', compact('chartOfAccount', 'groupAccounts'));
    }

    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $request->validate([
            'account_code'   => 'required|string|max:20|unique:chart_of_accounts,account_code,' . $chartOfAccount->id,
            'account_name'   => 'required|string|max:255',
            'account_type'   => 'required|in:asset,liability,equity,revenue,expense',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id'      => 'nullable|exists:chart_of_accounts,id',
            'is_active'      => 'nullable|boolean',
        ]);

        // Protect system root account classification from being corrupted
        if ($chartOfAccount->isSystemProtected() && $chartOfAccount->account_code !== $request->account_code) {
            Toastr::error('System core account code cannot be modified.', 'Action Denied');
            return redirect()->back();
        }

        $chartOfAccount->update([
            'account_code'   => $request->account_code,
            'account_name'   => $request->account_name,
            'account_type'   => $request->account_type,
            'normal_balance' => $request->normal_balance,
            'parent_id'      => $request->parent_id,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        Toastr::success('Account Head updated successfully.', 'Success');
        return redirect()->route('admin.chart-of-accounts.index');
    }

    public function destroy(ChartOfAccount $chartOfAccount)
    {
        if ($chartOfAccount->isSystemProtected()) {
            return response()->json(['status' => 'error', 'message' => 'System protected account cannot be deleted.'], 403);
        }

        if ($chartOfAccount->journalLines()->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Account has active journal postings and cannot be deleted.'], 400);
        }

        if ($chartOfAccount->children()->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Account has child sub-accounts and cannot be deleted.'], 400);
        }

        $chartOfAccount->delete();
        return response()->json(['status' => 'success', 'message' => 'Account head deleted successfully.']);
    }
}
