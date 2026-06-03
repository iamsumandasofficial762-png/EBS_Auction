<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_reviews', function (Blueprint $table): void {
            $table->string('badge_type', 30)->default('auto')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('ec_reviews', function (Blueprint $table): void {
            $table->dropColumn('badge_type');
        });
    }
};
