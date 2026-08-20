<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id'              => 'required|exists:orders,id',
            'delivery_date'         => 'nullable|date',
            'outlet_id'             => 'nullable|exists:outlets,id',
            'carrier_name'          => 'nullable|string|max:100',
            'shipping_method'       => 'nullable|string|max:100',
            'shipping_address'      => 'nullable|string|max:500',
            'contact_person'        => 'nullable|string|max:100',
            'contact_phone'         => 'nullable|string|max:50',
            'vehicle_no'            => 'nullable|string|max:100',
            'awb_number'            => 'nullable|string|max:100',
            'tracking_number'       => 'nullable|string|max:100',
            'driver_name'           => 'nullable|string|max:100',
            'driver_phone'          => 'nullable|string|max:50',
            'estimated_delivery_at' => 'nullable|date',
            'notes'                 => 'nullable|string|max:1000',
            'remarks'               => 'nullable|string|max:1000',
            'items'                 => 'required|array|min:1',
            'items.*.order_item_id' => 'nullable|exists:order_items,id',
            'items.*.product_id'    => 'nullable|exists:products,id',
            'items.*.variant_id'    => 'nullable|exists:product_variants,id',
            'items.*.ordered_qty'   => 'nullable|numeric',
            'items.*.delivery_qty'  => 'nullable|numeric',
            'items.*.qty'           => 'nullable|numeric',
            'items.*.remarks'       => 'nullable|string|max:255',
        ];
    }
}
