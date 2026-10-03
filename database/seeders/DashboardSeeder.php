<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Database\Seeder;

class DashboardSeeder extends Seeder
{
    public function run(): void
    {
        // Buat beberapa menu
        $menus = [
            Menu::create(['name' => 'Van Houtte Signature Latte', 'price' => 35000, 'image' => null]),
            Menu::create(['name' => 'Caramel Macchiato Ice', 'price' => 35000, 'image' => null]),
            Menu::create(['name' => 'Almond Croissant', 'price' => 35000, 'image' => null]),
            Menu::create(['name' => 'Matcha Latte', 'price' => 30000, 'image' => null]),
            Menu::create(['name' => 'Americano', 'price' => 25000, 'image' => null]),
        ];

        // Buat transaksi hari ini
        for ($i = 0; $i < 10; $i++) {
            $transaction = Transaction::create([
                'total_amount' => 0,
            ]);

            $total = 0;
            // Setiap transaksi punya 1-3 item
            $itemCount = rand(1, 3);
            for ($j = 0; $j < $itemCount; $j++) {
                $menu = $menus[array_rand($menus)];
                $qty = rand(1, 3);
                $subtotal = $menu->price * $qty;

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'menu_id' => $menu->id,
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $transaction->update(['total_amount' => $total]);
        }
    }
}
