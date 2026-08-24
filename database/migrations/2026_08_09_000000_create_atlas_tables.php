<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtlasTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Employees table
        Schema::create('employees', function (Blueprint $table) {
            $table->string('id')->primary(); // NIP Baru
            $table->string('name');
            $table->string('jenis_kelamin')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('email_dinas')->nullable();
            $table->string('unit_kerja_1')->nullable();
            $table->string('unit_kerja_2')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('pangkat')->nullable();
            $table->string('strata')->nullable();
            $table->string('jurusan')->nullable();
            $table->integer('toefl')->nullable();
            $table->float('ielts')->nullable();

            $table->string('initial');
            $table->string('category'); // JFA / Non-JFA / Pelaksana
            $table->string('unit'); // Biro SDM, etc.
            $table->string('role'); // Auditor Ahli Muda, etc.
            $table->boolean('assessment')->default(false);
            $table->string('assessment_label');
            $table->string('basis');
            $table->integer('idp_coverage')->default(0);
            $table->timestamps();
        });

        // 2. Competency Gaps table (from COMPASS)
        Schema::create('competency_gaps', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('competency_name');
            $table->integer('score');
            $table->string('type')->default('Teknis'); // Teknis, Manajerial, Sosial Kultural
            $table->integer('standard')->default(0);
            $table->integer('gap')->default(0);
            $table->string('level')->default('-');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        // 3. IDP Items table
        Schema::create('idp_items', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('employee_id');
            $table->string('need');
            $table->string('competency_type')->default('Teknis'); // Teknis, Manajerial
            $table->string('source'); // assessment-based, role-based, mandatory-based, unit-strategic-direction-based, self-initiative
            $table->text('basis');
            $table->string('priority'); // Tinggi / Sedang / Rendah
            $table->string('status'); // Draft / Diajukan / Disepakati / Perlu Perbaikan
            $table->text('revision_note')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        // 4. Strategic Directions table (Kepala Unit)
        Schema::create('strategic_directions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->string('basis'); // Kinerja unit, Sasaran/IKK, Risiko unit, Mandat baru, Prioritas pengawasan, Kebutuhan layanan
            $table->text('context');
            $table->string('competency');
            $table->string('priority'); // Tinggi / Sedang / Rendah
            $table->string('period'); // TW III 2026, etc.
            $table->string('path')->default('Bangkom Unit / Pelatihan Formal');
            $table->string('status')->default('Masuk Demand Pool');
            $table->string('follow_up')->default('Belum'); // Belum / Bangkom Unit
            $table->string('unit')->default('Biro SDM');
            $table->string('sasaran_pegawai')->default('JFA'); // JFA / Non-JFA
            $table->timestamps();
        });

        // 5. Bangkom Plans & Realisations table - MOVED TO NEW MIGRATION


        // 7. Audit Trails table
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->string('time');
            $table->string('actor');
            $table->string('action');
            $table->string('object');
            $table->timestamps();
        });

        // 8. Reference & Parameters table
        Schema::create('reference_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // IDP Source Type, Jenis Bangkom, Status Arahan, Role, Periode, Unit Kerja, Display Parameter
            $table->string('key');
            $table->string('value');
            $table->string('owner')->default('Biro SDM');
            $table->timestamps();
        });

        // 9. Employee Diklats table (from SMILE)
        Schema::create('employee_diklats', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('no_sertifikat');
            $table->string('nama_diklat');
            $table->integer('jumlah_jam');
            $table->string('dokumen');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        // 10. Employee Certifications table (from SMILE)
        Schema::create('employee_sertifikasis', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('nama_sertifikasi');
            $table->string('nomor_sertifikasi');
            $table->string('dokumen');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        // 11. COMPASS Nilai Mansos table
        Schema::create('compass_nilai_mansos', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('kompetensi');
            $table->integer('nilai');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        // 12. COMPASS Nilai Teknis table
        Schema::create('compass_nilai_teknis', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('kompetensi');
            $table->integer('nilai');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        // 13. COMPASS Nilai Rata-rata table
        Schema::create('compass_nilai_rata_rata', function (Blueprint $table) {
            $table->string('employee_id')->primary();
            $table->double('nilai_teknis');
            $table->double('nilai_mansoskul');
            $table->integer('nilai_potensi');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        // 14. INTERNA Rencana Diklat table
        Schema::create('interna_rencana_diklat', function (Blueprint $table) {
            $table->string('kode_pembelajaran')->primary();
            $table->string('unit_pengusul');
            $table->string('program_pembelajaran');
            $table->text('judul_program');
            $table->text('jenis_kompetensi');
            $table->string('mulai_e_learning');
            $table->string('selesai_e_learning');
            $table->string('mulai_tatap_muka');
            $table->string('selesai_tatap_muka');
            $table->string('jenis_pembelajaran');
            $table->string('status');
            $table->timestamps();
        });

        // 15. SIMPEL t_diklat table
        Schema::create('simpel_t_diklat', function (Blueprint $table) {
            $table->string('kode_pelatihan')->primary();
            $table->text('nama_pelatihan');
            $table->string('jenis_pelatihan');
            $table->text('kompetensi');
            $table->string('tanggal_mulai_daftar');
            $table->string('tanggal_selesai_daftar');
            $table->string('tanggal_mulai');
            $table->string('tanggal_selesai');
            $table->string('batas_daftar');
            $table->integer('jam_pelatihan');
            $table->integer('jumlah_kuota');
            $table->string('pendaftaran');
            $table->string('syarat_jabatan');
            $table->string('syarat_pendidikan');
            $table->string('skp');
            $table->string('pic');
            $table->string('unit_penyelenggara');
            $table->integer('jumlah_pendaftar');
            $table->timestamps();
        });

        // 16. SIMPEL t_pendaftar table
        Schema::create('simpel_t_pendaftar', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pelatihan');
            $table->string('nama');
            $table->string('employee_id');
            $table->string('jabatan');
            $table->string('unit_eselon2');
            $table->string('status');
            $table->string('peserta')->nullable();
            $table->timestamps();

            $table->foreign('kode_pelatihan')->references('kode_pelatihan')->on('simpel_t_diklat')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        // 17. SITUBEL t_tubel table
        Schema::create('situbel_t_tubel', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('nama');
            $table->string('sponsor');
            $table->string('program_studi');
            $table->string('jurusan');
            $table->string('tanggal_mulai_studi');
            $table->string('tanggal_akhir_studi');
            $table->string('no_spptb');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        // 18. COMPASS Required Mansos table
        Schema::create('compass_req_mansos', function (Blueprint $table) {
            $table->string('jenjang')->primary();
            $table->integer('integritas')->default(0);
            $table->integer('kerja_sama')->default(0);
            $table->integer('komunikasi')->default(0);
            $table->integer('orientasi_pada_hasil')->default(0);
            $table->integer('pelayanan_publik')->default(0);
            $table->integer('pengembangan_diri_dan_orang_lain')->default(0);
            $table->integer('mengelola_perubahan')->default(0);
            $table->integer('pengambilan_keputusan')->default(0);
            $table->integer('karakteristik_lintas_manajerial')->default(0);
            $table->integer('kepemimpinan')->default(0);
            $table->integer('komunikasi_trusted_advisor_value_driver')->default(0);
            $table->integer('perekat_bangsa')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compass_req_mansos');
        Schema::dropIfExists('situbel_t_tubel');
        Schema::dropIfExists('simpel_t_pendaftar');
        Schema::dropIfExists('simpel_t_diklat');
        Schema::dropIfExists('interna_rencana_diklat');
        Schema::dropIfExists('compass_nilai_rata_rata');
        Schema::dropIfExists('compass_nilai_teknis');
        Schema::dropIfExists('compass_nilai_mansos');
        Schema::dropIfExists('employee_sertifikasis');
        Schema::dropIfExists('employee_diklats');
        Schema::dropIfExists('reference_parameters');
        Schema::dropIfExists('audit_trails');
        Schema::dropIfExists('strategic_directions');
        Schema::dropIfExists('idp_items');
        Schema::dropIfExists('competency_gaps');
        Schema::dropIfExists('employees');
    }
}
