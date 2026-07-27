<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderApprovalController extends Controller
{
    public function approve(Request $request, Order $order, ApprovalService $approvalService)
    {
        $success = $approvalService->approveStep($order, Auth::id());
        
        if ($success) {
            toastr()->success('Order step approved successfully!');
        } else {
            toastr()->error('Failed to approve order step.');
        }

        return redirect()->back();
    }

    public function reject(Request $request, Order $order, ApprovalService $approvalService)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $success = $approvalService->rejectStep($order, Auth::id(), $request->reason);

        if ($success) {
            // Because order is rejected, mark its main status as cancelled.
            $order->status = 'cancelled';
            $order->save();

            toastr()->success('Order rejected successfully!');
        } else {
            toastr()->error('Failed to reject order.');
        }

        return redirect()->back();
    }
}
