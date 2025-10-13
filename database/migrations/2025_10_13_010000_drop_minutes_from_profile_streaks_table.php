<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('profile_streaks') && Schema::hasColumn('profile_streaks', 'minutes')) {
            Schema::table('profile_streaks', function (Blueprint $table) {
                $table->dropColumn('minutes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('profile_streaks') && !Schema::hasColumn('profile_streaks', 'minutes')) {
            Schema::table('profile_streaks', function (Blueprint $table) {
                $table->integer('minutes')->default(0);
            });
        }
    }
};
