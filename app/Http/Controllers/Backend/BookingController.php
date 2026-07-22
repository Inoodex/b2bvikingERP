<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\BookingDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\BookingStoreRequest;
use App\Http\Requests\Booking\BookingUpdateRequest;
use App\Mail\AdminBookingNotificationMail;
use App\Models\Booking;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Unit;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\BookingNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Support\PdfImageHelper;
use App\Exports\BookingsExport;
use App\Exports\BookingOrderExport;
use Maatwebsite\Excel\Facades\Excel;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(BookingDataTable $dataTable)
    {
        return $dataTable->render('backend.booking.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $vendors = Vendor::where('status', 1)->latest()->get();
        $units = Unit::where('status', 1)->get();
        $categories = Category::where('status', 1)->get();
        
        $selectedIds = [];
        if ($request->has('ids')) {
            $selectedIds = explode(',', $request->ids);
        }

        // Pass products with details for JS population
        $products = Product::where('status', 1)->with(['variants.color', 'variants.size', 'category', 'subCategory', 'childCategory', 'unit'])->withSum('inventoryStocks', 'quantity')->latest()->get(); 
        return view('backend.booking.create', compact('vendors', 'products', 'units', 'categories', 'selectedIds'));
    }

    /**
     * Get sub categories based on category (AJAX).
     */
    public function getSubCategories(Request $request)
    {
        $subCategories = SubCategory::where('category_id', $request->id)->where('status', 1)->get();
        return response()->json($subCategories);
    }

    /**
     * Get child categories based on sub category (AJAX).
     */
    public function getChildCategories(Request $request)
    {
        $childCategories = ChildCategory::where('sub_category_id', $request->id)->where('status', 1)->get();
        return response()->json($childCategories);
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(BookingStoreRequest $request)
    // {
    //     $booking_no = 'DS-' . strtoupper(Str::random(10));
    //     $bookings_saved = [];

    //     foreach ($request->items as $item) {
    //         $booking = new Booking();
    //         $booking->booking_no = $booking_no;
    //         $booking->vendor_id = $request->vendor_id;
    //         $booking->product_id = $item['product_id'];
            
    //         // Fetch product to get category/unit defaults
    //         $product = Product::find($item['product_id']);
    //         if (!$product) continue;

    //         $booking->category_id = $product->category_id;
    //         $booking->sub_category_id = $product->sub_category_id;
    //         $booking->child_category_id = $product->child_category_id;
    //         $booking->unit_id = $item['unit_id'] ?? $product->unit_id;
            
    //         $booking->qty = $item['qty'];
            
    //         // Handle Variant Info
    //         if (isset($item['variant_quantities']) && is_array($item['variant_quantities']) && count(array_filter($item['variant_quantities'])) > 0) {
    //             $variantSum = 0;
    //             $variantsData = [];
    //             foreach ($item['variant_quantities'] as $variant => $qty) {
    //                 if ($qty > 0) {
    //                     $variantSum += $qty;
    //                     $variantsData[$variant] = $qty;
    //                 }
    //             }
    //             $booking->variant_info = $variantsData;
    //             if ($booking->qty < $variantSum) {
    //                 $booking->qty = $variantSum;
    //             }
    //         } else {
    //             $booking->variant_info = $item['variant_info'] ?? null;
    //         }

    //         $booking->description = $request->description;
    //         $booking->custom_fields = $request->custom_fields;
    //         $booking->shipping_method = $request->shipping_method;
    //         $booking->status = $request->status ?? 'pending';
            
    //         $booking->unit_price = 0;
    //         $booking->extra_cost = 0;
    //         $booking->total_cost = 0;
    //         $booking->sale_price = 0;
            
    //         $booking->save();
    //         $bookings_saved[] = $booking;
    //     }

    //     if (count($bookings_saved) > 0) {
    //         // Clear the booking cart after successful booking
    //         Cart::where('user_id', Auth::id())
    //              ->where('cart_type', 'booking')
    //              ->delete();
            
    //         $vendor = Vendor::find($request->vendor_id);
    //         if ($vendor && $vendor->email) {
    //             dispatch(function () use ($bookings_saved, $vendor) {
    //                 Mail::to($vendor->email)->send(new BookingNotification($bookings_saved[0]));
    //             })->afterResponse();
    //         }
    //     }

    //     Toastr::success('Order(s) Placed Successfully!');
    //     session()->flash('clear_booking_basket', true);
    //     return redirect()->route('admin.bookings.index');
    // }

    public function store(BookingStoreRequest $request)
{
    $booking_no = $this->generateBookingNumber();
    $bookings_saved = [];

    foreach ($request->items as $item) {
        $booking = new Booking();
        $booking->booking_no = $booking_no;
        $booking->vendor_id = $request->vendor_id;
        $booking->product_id = $item['product_id'];
        
        // Fetch product to get category/unit defaults
        $product = Product::find($item['product_id']);
        if (!$product) continue;

        $booking->category_id = $product->category_id;
        $booking->sub_category_id = $product->sub_category_id;
        $booking->child_category_id = $product->child_category_id;
        $booking->unit_id = $item['unit_id'] ?? $product->unit_id;
        
        $booking->qty = $item['qty'];
        
        // Handle Variant Info
        if (isset($item['variant_quantities']) && is_array($item['variant_quantities']) && count(array_filter($item['variant_quantities'])) > 0) {
            $variantSum = 0;
            $variantsData = [];
            foreach ($item['variant_quantities'] as $variant => $qty) {
                if ($qty > 0) {
                    $variantSum += $qty;
                    $variantsData[$variant] = $qty;
                }
            }
            $booking->variant_info = $variantsData;
            if ($booking->qty < $variantSum) {
                $booking->qty = $variantSum;
            }
        } else {
            $booking->variant_info = $item['variant_info'] ?? null;
        }

        $booking->description = $request->description;
        $booking->custom_fields = $request->custom_fields;
        $booking->shipping_method = $request->shipping_method;
        $booking->status = $request->status ?? 'pending';
        
        $booking->unit_price = 0;
        $booking->extra_cost = 0;
        $booking->total_cost = 0;
        $booking->sale_price = 0;
        
        $booking->save();
        $bookings_saved[] = $booking;
    }

    if (count($bookings_saved) > 0) {
        // Clear the booking cart after successful booking
        Cart::where('user_id', Auth::id())
             ->where('cart_type', 'booking')
             ->delete();
        
        $vendor = Vendor::find($request->vendor_id);
        if ($vendor && $vendor->email) {
            dispatch(function () use ($bookings_saved, $vendor) {
                try {
                    Mail::to($vendor->email)->send(new BookingNotification($bookings_saved[0]));
                } catch (\Exception $e) {}
                try {
                    Mail::to('ctpwh2026@gmail.com')->send(new AdminBookingNotificationMail($bookings_saved[0]));
                } catch (\Exception $e) {}
            })->afterResponse();
        }
    }

    session()->flash('clear_booking_basket', true);
    Toastr::success('Order(s) Placed Successfully!');
    return redirect()->route('admin.bookings.index');
}

private function generateBookingNumber(): string
{
    return \App\Services\OrderNumberService::generate('VO', \App\Models\Booking::class, 'VO');
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->viewInvoice($id);
    }

    public function viewInvoice(string $id)
    {
        $targetBooking = Booking::findOrFail($id);
        $orderGroup = Booking::where('booking_no', $targetBooking->booking_no)
            ->with(['product.variants.color', 'product.variants.size', 'vendor', 'unit'])
            ->get();
        
        $settings = \App\Models\GeneralSetting::first();

        return view('backend.booking.invoice', compact('orderGroup', 'targetBooking', 'settings'));
    }

    public function downloadPdf(string $id)
    {
        $targetBooking = Booking::findOrFail($id);
        $path = 'bookings/booking_' . $targetBooking->booking_no . '.pdf';
        
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
        }

        \App\Jobs\GenerateBookingPdfJob::dispatch($targetBooking->id, \Illuminate\Support\Facades\Auth::id());
        
        Toastr::info('Booking PDF is generating in the background. Please refresh and click download again after a minute.');
        return redirect()->back();
    }

    public function exportExcel()
    {
        return Excel::download(new BookingsExport, 'bookings-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function downloadExcel(string $id)
    {
        $booking = Booking::findOrFail($id);
        return Excel::download(
            new BookingOrderExport($booking->booking_no),
            'booking-' . $booking->booking_no . '.xlsx'
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $targetBooking = Booking::findOrFail($id);
        // Fetch all bookings with the same booking_no
        $orderGroup = Booking::where('booking_no', $targetBooking->booking_no)->with(['product.variants.color', 'product.variants.size'])->get();
        
        $vendors = Vendor::where('status', 1)->latest()->get();
        $units = Unit::where('status', 1)->get();
        $categories = Category::where('status', 1)->get();
        
        // Match create fields: products for selection
        $products = Product::where('status', 1)->with(['variants.color', 'variants.size', 'category', 'subCategory', 'childCategory', 'unit'])->withSum('inventoryStocks', 'quantity')->latest()->get();

        return view('backend.booking.edit', compact('orderGroup', 'targetBooking', 'vendors', 'units', 'categories', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BookingUpdateRequest $request, string $id)
    {
        $targetBooking = Booking::findOrFail($id);
        $bookingNo = $targetBooking->booking_no;

        DB::beginTransaction();
        try {
            // Delete existing group records (to re-sync batch)
            Booking::where('booking_no', $bookingNo)->delete();

            // Delete existing PDF to ensure the next download is fresh
            $path = 'bookings/booking_' . $bookingNo . '.pdf';
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }

            // Re-insert new/updated items
            foreach ($request->items as $item) {
                $booking = new Booking();
                $booking->booking_no = $bookingNo;
                $booking->vendor_id = $request->vendor_id;
                $booking->product_id = $item['product_id'];
                
                // Categorization
                $product = Product::find($item['product_id']);
                $booking->category_id = $product->category_id;
                $booking->sub_category_id = $product->sub_category_id;
                $booking->child_category_id = $product->child_category_id;
                
                $booking->unit_id = $item['unit_id'] ?? $product->unit_id;
                $booking->qty = $item['qty'];

                // Handle Variant Info
                if (isset($item['variant_quantities']) && is_array($item['variant_quantities']) && count(array_filter($item['variant_quantities'])) > 0) {
                    $variantSum = 0;
                    $variantsData = [];
                    foreach ($item['variant_quantities'] as $variant => $qty) {
                        if ($qty > 0) {
                            $variantSum += $qty;
                            $variantsData[$variant] = $qty;
                        }
                    }
                    $booking->variant_info = $variantsData;
                    if ($booking->qty < $variantSum) {
                        $booking->qty = $variantSum;
                    }
                }

                $booking->description = $request->description;
                $booking->custom_fields = $request->custom_fields;
                $booking->shipping_method = $request->shipping_method;
                $booking->status = $request->status ?? 'pending';
                
                $booking->unit_price = 0;
                $booking->extra_cost = 0;
                $booking->total_cost = 0;
                $booking->sale_price = 0;
                
                $booking->save();
            }

            DB::commit();
            Toastr::success('Order Updated Successfully!');
            return redirect()->route('admin.bookings.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('Something went wrong: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $booking = Booking::findOrFail($id);
            Booking::where('booking_no', $booking->booking_no)->delete();
            return response(['status' => 'success', 'message' => 'Order Deleted Successfully!']);
        } catch (\Exception $e) {
            return response(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function changeStatus(Request $request)
    {
        // Find one item to get the booking_no
        $booking = Booking::findOrFail($request->id);
        
        // Update status for all items in this booking group
        Booking::where('booking_no', $booking->booking_no)->update(['status' => $request->status]);
        
        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }
}
