    <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('units', 'expected_time')) {
            Schema::table('units', function (Blueprint $table) {
                $table->dropColumn('expected_time');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('units', 'expected_time')) {
            Schema::table('units', function (Blueprint $table) {
                $table->integer('expected_time')->nullable();
            });
        }
    }
};
