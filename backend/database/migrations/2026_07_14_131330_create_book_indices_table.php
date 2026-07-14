<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_indices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            // Auto-referencia: subindice aponta para o indice pai. Nulo = indice raiz.
            $table->foreignId('parent_id')->nullable()
                ->constrained('book_indices')->cascadeOnDelete();
            $table->string('titulo');
            $table->unsignedInteger('pagina');
            // Preserva a ordem dos irmaos dentro do mesmo pai.
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index('book_id');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_indices');
    }
};
