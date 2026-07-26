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
        Schema::create('md_project_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('md_projects')->onDelete('cascade');
            $table->date('tanggal');
            $table->foreignId('kategori_id')->nullable()->constrained('md_master_data')->nullOnDelete();
            $table->string('judul', 150);
            $table->text('catatan')->nullable();
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'tanggal']);
            $table->index(['kategori_id', 'selesai_pada']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('md_project_notes');
    }
};
