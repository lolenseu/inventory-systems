<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'approved', 'declined', 'delivered'])->default('pending');
            $table->text('shipping_address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for reports performance
            $table->index(['status']);
            $table->index(['created_at']);
            $table->index(['customer_id']);
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['customer_id']);
        });
        Schema::dropIfExists('orders');
    }
};