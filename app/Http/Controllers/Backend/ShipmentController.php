<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Purchase;
use App\DataTables\ShipmentDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Brian2694\Toastr\Facades\Toastr;

class ShipmentController extends Controller
{
    public function index(ShipmentDataTable $dataTable)
    {
        return $dataTable->render('backend.shipment.index');
    }

    public function create(Request $request)
    {
        $purchaseId = $request->get('purchase_id');
        $purchases = Purchase::whereIn('approval_status', ['approved'])
            ->orderBy('id', 'desc')
            ->get();

        return view('backend.shipment.create', compact('purchases', 'purchaseId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_id'       => 'required|exists:purchases,id',
            'vessel_or_flight'  => 'nullable|string|max:255',
            'container_no'      => 'nullable|string|max:100',
            'bl_awb_no'         => 'nullable|string|max:100',
            'port_of_loading'   => 'nullable|string|max:255',
            'port_of_discharge' => 'nullable|string|max:255',
            'etd'               => 'nullable|date',
            'eta'               => 'nullable|date',
            'document'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('shipments', 'public');
        }

        $shipment = Shipment::create([
            'purchase_id'       => $request->purchase_id,
            'vessel_or_flight'  => $request->vessel_or_flight,
            'container_no'      => $request->container_no,
            'bl_awb_no'         => $request->bl_awb_no,
            'port_of_loading'   => $request->port_of_loading,
            'port_of_discharge' => $request->port_of_discharge,
            'etd'               => $request->etd,
            'eta'               => $request->eta,
            'status'            => 'in_transit',
            'document_path'     => $documentPath,
        ]);

        // Update PO milestone_status to shipped if in_transit
        $purchase = Purchase::find($request->purchase_id);
        if ($purchase && in_array($purchase->milestone_status, ['lc_opened', 'po_sent', 'approved'])) {
            $purchase->update(['milestone_status' => 'shipped']);
        }

        Toastr::success('Shipment tracking registered successfully.', 'Success');
        return redirect()->route('admin.shipments.show', $shipment->id);
    }

    public function show($id)
    {
        $shipment = Shipment::with(['purchase.vendor', 'purchase.currency', 'purchase.items.product', 'shipmentCostEstimates'])->findOrFail($id);
        return view('backend.shipment.show', compact('shipment'));
    }

    public function edit($id)
    {
        $shipment = Shipment::findOrFail($id);

        if ($shipment->status === 'cancelled') {
            Toastr::error('Cancelled shipments cannot be edited.', 'Action Locked');
            return redirect()->back();
        }

        if ($shipment->goodsReceiptsCount() > 0) {
            Toastr::error('Shipment cannot be edited after Goods Receipt Note (GRN) has been created.', 'Action Locked');
            return redirect()->back();
        }

        return view('backend.shipment.edit', compact('shipment'));
    }

    public function update(Request $request, $id)
    {
        $shipment = Shipment::findOrFail($id);

        if ($shipment->status === 'cancelled') {
            Toastr::error('Cancelled shipments cannot be updated.', 'Action Locked');
            return redirect()->back();
        }

        if ($shipment->goodsReceiptsCount() > 0) {
            Toastr::error('Shipment cannot be updated after Goods Receipt Note (GRN) has been created.', 'Action Locked');
            return redirect()->back();
        }

        $request->validate([
            'vessel_or_flight'  => 'nullable|string|max:255',
            'container_no'      => 'nullable|string|max:100',
            'bl_awb_no'         => 'nullable|string|max:100',
            'port_of_loading'   => 'nullable|string|max:255',
            'port_of_discharge' => 'nullable|string|max:255',
            'etd'               => 'nullable|date',
            'eta'               => 'nullable|date',
            'document'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = [
            'vessel_or_flight'  => $request->vessel_or_flight,
            'container_no'      => $request->container_no,
            'bl_awb_no'         => $request->bl_awb_no,
            'port_of_loading'   => $request->port_of_loading,
            'port_of_discharge' => $request->port_of_discharge,
            'etd'               => $request->etd,
            'eta'               => $request->eta,
        ];

        if ($request->hasFile('document')) {
            if ($shipment->document_path && Storage::disk('public')->exists($shipment->document_path)) {
                Storage::disk('public')->delete($shipment->document_path);
            }
            $data['document_path'] = $request->file('document')->store('shipments', 'public');
        }

        $shipment->update($data);

        Toastr::success('Shipment details updated successfully.', 'Success');
        return redirect()->route('admin.shipments.show', $shipment->id);
    }

    public function updateStatus(Request $request, $id)
    {
        $shipment = Shipment::findOrFail($id);

        // Strict Enterprise Rule: Lock status changes if already Cancelled
        if ($shipment->status === 'cancelled') {
            Toastr::error('This shipment is permanently cancelled and its status cannot be changed.', 'Status Locked');
            return redirect()->back();
        }

        $request->validate([
            'status' => 'required|in:in_transit,arrived,cleared,cancelled',
        ]);

        $shipment->update(['status' => $request->status]);

        // If shipment is cancelled, rollback PO milestone_status if no GRNs exist
        if ($request->status === 'cancelled') {
            $purchase = Purchase::find($shipment->purchase_id);
            if ($purchase && $shipment->goodsReceiptsCount() == 0) {
                // Revert to lc_opened or approved
                $newMilestone = $purchase->letterOfCredit ? 'lc_opened' : ($purchase->proformaInvoice ? 'pi_attached' : 'approved');
                $purchase->update(['milestone_status' => $newMilestone]);
            }
            Toastr::warning('Shipment has been cancelled and PO milestone reverted.', 'Shipment Cancelled');
            return redirect()->back();
        }

        Toastr::success('Shipment status updated to ' . ucfirst(str_replace('_', ' ', $request->status)) . '.', 'Success');
        return redirect()->back();
    }

    public function destroy($id)
    {
        $shipment = Shipment::findOrFail($id);

        if ($shipment->goodsReceiptsCount() > 0) {
            Toastr::error('Shipment deletion prevented: Goods Receipts (GRNs) have already been generated for this shipment.', 'Deletion Blocked');
            return redirect()->back();
        }

        $shipment->delete();

        Toastr::success('Shipment record deleted successfully.', 'Success');
        return redirect()->route('admin.shipments.index');
    }
}
