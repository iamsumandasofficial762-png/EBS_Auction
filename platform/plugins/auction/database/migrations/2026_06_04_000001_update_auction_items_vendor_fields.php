<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('auction_items')) {
            return;
        }

        Schema::table('auction_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('auction_items', 'short_description')) {
                $table->text('short_description')->nullable()->after('images');
            }

            if (! Schema::hasColumn('auction_items', 'condition')) {
                $table->string('condition', 30)->nullable()->after('category_id');
            }

            if (! Schema::hasColumn('auction_items', 'brand')) {
                $table->string('brand', 150)->nullable()->after('condition');
            }

            if (! Schema::hasColumn('auction_items', 'model')) {
                $table->string('model', 150)->nullable()->after('brand');
            }

            if (! Schema::hasColumn('auction_items', 'auto_winner_delay_hours')) {
                $table->unsignedInteger('auto_winner_delay_hours')->default(8)->after('auto_select_at');
            }
        });

        if (Schema::hasColumn('auction_items', 'bid_increment') && DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE auction_items MODIFY bid_increment DECIMAL(15, 2) NULL DEFAULT 1');
        }

        if (Schema::hasColumn('auction_items', 'status')) {
            DB::table('auction_items')->where('status', 'live')->update(['status' => 'published']);
            DB::table('auction_items')->where('status', 'cancelled')->update(['status' => 'draft']);
        }
    }

    public function down(): void
    {
        // Keep auction data intact.
    }
};
