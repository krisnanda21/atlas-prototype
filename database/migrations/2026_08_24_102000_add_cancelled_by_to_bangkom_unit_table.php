<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bangkom_unit', function (Blueprint $table) {
            $table->string('cancelled_by')->nullable()->after('status');
            $table->text('cancel_reason')->nullable()->after('cancelled_by');
            $table->string('cancel_approved_by')->nullable()->after('cancel_reason');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bangkom_unit', function (Blueprint $table) {
            $table->dropColumn(['cancelled_by', 'cancel_reason', 'cancel_approved_by']);
        });
    }
};
