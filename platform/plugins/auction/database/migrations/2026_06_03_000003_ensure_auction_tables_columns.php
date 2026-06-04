<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('auction_items')) {
            Schema::create('auction_items', function (Blueprint $table): void {
                $table->id();
                $table->timestamps();
            });
        }

        Schema::table('auction_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('auction_items', 'vendor_id')) {
                $table->unsignedBigInteger('vendor_id')->nullable()->index();
            }

            if (! Schema::hasColumn('auction_items', 'store_id')) {
                $table->unsignedBigInteger('store_id')->nullable()->index();
            }

            if (! Schema::hasColumn('auction_items', 'title')) {
                $table->string('title')->nullable();
            }

            if (! Schema::hasColumn('auction_items', 'slug')) {
                $table->string('slug')->nullable()->unique();
            }

            if (! Schema::hasColumn('auction_items', 'description')) {
                $table->longText('description')->nullable();
            }

            if (! Schema::hasColumn('auction_items', 'images')) {
                $table->json('images')->nullable();
            }

            if (! Schema::hasColumn('auction_items', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->index();
            }

            if (! Schema::hasColumn('auction_items', 'starting_bid')) {
                $table->decimal('starting_bid', 15, 2)->default(0);
            }

            if (! Schema::hasColumn('auction_items', 'bid_increment')) {
                $table->decimal('bid_increment', 15, 2)->default(1);
            }

            if (! Schema::hasColumn('auction_items', 'start_time')) {
                $table->timestamp('start_time')->nullable()->index();
            }

            if (! Schema::hasColumn('auction_items', 'end_time')) {
                $table->timestamp('end_time')->nullable()->index();
            }

            if (! Schema::hasColumn('auction_items', 'status')) {
                $table->string('status', 30)->default('draft')->index();
            }

            if (! Schema::hasColumn('auction_items', 'winner_customer_id')) {
                $table->unsignedBigInteger('winner_customer_id')->nullable()->index();
            }

            if (! Schema::hasColumn('auction_items', 'winning_bid_id')) {
                $table->unsignedBigInteger('winning_bid_id')->nullable()->index();
            }

            if (! Schema::hasColumn('auction_items', 'winner_selected_at')) {
                $table->timestamp('winner_selected_at')->nullable();
            }

            if (! Schema::hasColumn('auction_items', 'auto_select_at')) {
                $table->timestamp('auto_select_at')->nullable()->index();
            }

            if (! Schema::hasColumn('auction_items', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('auction_items', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        if (! Schema::hasTable('auction_bids')) {
            Schema::create('auction_bids', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('auction_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->decimal('amount', 15, 2);
                $table->timestamps();
            });

            return;
        }

        Schema::table('auction_bids', function (Blueprint $table): void {
            if (! Schema::hasColumn('auction_bids', 'auction_id')) {
                $table->unsignedBigInteger('auction_id')->index();
            }

            if (! Schema::hasColumn('auction_bids', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->index();
            }

            if (! Schema::hasColumn('auction_bids', 'amount')) {
                $table->decimal('amount', 15, 2)->default(0);
            }

            if (! Schema::hasColumn('auction_bids', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('auction_bids', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // This migration repairs an existing table shape without deleting user data.
    }
};
