<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('auction_notifications')) {
            return;
        }

        if (Schema::hasColumn('auction_notifications', 'customer_id')) {
            match (DB::getDriverName()) {
                'mysql' => DB::statement('ALTER TABLE auction_notifications MODIFY customer_id BIGINT UNSIGNED NULL'),
                'pgsql' => DB::statement('ALTER TABLE auction_notifications ALTER COLUMN customer_id DROP NOT NULL'),
                default => null,
            };
        }

        if (Schema::hasColumn('auction_notifications', 'user_id')) {
            match (DB::getDriverName()) {
                'mysql' => DB::statement('ALTER TABLE auction_notifications MODIFY user_id BIGINT UNSIGNED NULL'),
                'pgsql' => DB::statement('ALTER TABLE auction_notifications ALTER COLUMN user_id DROP NOT NULL'),
                default => null,
            };
        }
    }

    public function down(): void
    {
        // Global auction notifications need nullable customer_id, so keep the column nullable.
    }
};
