<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('auction_items') || ! Schema::hasColumn('auction_items', 'status')) {
            return;
        }

        DB::table('auction_items')->where('status', 'live')->update(['status' => 'published']);
        DB::table('auction_items')->where('status', 'cancelled')->update(['status' => 'draft']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE auction_items MODIFY `status` VARCHAR(30) NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        // Keep existing auction statuses intact.
    }
};
