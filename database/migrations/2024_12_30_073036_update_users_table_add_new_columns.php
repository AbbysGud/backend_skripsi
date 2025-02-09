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
        Schema::table('users', function (Blueprint $table) {
            // Ubah age menjadi date_of_birth
            $table->dropColumn('age'); // Menghapus kolom usia
            $table->date('date_of_birth')->nullable(); // Menambahkan kolom tanggal lahir

            // Menambahkan kolom baru
            $table->float('height')->nullable(); // Tinggi
            $table->string('gender')->nullable(); // Jenis kelamin
            $table->date('pregnancy_date')->nullable(); // Tanggal mulai kehamilan
            $table->date('breastfeeding_date')->nullable(); // Tanggal mulai menyusui
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rollback perubahan
            $table->integer('age')->nullable(); // Menambahkan kembali kolom usia
            $table->dropColumn(['date_of_birth', 'height', 'gender', 'pregnancy_date', 'breastfeeding_date']);
        });
    }
};
