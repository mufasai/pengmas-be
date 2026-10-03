<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('notes')->constrained()->nullOnDelete();
            $table->integer('discount_amount')->default(0)->after('user_id');
            $table->integer('tax_percentage')->default(10)->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'discount_amount', 'tax_percentage']);
        });
    }
};