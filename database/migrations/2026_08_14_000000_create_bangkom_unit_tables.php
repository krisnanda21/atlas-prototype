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
        Schema::create('bangkom_unit', function (Blueprint $table) {
            $table->string('id', 6)->primary();
            $table->string('nama_kegiatan', 999);
            $table->string('unit_pengusul');
            $table->string('indikator_kinerja', 999);
            $table->string('jenis_latar_belakang'); // Hasil Asesmen, Kebutuhan Jabatan, Arahan Strategis, Isu Terkini
            $table->text('latar_belakang');
            $table->text('tujuan_kegiatan');
            $table->string('kompetensi_dasar');
            $table->text('indikator_keberhasilan'); // JSON array of values
            $table->text('penugasan_terkait'); // JSON array of values
            $table->string('metode'); // Full Tatap Muka, Hybrid, PJJ
            $table->integer('jp')->default(10);
            $table->timestamp('tanggal_dibuat')->useCurrent();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('jumlah_kelas');
            $table->string('jalur_pembelajaran');
            $table->bigInteger('nilai_anggaran');
            $table->text('kriteria_peserta'); // JSON array of values
            $table->string('fasilitator', 100);
            $table->string('evaluasi'); // Ya / Tidak
            $table->integer('jenis_evaluasi')->nullable(); // 1 (Level 1), 2 (Level 2)
            $table->string('status')->default('draft'); // draft / menunggu penetapan / ditetapakan / revisi / realisasi / realisasi diajukan / revisi realisasi / selesai
            
            // Realization documents
            $table->string('dok_daftar_hadir')->nullable();
            $table->string('dok_notulen')->nullable();
            $table->string('dok_dokumentasi')->nullable();
            $table->string('dok_nilai')->nullable();

            $table->timestamps();
        });

        Schema::create('bangkom_unit_realisasi', function (Blueprint $table) {
            $table->id();
            $table->string('bangkom_unit_id', 6);
            $table->string('employee_id');
            $table->integer('skor_penyelenggara');
            $table->integer('skor_materi');
            $table->integer('skor_fasilitator');
            $table->integer('skor_pre')->nullable();
            $table->integer('skor_post')->nullable();
            $table->timestamps();

            $table->foreign('bangkom_unit_id')
                ->references('id')
                ->on('bangkom_unit')
                ->onDelete('cascade');

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bangkom_unit_realisasi');
        Schema::dropIfExists('bangkom_unit');
    }
};
