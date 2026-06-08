<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('auction_bids')) {
            return;
        }

        if (
            Schema::hasColumn('auction_bids', 'auction_id')
            && Schema::hasColumn('auction_bids', 'auction_item_id')
        ) {
            DB::table('auction_bids')
                ->where(function ($query): void {
                    $query->whereNull('auction_id')->orWhere('auction_id', 0);
                })
                ->update(['auction_id' => DB::raw('auction_item_id')]);
        }

        if (
            Schema::hasColumn('auction_bids', 'customer_id')
            && Schema::hasColumn('auction_bids', 'user_id')
        ) {
            DB::table('auction_bids')
                ->where(function ($query): void {
                    $query->whereNull('customer_id')->orWhere('customer_id', 0);
                })
                ->update(['customer_id' => DB::raw('user_id')]);
        }

        if (
            Schema::hasColumn('auction_bids', 'amount')
            && Schema::hasColumn('auction_bids', 'bid_amount')
        ) {
            DB::table('auction_bids')
                ->where('amount', 0)
                ->where('bid_amount', '>', 0)
                ->update(['amount' => DB::raw('bid_amount')]);
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $indexes = collect(DB::select('SHOW INDEX FROM auction_bids'))
            ->groupBy('Key_name')
            ->map(fn ($items) => [
                'unique' => (int) $items->first()->Non_unique === 0,
                'columns' => $items->sortBy('Seq_in_index')->pluck('Column_name')->values()->all(),
            ]);

        foreach ($indexes as $name => $index) {
            if ($name === 'PRIMARY' || ! $index['unique']) {
                continue;
            }

            if (in_array($index['columns'], [['customer_id'], ['user_id']], true)) {
                DB::statement(sprintf('ALTER TABLE auction_bids DROP INDEX `%s`', str_replace('`', '``', $name)));
            }
        }

        $hasCurrentComposite = collect(DB::select('SHOW INDEX FROM auction_bids'))
            ->groupBy('Key_name')
            ->contains(function ($items): bool {
                $columns = $items->sortBy('Seq_in_index')->pluck('Column_name')->values()->all();

                return (int) $items->first()->Non_unique === 0
                    && $columns === ['auction_id', 'customer_id'];
            });

        if (! $hasCurrentComposite) {
            DB::statement('ALTER TABLE auction_bids ADD UNIQUE auction_bids_auction_customer_unique (`auction_id`, `customer_id`)');
        }
    }

    public function down(): void
    {
    }
};
