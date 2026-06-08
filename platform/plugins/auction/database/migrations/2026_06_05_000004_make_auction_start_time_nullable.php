<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('auction_items')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasColumn('auction_items', 'start_time')) {
            DB::statement('ALTER TABLE auction_items MODIFY `start_time` DATETIME NULL DEFAULT NULL');
        }

        if (Schema::hasColumn('auction_items', 'start_at')) {
            DB::statement('ALTER TABLE auction_items MODIFY `start_at` DATETIME NULL DEFAULT NULL');
        }
    }

    public function down(): void
    {
        // Keep existing auction dates intact.
    }
};
