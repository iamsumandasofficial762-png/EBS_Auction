<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('auction_notifications')) {
            Schema::create('auction_notifications', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('auction_id')->nullable()->index();
                $table->string('type', 60)->index();
                $table->string('title');
                $table->text('message')->nullable();
                $table->boolean('is_read')->default(false)->index();
                $table->timestamps();

                $table->unique(['customer_id', 'auction_id', 'type'], 'auction_notifications_customer_auction_type_unique');
            });
        }

        $this->addBidUniqueIndexWhenSafe();
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_notifications');
    }

    protected function addBidUniqueIndexWhenSafe(): void
    {
        if (! Schema::hasTable('auction_bids') || DB::getDriverName() !== 'mysql') {
            return;
        }

        $duplicates = DB::table('auction_bids')
            ->select('auction_id', 'customer_id')
            ->groupBy('auction_id', 'customer_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicates) {
            return;
        }

        $indexes = DB::select("SHOW INDEX FROM auction_bids WHERE Key_name = 'auction_bids_auction_customer_unique'");

        if (! $indexes) {
            DB::statement('ALTER TABLE auction_bids ADD UNIQUE auction_bids_auction_customer_unique (`auction_id`, `customer_id`)');
        }
    }
};
