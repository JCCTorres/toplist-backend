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
        Schema::create('client_properties', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key (equivale ao _id do MongoDB)
            
            // Campos principais
            $table->bigInteger('airbnb_id')->unique()->comment('ID único do Airbnb');
            $table->string('airbnb_url')->comment('URL da propriedade no Airbnb');
            $table->string('title')->comment('Título da propriedade');
            $table->string('house_number')->nullable()->comment('Número da casa');
            $table->string('owner')->nullable()->comment('Proprietário');
            $table->text('observations')->nullable()->comment('Observações');
            $table->text('address')->nullable()->comment('Endereço da propriedade');
            
            // Dados em JSON
            $table->json('bookerville_data')->nullable()->comment('Dados da API do Bookerville');
            
            // Timestamps
            $table->timestamp('last_sync')->nullable()->comment('Data da última sincronização');
            $table->timestamps(); // created_at e updated_at
            
            // Índices para performance
            $table->index('airbnb_id');
            $table->index('owner');
            $table->index('last_sync');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_properties');
    }
};
