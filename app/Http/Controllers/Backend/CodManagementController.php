<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\CodCollection;
use App\Models\User;
use App\Services\Payment\CodService;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;

class CodManagementController extends Controller
{
    protected CodService $codService;

    public function __construct(CodService $codService)
    {
        $this->codService = $codService;
    }

    /**
     * Display all COD deliveries and cash collections.
     */
    public function index(Request $request)
    {
        $query = CodCollection::with(['order', 'invoice', 'driver', 'cashier', 'depositAccount'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('collection_no', 'like', "%{$s}%")
                  ->orWhereHas('invoice', fn($iq) => $iq->where('invoice_no', 'like', "%{$s}%"))
                  ->orWhereHas('order', fn($oq) => $oq->where('order_no', 'like', "%{$s}%"));
            });
        }

        $collections = $query->paginate(20);

        // Top KPI metrics
        $totalExpected = (float) CodCollection::sum('expected_amount');
        $totalInTransit = (float) CodCollection::whereIn('status', ['out_for_delivery', 'collected'])->sum('expected_amount');
        $totalHandedOver = (float) CodCollection::where('status', 'handed_over')->sum('collected_amount');
        $pendingDispatchCount = CodCollection::where('status', 'pending_dispatch')->count();

        // Drivers / Couriers
        try {
            $drivers = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Delivery Driver', 'Admin', 'Staff']);
            })->get();
        } catch (\Throwable $e) {
            $drivers = collect();
        }

        if ($drivers->isEmpty()) {
            $drivers = User::limit(50)->get();
        }

        // Cash Accounts
        $cashAccounts = ChartOfAccount::where('account_type', 'asset')
            ->where(function ($q) {
                $q->where('account_code', 'like', '1010%')
                  ->orWhere('account_code', 'like', '1020%');
            })
            ->get();

        return view('backend.payments.cod.index', compact(
            'collections',
            'totalExpected',
            'totalInTransit',
            'totalHandedOver',
            'pendingDispatchCount',
            'drivers',
            'cashAccounts'
        ));
    }

    /**
     * Mark COD item as out for delivery.
     */
    public function markOutForDelivery(Request $request, $id)
    {
        $request->validate([
            'driver_id'    => 'nullable|exists:users,id',
            'courier_name' => 'nullable|string|max:128',
        ]);

        try {
            $cod = CodCollection::findOrFail($id);
            $this->codService->markOutForDelivery($cod, $request->input('driver_id'), $request->input('courier_name'));

            Toastr::success("COD #{$cod->collection_no} marked as out for delivery!");
        } catch (Exception $e) {
            Toastr::error($e->getMessage());
        }

        return redirect()->back();
    }

    /**
     * Driver marks cash collected from customer.
     */
    public function markCollected(Request $request, $id)
    {
        $request->validate([
            'collected_amount' => 'required|numeric|min:0',
            'notes'            => 'nullable|string|max:500',
        ]);

        try {
            $cod = CodCollection::findOrFail($id);
            $this->codService->markCollected(
                $cod,
                (float)$request->input('collected_amount'),
                $request->input('notes')
            );

            Toastr::success("COD #{$cod->collection_no} marked as collected by driver!");
        } catch (Exception $e) {
            Toastr::error($e->getMessage());
        }

        return redirect()->back();
    }

    /**
     * Cashier confirms physical cash envelope handover and posts GL journal.
     */
    public function settleHandover(Request $request, $id)
    {
        $request->validate([
            'cash_received'      => 'required|numeric|min:0.01',
            'deposit_account_id' => 'nullable|exists:chart_of_accounts,id',
            'notes'              => 'nullable|string|max:500',
        ]);

        try {
            $cod = CodCollection::findOrFail($id);

            $this->codService->settleHandover(
                $cod,
                (float)$request->input('cash_received'),
                auth()->id() ?? 1,
                $request->input('deposit_account_id')
            );

            Toastr::success("Physical cash of kr. " . number_format($request->input('cash_received'), 2) . " received, invoice settled, and GL journal posted!");
        } catch (Exception $e) {
            Toastr::error("Settlement Failed: " . $e->getMessage());
        }

        return redirect()->back();
    }
}
