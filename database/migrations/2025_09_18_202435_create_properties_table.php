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
        Schema::create('properties', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key (equivale ao _id do MongoDB)
            
            // Campos principais
            $table->string('property_id')->unique()->comment('ID único da propriedade');
            $table->string('title')->nullable()->comment('Título da propriedade para cards');
            $table->json('summary')->nullable()->comment('Resumo da propriedade em JSON');
            $table->json('details')->nullable()->comment('Detalhes da propriedade em JSON');
            $table->string('category')->nullable()->comment('Categoria da propriedade');
            $table->boolean('is_active')->default(true)->comment('Se a propriedade está ativa');
            
            // Timestamps
            $table->timestamp('last_sync')->nullable()->comment('Data da última sincronização');
            $table->timestamps(); // created_at e updated_at
            
            // Índices para performance
            $table->index('property_id');
            $table->index('category');
            $table->index('is_active');
            $table->index('last_sync');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
