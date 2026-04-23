<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sccr_campus';

    public function up(): void
    {
        Schema::connection($this->connection)->create('students', function (Blueprint $table) {

            // PRIMARY KEY
            $table->id();

            // SSO (Relasi global user di sccr_db)
            $table->unsignedBigInteger('user_id')->nullable()->comment('Relasi ke tabel users di sccr_db');

            // Identitas dasar
            $table->string('nim', 20)->unique();
            $table->string('no_ektp', 20)->nullable();
            $table->string('nisn', 20)->nullable();
            $table->string('nama_lengkap', 100);
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->default('Perempuan');
            $table->enum('agama', ['Islam', 'Kristen', 'Hindu', 'Buddha', 'Konghuchu', 'Kepercayaan', 'Tidak Punya'])->default('Islam');
            $table->enum('gol_darah', ['A', 'B', 'AB', 'O', 'TIDAK TAHU'])->default('TIDAK TAHU');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();

            // Kontak
            $table->string('email_private')->nullable();
            $table->string('email_campus')->nullable();
            $table->string('no_hp', 25)->nullable();
            $table->string('alamat_domisili', 255)->nullable();
            $table->string('kota_domisili', 255)->nullable();

            // Akademik
            $table->unsignedBigInteger('kelas_id');     // FK ke tabel kelas
            $table->unsignedBigInteger('prodi_id');     // FK ke tabel prodis
            $table->unsignedBigInteger('fakultas_id');  // FK ke tabel fakultas

            $table->string('tahun_masuk', 10)->nullable();
            $table->enum('jenjang', ['D3', 'S1', 'S2', 'S3'])->default('S1');
            $table->enum('student_status', ['active', 'leave', 'graduated', 'dropout'])->default('active');
            $table->string('asal_sekolah', 100)->nullable();

            // Data Orang Tua
            $table->string('nama_ayah', 100)->nullable();
            $table->string('nama_ibu', 100)->nullable();
            $table->string('no_hp_parent', 25)->nullable();
            $table->string('alamat_asal', 255)->nullable();
            $table->string('kota_asal', 100)->nullable();
            $table->string('propinsi_asal', 100)->nullable();

            // Dokumen (nama file pendek, max 30 char)
            $table->string('photo_file', 30)->nullable()->comment('contoh: 25311123456789_photo.png');
            $table->string('kk_file', 30)->nullable()->comment('contoh: 25311123456789_kk.png');
            $table->string('ektp_file', 30)->nullable()->comment('contoh: 25311123456789_ektp.png');
            $table->string('ijazah_file', 30)->nullable()->comment('contoh: 25311123456789_ijazah.png');

            // Info lainnya
            $table->string('notes', 255)->nullable();
            $table->string('no_virtual_account')->nullable();
            $table->string('nama_bank', 25)->nullable();

            // Laravel default
            $table->timestamps();
            $table->softDeletes();

            // 🔒 Foreign Keys (optional — kamu bisa aktifkan setelah tabel master siap)
            // $table->foreign('kelas_id')->references('id')->on('kelas');
            // $table->foreign('prodi_id')->references('id')->on('prodis');
            // $table->foreign('fakultas_id')->references('id')->on('fakultas');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('students');
    }
};
