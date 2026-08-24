<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIdpItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('idp_items', function (Blueprint $table) {
            if (!Schema::hasColumn('idp_items', 'competency_type')) {
                $table->string('competency_type')->default('Teknis')->after('need');
            }
            if (Schema::hasColumn('idp_items', 'path')) {
                $table->dropColumn('path');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('idp_items', function (Blueprint $table) {
            if (Schema::hasColumn('idp_items', 'competency_type')) {
                $table->dropColumn('competency_type');
            }
            if (!Schema::hasColumn('idp_items', 'path')) {
                $table->string('path')->default('Rekomendasi sistem');
            }
        });
    }
}
