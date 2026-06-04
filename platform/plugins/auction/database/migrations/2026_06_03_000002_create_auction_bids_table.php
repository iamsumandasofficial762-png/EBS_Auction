<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('auction_bids')) {
            return;
        }

        Schema::create('auction_bids', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('auction_id')->index()->constrained('auction_items')->cascadeOnDelete();
            $table->foreignId('customer_id')->index()->constrained('ec_customers')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->index(['auction_id', 'amount', 'created_at']);
            $table->index(['auction_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_bids');
    }
};
