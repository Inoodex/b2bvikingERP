<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\GiftCardDataTable;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\GiftCard;
use App\Services\GiftCard\GiftCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GiftCardController extends Controller
{
    protected GiftCardService $service;

    public function __construct(GiftCardService $service)
    {
        $this->service = $service;
    }

    public function index(GiftCardDataTable $dataTable)
    {
        return $dataTable->render('backend.gift_cards.index');
    }

    public function create(): View
    {
        $currencies = Currency::where('status', 1)->get();
        $autoCode = $this->service->generateUniqueCode();
        return view('backend.gift_cards.create', compact('currencies', 'autoCode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:gift_cards,code',
            'initial_value' => 'required|numeric|min:1',
            'currency_id' => 'nullable|exists:currencies,id',
            'expires_at' => 'nullable|date',
            'status' => 'required|boolean',
        ]);

        try {
            $this->service->issueGiftCard([
                'code' => strtoupper(trim($request->code)),
                'initial_value' => $request->initial_value,
                'currency_id' => $request->currency_id,
                'expires_at' => $request->expires_at ? $request->expires_at . ' 23:59:59' : null,
                'status' => $request->status,
            ]);

            toastr()->success('Gift Card issued successfully!');
            return redirect()->route('admin.gift-cards.index');
        } catch (\Exception $e) {
            toastr()->error('Failed to issue Gift Card: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function show(GiftCard $giftCard): View
    {
        $giftCard->load(['currency', 'transactions.order']);
        return view('backend.gift_cards.show', compact('giftCard'));
    }

    public function adjustBalance(Request $request, GiftCard $giftCard): RedirectResponse
    {
        $request->validate([
            'adjustment_amount' => 'required|numeric',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $this->service->adjustBalance(
                $giftCard,
                (float) $request->adjustment_amount,
                $request->reason ?: 'Manual Admin Adjustment'
            );

            toastr()->success('Gift Card balance adjusted successfully!');
            return redirect()->route('admin.gift-cards.show', $giftCard->id);
        } catch (\Exception $e) {
            toastr()->error('Failed to adjust balance: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function destroy(string $id)
    {
        try {
            $giftCard = GiftCard::findOrFail($id);
            $giftCard->transactions()->delete();
            $giftCard->delete();

            return response(['status' => 'success', 'message' => 'Gift Card deleted successfully!']);
        } catch (\Exception $e) {
            return response(['status' => 'error', 'message' => 'Failed to delete Gift Card: ' . $e->getMessage()], 500);
        }
    }

    public function changeStatus(Request $request): JsonResponse
    {
        $giftCard = GiftCard::findOrFail($request->id);
        $giftCard->status = $request->status == 'true' ? 1 : 0;
        $giftCard->save();

        return response()->json(['status' => 'success', 'message' => 'Gift Card status updated successfully!']);
    }

    public function validateGiftCard(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = strtoupper(trim($request->code));
        $giftCard = GiftCard::with('currency')->where('code', $code)->where('status', 1)->first();

        if (!$giftCard) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or inactive Gift Card code.'], 404);
        }

        if ($giftCard->expires_at && $giftCard->expires_at->isPast()) {
            return response()->json(['status' => 'error', 'message' => 'This Gift Card has expired.'], 422);
        }

        if ($giftCard->balance <= 0) {
            return response()->json(['status' => 'error', 'message' => 'This Gift Card has zero balance remaining.'], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Gift Card validated successfully!',
            'card_id' => $giftCard->id,
            'code' => $giftCard->code,
            'balance' => (float) $giftCard->balance,
            'currency_symbol' => $giftCard->currency?->symbol ?? 'kr.',
        ]);
    }
}
