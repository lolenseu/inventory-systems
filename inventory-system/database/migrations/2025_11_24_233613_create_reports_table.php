<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type');          // 'monthly_sales'
            $table->integer('report_year');
            $table->integer('report_month')->nullable(); // null = yearly summary
            $table->integer('order_count');
            $table->decimal('total_sales', 12, 2);
            $table->decimal('avg_order_value', 10, 2);
            $table->string('top_product')->nullable();
            $table->string('best_customer')->nullable();
            $table->json('meta')->nullable();       // extra stats
            $table->timestamps();
            
            $table->unique(['report_type', 'report_year', 'report_month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
};