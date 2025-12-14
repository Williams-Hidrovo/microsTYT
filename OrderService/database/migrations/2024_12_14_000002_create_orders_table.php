<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('order_number', 50)->unique();
            $table->string('status', 50)->default('pending');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->text('notes')->nullable();
            $table->dateTime('order_date');
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('status');
            $table->index('order_date');
            $table->index('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
