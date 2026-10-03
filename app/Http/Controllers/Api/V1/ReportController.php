<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\TransactionReceipt;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d',
        ]);

        $query = Transaction::with(['details.menu.category', 'user'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59',
            ]);
        } elseif ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->get();

        $formattedData = $transactions->map(function (Transaction $transaction) {
            return $this->formatReportItem($transaction);
        });

        return response()->json([
            'success' => true,
            'data' => $formattedData,
        ]);
    }

    public function sendReceipt(Request $request): JsonResponse
    {
        $request->validate([
            'transaction_id' => 'required|integer|exists:transactions,id',
            'email' => 'required|email',
        ]);

        $transaction = Transaction::with(['details.menu.category', 'user'])
            ->findOrFail($request->transaction_id);

        $receipt = $this->formatReportItem($transaction);

        Mail::to($request->email)->send(new TransactionReceipt($receipt));

        return response()->json([
            'success' => true,
            'message' => 'E-receipt berhasil dikirim ke ' . $request->email,
        ]);
    }

    private function formatReportItem(Transaction $transaction): array
    {
        $user = $transaction->user;

        return [
            'id' => $transaction->id,
            'transaction_id' => $transaction->transaction_code,
            'date' => $transaction->created_at->format('Y-m-d'),
            'time' => $transaction->created_at->format('H:i') . ' WIB',
            'cashier' => [
                'id' => $user?->id,
                'name' => $user?->name,
            ],
            'payment_method' => $transaction->payment_method,
            'payment_summary' => [
                'subtotal' => $transaction->subtotal,
                'tax_percentage' => $transaction->tax_percentage,
                'tax_amount' => $transaction->tax,
                'discount_amount' => $transaction->discount_amount,
                'total_payment' => $transaction->total_amount,
            ],
            'items' => $transaction->details->map(function ($detail) {
                return [
                    'product_id' => $detail->menu_id,
                    'product_name' => $detail->menu->name ?? null,
                    'category' => $detail->menu->category->name ?? null,
                    'image_url' => $detail->menu->image ? asset('storage/' . $detail->menu->image) : null,
                    'variant_notes' => $detail->notes,
                    'quantity' => $detail->quantity,
                    'price' => $detail->menu->price ?? null,
                    'total_price' => $detail->subtotal,
                ];
            }),
        ];
    }
}