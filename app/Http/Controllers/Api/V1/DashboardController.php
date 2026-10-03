<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today('Asia/Jakarta');

        // 1. Jumlah Transaksi (Hari ini)
        $totalTransactions = Transaction::whereDate('created_at', $today)->count();
        $targetTransactions = 200;

        // 2. Rata-rata Struk (Hari ini)
        $averageReceipt = Transaction::whereDate('created_at', $today)->avg('total_amount') ?? 0;

        // 3. Produk Terlaris
        $topProducts = TransactionDetail::join('menus', 'transaction_details.menu_id', '=', 'menus.id')
            ->select(
                'menus.name',
                'menus.image',
                DB::raw('SUM(transaction_details.quantity) as total_sold'),
                DB::raw('SUM(transaction_details.subtotal) as total_revenue')
            )
            ->groupBy('menus.id', 'menus.name', 'menus.image')
            ->orderByDesc('total_sold')
            ->limit(3)
            ->get();

        // Map image ke full URL
        $topProducts->transform(function ($product) {
            $product->image_url = $product->image
                ? asset('storage/' . $product->image)
                : null;
            unset($product->image);
            return $product;
        });

        return response()->json([
            'data' => [
                'transactions' => [
                    'total' => $totalTransactions,
                    'target' => $targetTransactions,
                    'percentage' => round(($totalTransactions / $targetTransactions) * 100, 2)
                ],
                'average_receipt' => round($averageReceipt),
                'top_products' => $topProducts
            ]
        ]);
    }
}
