<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the method column to add 'vnpay' option
        DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('cash', 'credit_card', 'bank_transfer', 'paypal', 'vnpay') DEFAULT 'cash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'vnpay' option from method column
        DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('cash', 'credit_card', 'bank_transfer', 'paypal') DEFAULT 'cash'");
    }
};
