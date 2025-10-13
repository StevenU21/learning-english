<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('lesson_user_progress')) {
            Schema::table('lesson_user_progress', function (Blueprint $table) {
                if (!Schema::hasColumn('lesson_user_progress', 'attempts_count')) {
                    $table->unsignedInteger('attempts_count')->default(0)->after('status');
                }
                if (!Schema::hasColumn('lesson_user_progress', 'last_completed_at')) {
                    $table->dateTime('last_completed_at')->nullable()->after('attempts_count');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lesson_user_progress')) {
            Schema::table('lesson_user_progress', function (Blueprint $table) {
                if (Schema::hasColumn('lesson_user_progress', 'last_completed_at')) {
                    $table->dropColumn('last_completed_at');
                }
                if (Schema::hasColumn('lesson_user_progress', 'attempts_count')) {
                    $table->dropColumn('attempts_count');
                }
            });
        }
    }
};
