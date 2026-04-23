<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('nama')->after('nip');
            $table->string('gelar_depan')->after('nama')->nullable();
            $table->string('gelar_belakang')->after('gelar_depan')->nullable();
            $table->string('pendidikan')->nullable();
            $table->text('alamat_asal')->nullable();
            $table->string('kota_asal')->nullable();
            $table->text('alamat_domisili')->nullable();
            $table->string('kota_domisili')->nullable();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->default('Perempuan');
            $table->enum('status_perkawinan', ['Kawin', 'Belum Kawin', 'Cerai Hidup', 'Cerai Mati'])->default('Belum Kawin');
            $table->enum('agama', ['Islam', 'Kristen Protestan', 'Kristen Katolik', 'Hindu', 'Buddha', 'Konghuchu', 'Kepercayaan'])->default('Islam');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->date('tanggal_join')->nullable();
            $table->string('email')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('no_ektp')->nullable();
            $table->string('kis')->nullable();
            $table->string('bpjs_tk')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('pemilik_rekening')->nullable();
            $table->string('nama_bank')->nullable();
            $table->string('foto')->nullable();
            $table->foreignId('job_title_id')->nullable()->constrained('job_titles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'nama', 'gelar_depan', 'gelar_belakang', 'pendidikan',
                'alamat_asal', 'kota_asal', 'alamat_domisili', 'kota_domisili',
                'jenis_kelamin', 'status_perkawinan', 'agama', 'tempat_lahir',
                'tanggal_lahir', 'tanggal_join', 'email', 'no_hp', 'no_ektp',
                'kis', 'bpjs_tk', 'no_rekening', 'pemilik_rekening',
                'nama_bank', 'foto', 'job_title_id',
            ]);
        });
    }
};
