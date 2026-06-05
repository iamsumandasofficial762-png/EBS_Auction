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
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->unsignedBigInteger('auction_id')->nullable()->index();
                $table->string('type', 60)->index();
                $table->string('title');
                $table->text('message')->nullable();
                $table->boolean('is_read')->default(false)->index();
                $table->timestamps();
            });
        } else {
            Schema::table('auction_notifications', function (Blueprint $table): void {
                if (! Schema::hasColumn('auction_notifications', 'customer_id')) {
                    $table->unsignedBigInteger('customer_id')->nullable()->index()->after('id');
                }

                if (! Schema::hasColumn('auction_notifications', 'auction_id')) {
                    $table->unsignedBigInteger('auction_id')->nullable()->index()->after('customer_id');
                }

                if (! Schema::hasColumn('auction_notifications', 'type')) {
                    $table->string('type', 60)->default('auction_update')->index()->after('auction_id');
                }

                if (! Schema::hasColumn('auction_notifications', 'title')) {
                    $table->string('title')->nullable()->after('type');
                }

                if (! Schema::hasColumn('auction_notifications', 'message')) {
                    $table->text('message')->nullable()->after('title');
                }

                if (! Schema::hasColumn('auction_notifications', 'is_read')) {
                    $table->boolean('is_read')->default(false)->index()->after('message');
                }

                if (! Schema::hasColumn('auction_notifications', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }

                if (! Schema::hasColumn('auction_notifications', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        $this->copyLegacyCustomerColumn();
        $this->addUniqueIndexWhenSafe();
    }

    public function down(): void
    {
        // This migration repairs existing notification tables without deleting user data.
    }

    protected function copyLegacyCustomerColumn(): void
    {
        if (Schema::hasColumn('auction_notifications', 'user_id') && Schema::hasColumn('auction_notifications', 'customer_id')) {
            DB::table('auction_notifications')
                ->whereNull('customer_id')
                ->whereNotNull('user_id')
                ->update(['customer_id' => DB::raw('user_id')]);
        }
    }

    protected function addUniqueIndexWhenSafe(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $indexes = DB::select("SHOW INDEX FROM auction_notifications WHERE Key_name = 'auction_notifications_customer_auction_type_unique'");

        if (! $indexes) {
            DB::statement('ALTER TABLE auction_notifications ADD UNIQUE auction_notifications_customer_auction_type_unique (`customer_id`, `auction_id`, `type`(60))');
        }
    }
};
