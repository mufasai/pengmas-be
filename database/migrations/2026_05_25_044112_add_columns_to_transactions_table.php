<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('transaction_code')->unique()->after('id');
            $table->integer('subtotal')->default(0)->after('transaction_code');
            $table->integer('tax')->default(0)->after('subtotal');
            $table->string('payment_method')->default('Tunai')->after('tax');
            $table->string('notes')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['transaction_code', 'subtotal', 'tax', 'payment_method', 'notes']);
        });
    }
};