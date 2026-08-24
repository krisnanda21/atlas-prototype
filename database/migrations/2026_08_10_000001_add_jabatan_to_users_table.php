<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJabatanToUsersTable extends Migration
{
    /**
     * Run the migrations.
     * Adds a 'jabatan' column to store the specific job title
     * (e.g. 'Koordinator Pengawasan') for distinguishing sub-types
     * within the same access role (e.g. 'eselon3').
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('jabatan')->nullable()->after('scope');
            $table->string('unit_eselon2')->nullable()->after('jabatan');
            $table->string('unit_eselon1')->nullable()->after('unit_eselon2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['jabatan', 'unit_eselon2', 'unit_eselon1']);
        });
    }
}
