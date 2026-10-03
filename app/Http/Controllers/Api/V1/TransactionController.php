<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(): JsonResponse
    {
        $transactions = Transaction::with(['details.menu', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $transactions->getCollection()->transform(function ($transaction) {
            return $this->formatTransaction($transaction);
        });

        return response()->json($transactions);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'payment_method' => 'required|string|in:Tunai,Digital',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:menus,id,is_active,1',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:255',
            'discount_amount' => 'nullable|integer|min:0',
            'tax_percentage' => 'nullable|integer|min:0|max:100',
        ]);

        $transaction = DB::transaction(function () use ($request) {
            $transactionCode = $this->generateTransactionCode();

            $subtotal = 0;
            $detailsPayload = [];

            foreach ($request->items as $item) {
                $menu = Menu::findOrFail($item['product_id']);
                $lineSubtotal = $menu->price * $item['quantity'];
                $subtotal += $lineSubtotal;

                $detailsPayload[] = [
                    'menu_id' => $menu->id,
                    'quantity' => $item['quantity'],
                    'subtotal' => $lineSubtotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            $discountAmount = $request->input('discount_amount', 0);
            if ($discountAmount > $subtotal) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], [], []),
                    response()->json([
                        'message' => 'Diskon tidak boleh melebihi subtotal.',
                        'errors' => ['discount_amount' => ['Diskon tidak boleh melebihi subtotal.']],
                    ], 422)
                );
            }
            $taxPercentage = $request->input('tax_percentage', 10);
            $tax = (int) round(($subtotal - $discountAmount) * $taxPercentage / 100);
            $totalAmount = $subtotal - $discountAmount + $tax;

            $transaction = Transaction::create([
                'transaction_code' => $transactionCode,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'user_id' => $request->user()->id,
                'discount_amount' => $discountAmount,
                'tax_percentage' => $taxPercentage,
            ]);

            foreach ($detailsPayload as $detail) {
                $detail['transaction_id'] = $transaction->id;
                TransactionDetail::create($detail);
            }

            return $transaction;
        });

        $transaction->load(['details.menu', 'user']);

        return response()->json([
            'message' => 'Transaksi berhasil dibuat.',
            'data' => $this->formatTransaction($transaction),
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $transaction = Transaction::with(['details.menu', 'user'])->findOrFail($id);

        return response()->json([
            'data' => $this->formatTransaction($transaction),
        ]);
    }

    private function generateTransactionCode(): string
    {
        $today = now()->format('Ymd');
        $prefix = "TRX-{$today}-";

        $lastTransaction = Transaction::where('transaction_code', 'LIKE', "{$prefix}%")
            ->orderBy('transaction_code', 'desc')
            ->lockForUpdate()
            ->first();

        if ($lastTransaction) {
            $lastNumber = (int) substr($lastTransaction->transaction_code, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function formatTransaction(Transaction $transaction): array
    {
        $data = [
            'id' => $transaction->id,
            'transaction_code' => $transaction->transaction_code,
            'payment_method' => $transaction->payment_method,
            'subtotal' => $transaction->subtotal,
            'discount_amount' => $transaction->discount_amount,
            'tax_percentage' => $transaction->tax_percentage,
            'tax' => $transaction->tax,
            'total_amount' => $transaction->total_amount,
            'notes' => $transaction->notes,
            'user' => $transaction->user ? [
                'id' => $transaction->user->id,
                'name' => $transaction->user->name,
            ] : null,
            'items' => $transaction->details->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'menu_id' => $detail->menu_id,
                    'menu_name' => $detail->menu->name ?? null,
                    'quantity' => $detail->quantity,
                    'price' => $detail->menu->price ?? null,
                    'subtotal' => $detail->subtotal,
                    'notes' => $detail->notes,
                ];
            }),
            'created_at' => $transaction->created_at,
        ];

        if ($transaction->payment_method === 'Digital') {
            $qrisSetting = Setting::where('key', 'qris_image')->first();
            $data['qris_image_url'] = $qrisSetting && $qrisSetting->value
                ? asset('storage/' . $qrisSetting->value)
                : null;
        }

        return $data;
    }
}