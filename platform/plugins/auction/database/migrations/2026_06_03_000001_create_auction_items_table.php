<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('auction_items')) {
            return;
        }

        Schema::create('auction_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->nullable()->index()->constrained('ec_customers')->nullOnDelete();
            $table->unsignedBigInteger('store_id')->nullable()->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->json('images')->nullable();
            $table->foreignId('category_id')->nullable()->index()->constrained('ec_product_categories')->nullOnDelete();
            $table->decimal('starting_bid', 15, 2);
            $table->decimal('bid_increment', 15, 2);
            $table->timestamp('start_time')->index();
            $table->timestamp('end_time')->index();
            $table->string('status', 30)->default('draft')->index();
            $table->foreignId('winner_customer_id')->nullable()->constrained('ec_customers')->nullOnDelete();
            $table->foreignId('winning_bid_id')->nullable()->index();
            $table->timestamp('winner_selected_at')->nullable();
            $table->timestamp('auto_select_at')->nullable()->index();
            $table->timestamps();

            $table->index(['status', 'start_time', 'end_time']);
            $table->index(['vendor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_items');
    }
};
