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

        $this->syncTimeColumns('start_at', 'start_time');
        $this->syncTimeColumns('end_at', 'end_time');
    }

    public function down(): void
    {
        // This migration only repairs compatibility with older table shapes.
    }

    protected function syncTimeColumns(string $legacyColumn, string $currentColumn): void
    {
        if (! Schema::hasColumn('auction_items', $legacyColumn)) {
            return;
        }

        if (Schema::hasColumn('auction_items', $currentColumn)) {
            DB::table('auction_items')
                ->whereNull($currentColumn)
                ->whereNotNull($legacyColumn)
                ->update([$currentColumn => DB::raw($legacyColumn)]);

            DB::table('auction_items')
                ->whereNull($legacyColumn)
                ->whereNotNull($currentColumn)
                ->update([$legacyColumn => DB::raw($currentColumn)]);
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement(sprintf('ALTER TABLE auction_items MODIFY `%s` DATETIME NULL DEFAULT NULL', $legacyColumn));
        }
    }
};
