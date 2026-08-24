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
        Schema::create('certification_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('simpel_kode_pelatihan');
            $table->string('smile_sertifikasi_name');
            $table->timestamps();
            
            // Unique constraint to prevent duplicate mapping
            $table->unique(['simpel_kode_pelatihan', 'smile_sertifikasi_name'], 'cert_map_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('certification_mappings');
    }
};
