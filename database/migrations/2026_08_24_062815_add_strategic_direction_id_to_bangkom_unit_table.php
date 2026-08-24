<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStrategicDirectionIdToBangkomUnitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('bangkom_unit', function (Blueprint $table) {
            $table->string('strategic_direction_id', 50)->nullable()->after('latar_belakang');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('bangkom_unit', function (Blueprint $table) {
            $table->dropColumn('strategic_direction_id');
        });
    }
}
