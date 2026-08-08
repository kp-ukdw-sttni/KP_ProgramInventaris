<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each row in the "barang" table now represents a single physical item
     * with its own unique inventory code, so the aggregated "jumlah" column
     * is no longer needed.
     */
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn('jumlah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->integer('jumlah')->default(1);
        });
    }
};
