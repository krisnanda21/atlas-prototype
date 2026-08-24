<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAtasanIdToUsersTable extends Migration
{
    /**
     * Tambah kolom atasan_id untuk menyimpan chain of command.
     * FK nullable ke users.id (self-referential).
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('atasan_id')->nullable()->after('jabatan');
            $table->foreign('atasan_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['atasan_id']);
            $table->dropColumn('atasan_id');
        });
    }
}
